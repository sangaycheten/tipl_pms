<?php

namespace App\Http\Controllers\Automation;

use App\Http\Controllers\Controller;
use App\PMSEmployeeGoal;
use App\PMSEmployeeGoalDetail;
use App\PMSEmployeeGoalTarget;
use App\PMSEmployeeGoalTargetMultipleAppraiser;
use App\PMSEmployeeGoalL1Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GoalAppraisalController extends Controller
{
    // ── Index: list subordinates awaiting L1 appraisal ──────────────────────

    public function index()
    {
        $appraiserId = Auth::id();

        // All active subordinates where I am Level 1 appraiser
        $subordinateIds = DB::table('mas_hierarchy as h')
            ->join('mas_employee as e', 'e.Id', '=', 'h.EmployeeId')
            ->where('h.ReportingLevel1EmployeeId', $appraiserId)
            ->where(DB::raw('COALESCE(e.Status, 0)'), 1)
            ->pluck('h.EmployeeId')
            ->unique()
            ->values();

        if ($subordinateIds->isEmpty()) {
            return view('automation.goalappraiselist', ['rows' => collect()]);
        }

        // For each subordinate, aggregate H1/H2 status across all their masters
        $masters = DB::table('pms_employeegoal')
            ->whereIn('EmployeeId', $subordinateIds)
            ->select('Id', 'EmployeeId', 'H1Status', 'H2Status')
            ->get();

        // Existing L1 submission records for this appraiser — join through pms_employeegoal
        // to recover EmployeeId for keying, since the submission now only stores EmployeeGoalId
        $submissions = DB::table('pms_employeegoal_l1submission as s')
            ->join('pms_employeegoal as eg', 'eg.Id', '=', 's.EmployeeGoalId')
            ->where('s.AppraiserEmployeeId', $appraiserId)
            ->whereIn('eg.EmployeeId', $subordinateIds)
            ->select('s.EmployeeGoalId', 's.Cycle', 's.SubmittedAt', 'eg.EmployeeId')
            ->get()
            ->keyBy(fn($s) => $s->EmployeeId . '_' . $s->Cycle);

        // Employee names
        $employees = DB::table('mas_employee as e')
            ->join('mas_department as d', 'd.Id', '=', 'e.DepartmentId')
            ->leftJoin('mas_designation as des', 'des.Id', '=', 'e.DesignationId')
            ->whereIn('e.Id', $subordinateIds)
            ->select('e.Id', 'e.Name', 'e.EmpId', 'd.ShortName as Department', 'des.Name as Designation')
            ->get()
            ->keyBy('Id');

        // Aggregate max H1/H2 status per employee across all their masters
        $cycleStatus = [];
        foreach ($masters as $m) {
            $eid = $m->EmployeeId;
            if (!isset($cycleStatus[$eid])) {
                $cycleStatus[$eid] = ['h1' => 0, 'h2' => 0];
            }
            $cycleStatus[$eid]['h1'] = max($cycleStatus[$eid]['h1'], (int)$m->H1Status);
            $cycleStatus[$eid]['h2'] = max($cycleStatus[$eid]['h2'], (int)$m->H2Status);
        }

        $rows = collect();
        foreach ($subordinateIds as $eid) {
            $emp    = $employees[$eid] ?? null;
            $cycles = $cycleStatus[$eid] ?? ['h1' => 0, 'h2' => 0];

            foreach (['H1' => 'h1', 'H2' => 'h2'] as $cycle => $key) {
                if ($cycles[$key] !== 1) continue; // employee hasn't submitted this cycle

                $sub = $submissions->get($eid . '_' . $cycle);
                $rows->push((object)[
                    'employeeId'  => $eid,
                    'employee'    => $emp,
                    'cycle'       => $cycle,
                    'isDraft'     => $sub && is_null($sub->SubmittedAt),
                    'isSubmitted' => $sub && !is_null($sub->SubmittedAt),
                    'submittedAt' => $sub ? $sub->SubmittedAt : null,
                ]);
            }
        }

        return view('automation.goalappraiselist', compact('rows'));
    }

    // ── Show: appraisal form for one employee+cycle ──────────────────────────

    public function show(Request $request, $employeeId, $cycle)
    {
        $cycle = strtoupper($cycle);
        if (!in_array($cycle, ['H1', 'H2'])) abort(404);

        $appraiserId = Auth::id();
        $this->authorizeAppraiser($employeeId, $appraiserId);

        [$h1Status, $h2Status] = $this->getCycleStatuses($employeeId);
        $cycleStatus = $cycle === 'H1' ? $h1Status : $h2Status;
        if ($cycleStatus !== 1) {
            return back()->with('error', "Employee has not submitted {$cycle} self-rating yet.");
        }

        $master     = $this->getMasterForCycle($employeeId, $cycle);
        $isMultiple = $master ? (bool)$master->MultipleLevel1Appraiser : false;

        $submission = $master
            ? PMSEmployeeGoalL1Submission::where('EmployeeGoalId', $master->Id)
                ->where('AppraiserEmployeeId', $appraiserId)
                ->where('Cycle', $cycle)
                ->first()
            : null;
        $isSubmitted = $submission && !is_null($submission->SubmittedAt);

        // Load goals for this cycle
        $cycleColumn = $cycle === 'H1' ? 'InH1' : 'InH2';
        $allGoalIds  = DB::table('pms_employeegoal')
            ->where('EmployeeId', $employeeId)
            ->where(function ($q) {
                $q->where('GoalSetBy', '!=', 'supervisor')
                  ->orWhere('ApprovalStatus', 2);
            })
            ->pluck('Id');

        $goalDetails = PMSEmployeeGoalDetail::with('targets')
            ->whereIn('EmployeeGoalId', $allGoalIds)
            ->where('Type', 2)
            ->where($cycleColumn, 1)
            ->orderBy('DisplayOrder')
            ->get();

        // Attach existing L1 scores to each task
        $taskIds = $goalDetails->flatMap(fn($g) => $g->targets->pluck('Id'));

        if ($isMultiple) {
            $existingScores = PMSEmployeeGoalTargetMultipleAppraiser::whereIn('TargetId', $taskIds)
                ->where('Level1Appraiser', $appraiserId)
                ->get()
                ->keyBy('TargetId');
        } else {
            $existingScores = PMSEmployeeGoalTarget::whereIn('Id', $taskIds)
                ->whereNotNull('Level1Appraiser')
                ->get()
                ->keyBy('Id');
        }

        $employee = DB::table('mas_employee as e')
            ->join('mas_department as d', 'd.Id', '=', 'e.DepartmentId')
            ->leftJoin('mas_designation as des', 'des.Id', '=', 'e.DesignationId')
            ->where('e.Id', $employeeId)
            ->select('e.Id', 'e.Name', 'e.EmpId', 'd.Name as Department', 'des.Name as Designation')
            ->first();

        return view('automation.goalappraise', compact(
            'employee', 'cycle', 'goalDetails', 'isMultiple',
            'existingScores', 'isSubmitted', 'submission', 'employeeId'
        ));
    }

    // ── Save: draft or submit L1 scores ─────────────────────────────────────

    public function save(Request $request, $employeeId, $cycle)
    {
        $cycle = strtoupper($cycle);
        if (!in_array($cycle, ['H1', 'H2'])) abort(404);

        $appraiserId = Auth::id();
        $this->authorizeAppraiser($employeeId, $appraiserId);

        [$h1Status, $h2Status] = $this->getCycleStatuses($employeeId);
        $cycleStatus = $cycle === 'H1' ? $h1Status : $h2Status;
        if ($cycleStatus !== 1) {
            return back()->with('error', "Employee has not submitted {$cycle} self-rating yet.");
        }

        $master = $this->getMasterForCycle($employeeId, $cycle);
        if (!$master) {
            return back()->with('error', "No goal master found for {$cycle}.");
        }

        // Block re-submission
        $existing = PMSEmployeeGoalL1Submission::where('EmployeeGoalId', $master->Id)
            ->where('AppraiserEmployeeId', $appraiserId)
            ->where('Cycle', $cycle)
            ->first();
        if ($existing && !is_null($existing->SubmittedAt)) {
            return back()->with('error', "You have already submitted your {$cycle} appraisal for this employee.");
        }

        $isMultiple  = (bool)$master->MultipleLevel1Appraiser;
        $action      = $request->input('save_action', 'draft');
        $now         = date('Y-m-d H:i:s');
        $submittedAt = $action === 'submit' ? $now : null;

        // On submit: every task must have an L1 score (0 is valid; blank is not)
        if ($action === 'submit') {
            $missingCount = 0;
            foreach ($request->input('tasks', []) as $data) {
                $s = $data['l1_score'] ?? '';
                if ($s === '' || $s === null) $missingCount++;
            }
            if ($missingCount > 0) {
                return back()->withInput()->withErrors(['error' =>
                    "Please enter L1 scores for all tasks before submitting ({$missingCount} task(s) still empty). Enter 0 if the score is zero."
                ]);
            }
        }

        DB::transaction(function () use ($request, $appraiserId, $cycle, $isMultiple, $action, $now, $submittedAt, $existing, $master) {
            foreach ($request->input('tasks', []) as $taskId => $data) {
                $rawScore = $data['l1_score'] ?? '';
                $score    = ($rawScore !== '' && $rawScore !== null) ? (float)$rawScore : null;
                $remarks  = isset($data['l1_remarks']) ? trim($data['l1_remarks'])   : null;

                if ($isMultiple) {
                    $row = PMSEmployeeGoalTargetMultipleAppraiser::where('TargetId', $taskId)
                        ->where('Level1Appraiser', $appraiserId)
                        ->first();

                    if ($row) {
                        $row->Level1Score   = $score;
                        $row->Level1Remarks = $remarks;
                        $row->EditedBy      = $appraiserId;
                        $row->updated_at    = $now;
                        $row->save();
                    } else {
                        PMSEmployeeGoalTargetMultipleAppraiser::create([
                            'Id'              => UUID(),
                            'TargetId'        => $taskId,
                            'Level1Appraiser' => $appraiserId,
                            'Level1Score'     => $score,
                            'Level1Remarks'   => $remarks,
                            'CreatedBy'       => $appraiserId,
                            'created_at'      => $now,
                        ]);
                    }
                } else {
                    PMSEmployeeGoalTarget::where('Id', $taskId)->update([
                        'Level1Appraiser' => $appraiserId,
                        'Level1Score'     => $score,
                        'Level1Remarks'   => $remarks,
                        'EditedBy'        => $appraiserId,
                        'updated_at'      => $now,
                    ]);
                }
            }

            if ($existing) {
                $existing->SubmittedAt = $submittedAt;
                $existing->updated_at  = $now;
                $existing->save();
            } else {
                PMSEmployeeGoalL1Submission::create([
                    'Id'                  => UUID(),
                    'EmployeeGoalId'      => $master->Id,
                    'AppraiserEmployeeId' => $appraiserId,
                    'Cycle'               => $cycle,
                    'SubmittedAt'         => $submittedAt,
                    'CreatedBy'           => $appraiserId,
                    'created_at'          => $now,
                ]);
            }
        });

        $msg = $action === 'submit'
            ? "Your {$cycle} appraisal has been submitted successfully."
            : "Your {$cycle} appraisal draft has been saved.";

        return redirect()->route('goals.appraise.show', [$employeeId, strtolower($cycle)])
            ->with('successmessage', $msg);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function authorizeAppraiser($employeeId, $appraiserId): void
    {
        $isL1 = DB::table('mas_hierarchy')
            ->where('EmployeeId', $employeeId)
            ->where('ReportingLevel1EmployeeId', $appraiserId)
            ->exists();
        if (!$isL1) abort(403, 'You are not the Level 1 appraiser for this employee.');
    }

    private function getCycleStatuses(string $employeeId): array
    {
        $masters = DB::table('pms_employeegoal')
            ->where('EmployeeId', $employeeId)
            ->select('H1Status', 'H2Status')
            ->get();

        $h1Status = 0;
        $h2Status = 0;
        foreach ($masters as $m) {
            $h1Status = max($h1Status, (int)$m->H1Status);
            $h2Status = max($h2Status, (int)$m->H2Status);
        }
        return [$h1Status, $h2Status];
    }

    private function getMasterForCycle(string $employeeId, string $cycle): ?object
    {
        $statusField = $cycle === 'H1' ? 'H1Status' : 'H2Status';

        return DB::table('pms_employeegoal as eg')
            ->join('sys_pmsnumber as pn', 'pn.Id', '=', 'eg.SysPmsNumberId')
            ->where('eg.EmployeeId', $employeeId)
            ->where("eg.{$statusField}", 1)
            ->select('eg.Id', 'eg.MultipleLevel1Appraiser', 'eg.SysPmsNumberId')
            ->orderByDesc('pn.StartDate')
            ->first();
    }
}
