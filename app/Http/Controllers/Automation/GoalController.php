<?php

namespace App\Http\Controllers\Automation;

use App\Http\Controllers\Controller;
use App\PMSEmployeeGoal;
use App\PMSEmployeeGoalDetail;
use App\PMSEmployeeGoalHistory;
use App\PMSEmployeeGoalTarget;
use App\PMSEmployeeGoalTargetMultipleAppraiser;
use App\PMSEmployeeGoalL1Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class GoalController extends Controller
{
    
    public function getNewGoalIndex($id, $round = false)
    {
        /* PROFILE */
        $data['EmployeeId'] = $id;
        $data['details'] = DB::select("
                SELECT 
                    T1.EmpId,
                    T1.Status,
                    T3.GradeId,
                    T1.PositionId,
                    T1.CIDNo,
                    T1.Extension, 
                    T1.MobileNo, 
                    T1.DateOfBirth, 
                    T1.DateOfAppointment,
                    T1.ProfilePicPath,
                    T1.Name,
                    B.Name AS DesignationLocation, 
                    T2.Name AS Department,
                    A.Name AS Section, 
                    T4.Name AS GradeStep, 
                    T4.PayScale, 

                    CASE 
                        WHEN COALESCE(T1.NoProbation, 0) = 0 AND T4.PayScale IS NOT NULL
                        THEN DATE_ADD(T1.DateOfAppointment, INTERVAL 6 MONTH) 
                        ELSE T1.DateOfAppointment 
                    END AS DateOfRegularization, 

                    (
                        SELECT GROUP_CONCAT(CONCAT(P.Name, ' (', Q.Name, ')') SEPARATOR '<br/>') 
                        FROM mas_hierarchy O 
                        JOIN mas_employee P ON P.Id = O.ReportingLevel1EmployeeId 
                        JOIN mas_designation Q ON Q.Id = P.DesignationId 
                        WHERE O.EmployeeId = T1.Id
                    ) AS Level1Name,

                    (
                        SELECT GROUP_CONCAT(CONCAT(P.Name, ' (', Q.Name, ')') SEPARATOR '<br/>') 
                        FROM mas_hierarchy O 
                        JOIN mas_employee P ON P.Id = O.ReportingLevel2EmployeeId 
                        JOIN mas_designation Q ON Q.Id = P.DesignationId 
                        WHERE O.EmployeeId = T1.Id
                    ) AS Level2Name, 

                    CONCAT(
                        Z1.Name,
                        CASE 
                            WHEN Z2.Id IS NULL THEN '' 
                            ELSE CONCAT(' - Reporting to ', Z2.Name) 
                        END
                    ) AS Position 

                FROM mas_employee T1 
                LEFT JOIN mas_designation B ON B.Id = T1.DesignationId 
                JOIN mas_department T2 ON T2.Id = T1.DepartmentId 
                LEFT JOIN mas_section A ON A.Id = T1.SectionId 
                LEFT JOIN (
                    mas_position T3 
                    JOIN mas_grade Z1 ON Z1.Id = T3.GradeId 
                    LEFT JOIN mas_supervisor Z2 ON Z2.Id = T3.SupervisorId
                ) ON T3.Id = T1.PositionId 
                LEFT JOIN mas_gradestep T4 ON T4.Id = T1.GradeStepId
                
                WHERE T1.Id = ?
            ", [$id]);

        if (count($data['details']) == 0) {
            abort(404);
        }
        /* END PROFILE */
        $today = strtotime(date('Y-m-d'));
        $withinFirstPMSOfYear = false;
        $withinSecondPMSOfYear = false;
        $notWithinPMSPeriod = false;

        if ($today >= strtotime(date(CONST_PMSSETTING_SECONDPMSSTARTDATE)) && $today <= strtotime(date(CONST_PMSSETTING_SECONDPMSENDDATE))) {
            $withinSecondPMSOfYear = true;
        } else {
            if ($today >= strtotime(date(CONST_PMSSETTING_FIRSTPMSSTARTDATE)) && $today <= strtotime(date(CONST_PMSSETTING_FIRSTPMSENDDATE))) {
                $withinFirstPMSOfYear = true;
            }
        }

        if (!$withinFirstPMSOfYear && !$withinSecondPMSOfYear) {
            $notWithinPMSPeriod = true;
        }
        $data['isDefined'] = false;
        if ($round) {
            if ($round == 1):
                $data['nextPMSId'] = DB::table("sys_pmsnumber")
                    ->where('StartDate', '<', date('Y-m-d'))
                    ->orderBy('StartDate', 'DESC')
                    ->take(1)
                    ->value("Id");
            else:
                $data['nextPMSId'] = DB::table("sys_pmsnumber")
                    ->where('StartDate', '<', date('Y-m-d'))
                    ->orderBy('StartDate', 'DESC')
                    ->take(1)
                    ->value("Id");
            endif;
        } else {
            if ($notWithinPMSPeriod) {
                $data['nextPMSId'] = DB::table("sys_pmsnumber")
                    ->where('StartDate', '>', date('Y-m-d'))
                    ->orderBy('StartDate')
                    ->take(1)
                    ->value("Id");
            } else {
                $data['nextPMSId'] = DB::table('sys_pmsnumber')->where('StartDate', "<=", date('Y-m-d'))->orderBy('StartDate', 'DESC')->take(1)->value('Id');
            }
        }

        // goalId — any visible master (for View button)
        // supervisorGoalId — strictly supervisor-set masters only (for Edit / Submit buttons)
        $data['goalId'] = DB::table('pms_employeegoal')
            ->where("SysPmsNumberId", $data['nextPMSId'])
            ->where('EmployeeId', $id)
            ->where(function ($q) {
                $q->where('GoalSetBy', 'supervisor')
                  ->orWhere('ApprovalStatus', '>=', 1);
            })
            ->value("Id");
        $data['supervisorGoalId'] = DB::table('pms_employeegoal')
            ->where("SysPmsNumberId", $data['nextPMSId'])
            ->where('EmployeeId', $id)
            ->where('GoalSetBy', 'supervisor')
            ->value("Id");

        $selectedYear = (int) request('year', date('Y'));
        $data['selectedYear'] = $selectedYear;

        $data['goalDetailsExists'] = false;
        $data['goalDetails'] = collect();
        $data['onmDetails'] = [new PMSEmployeeGoalDetail()];
        $data['goalSubmissionHistory'] = [new PMSEmployeeGoalHistory()];

        // Supervisor view: hide subordinate draft goals (ApprovalStatus=0); show only submitted/approved/rejected
        $visibleGoalIds = DB::table('pms_employeegoal')
            ->where('EmployeeId', $id)
            ->where(function ($q) {
                $q->where('GoalSetBy', 'supervisor')
                  ->orWhere('ApprovalStatus', '>=', 1);
            })
            ->pluck('Id');

        if ($visibleGoalIds->isNotEmpty()) {
            $data['isDefined'] = true;

            $data['onmDetails'] = DB::table("pms_employeegoaldetail")
                ->where("EmployeeGoalId", $data['goalId'])
                ->where('Type', 1)
                ->orderBy('DisplayOrder')
                ->get(['Id', 'Description', 'DisplayOrder', 'Weightage', 'Target', 'Achievement', 'SelfScore']);
            if ($data['onmDetails']->isEmpty()) {
                $data['onmDetails'] = [new PMSEmployeeGoalDetail()];
            }

            // Fetch goal details filtered by selected year; hide subordinate drafts from supervisor
            $data['goalDetails'] = PMSEmployeeGoalDetail::with('targets')
                ->whereIn('EmployeeGoalId', $visibleGoalIds)
                ->where('Type', 2)
                ->where('Year', $selectedYear)
                ->orderBy('DisplayOrder')
                ->get();

            if ($data['goalDetails']->isNotEmpty()) {
                $data['goalDetailsExists'] = true;
                // Point goalId to the first visible master for the selected year (View button)
                $data['goalId'] = $data['goalDetails']->first()->EmployeeGoalId;
                // supervisorGoalId: supervisor-set master among the selected year's goals (Edit/Submit)
                $yearMasterIds = $data['goalDetails']->pluck('EmployeeGoalId')->unique()->toArray();
                $supervisorMaster = DB::table('pms_employeegoal')
                    ->whereIn('Id', $yearMasterIds)
                    ->where('GoalSetBy', 'supervisor')
                    ->value('Id');
                if ($supervisorMaster) {
                    $data['supervisorGoalId'] = $supervisorMaster;
                }
            }
        }

        [$data['h1Status'], $data['h2Status']] = $this->getCycleStatuses($id);

        // Approval info for the supervisor view — exclude drafts (ApprovalStatus=0)
        $data['approvalMasters'] = DB::table('pms_employeegoal')
            ->where('EmployeeId', $id)
            ->where('GoalSetBy', 'individual')
            ->where('ApprovalStatus', '>=', 1)
            ->select('Id', 'GoalSetBy', 'ApprovalStatus', 'ApprovalRemark')
            ->get();

        // L1 appraisal — is current user a Level 1 appraiser for this employee?
        $data['isL1Appraiser'] = DB::table('mas_hierarchy')
            ->where('EmployeeId', $id)
            ->where('ReportingLevel1EmployeeId', Auth::id())
            ->exists();

        // L1 submission status per cycle for current viewer (null=not started, SubmittedAt null=draft, set=submitted)
        $l1Subs = DB::table('pms_employeegoal_l1submission as s')
            ->join('pms_employeegoal as eg', 'eg.Id', '=', 's.EmployeeGoalId')
            ->where('eg.EmployeeId', $id)
            ->where('s.AppraiserEmployeeId', Auth::id())
            ->select('s.*')
            ->get()
            ->keyBy('Cycle');
        $data['l1H1Submission'] = $l1Subs->get('H1');
        $data['l1H2Submission'] = $l1Subs->get('H2');

        // Has ANY L1 appraiser submitted for each cycle? (used to reveal L1 Score column)
        $anyL1Submitted = DB::table('pms_employeegoal_l1submission as s')
            ->join('pms_employeegoal as eg', 'eg.Id', '=', 's.EmployeeGoalId')
            ->where('eg.EmployeeId', $id)
            ->whereNotNull('s.SubmittedAt')
            ->select('s.Cycle')
            ->get()
            ->pluck('Cycle');
        $data['h1L1Appraised'] = $anyL1Submitted->contains('H1');
        $data['h2L1Appraised'] = $anyL1Submitted->contains('H2');

        // MultipleLevel1Appraiser flag — take it from whichever master has submitted status
        $master = DB::table('pms_employeegoal')
            ->where('EmployeeId', $id)
            ->where(function ($q) {
                $q->where('H1Status', 1)->orWhere('H2Status', 1);
            })
            ->orderByDesc('created_at')
            ->first(['MultipleLevel1Appraiser']);
        $isMultiple = $master ? (bool)$master->MultipleLevel1Appraiser : false;
        $data['isMultiple'] = $isMultiple;

        // For multiple-appraiser mode: average L1 score per task keyed by task Id
        $data['l1AvgScores'] = collect();
        if ($isMultiple) {
            $data['l1AvgScores'] = DB::table('pms_employeetargetmultipleappraiser')
                ->select('TargetId', DB::raw('AVG(Level1Score) as AvgL1Score'))
                ->groupBy('TargetId')
                ->get()
                ->keyBy('TargetId');
        }

        return view('automation.goalindex', $data);
    }

    public function index()
    {
        return redirect()->route('goals.create');
    }

    public function show($goal)
    {
        $employeeGoal = PMSEmployeeGoal::findOrFail($goal);

        $goalDetails = PMSEmployeeGoalDetail::with('targets')
            ->where('EmployeeGoalId', $goal)
            ->where('Type', 2)
            ->orderBy('DisplayOrder')
            ->get();

        $employee = DB::table('mas_employee')
            ->where('Id', $employeeGoal->EmployeeId)
            ->select('Id', 'Name', 'EmpId')
            ->first();

        return view('automation.viewgoal', compact('employeeGoal', 'goalDetails', 'employee'));
    }

    public function edit($goal)
    {
        $employeeGoal = PMSEmployeeGoal::findOrFail($goal);
        $employeeId   = $employeeGoal->EmployeeId;

        // Block individual from editing a supervisor-set master
        if ((string)Auth::id() === (string)$employeeId && $employeeGoal->GoalSetBy === 'supervisor') {
            return redirect()->route('individualpmsgoal')
                ->with('error', 'Your supervisor has set goals for you. Only your supervisor can edit these goals.');
        }

        // Block editing while goals are pending supervisor approval
        if ((int)$employeeGoal->ApprovalStatus === 1) {
            return redirect()->route('individualpmsgoal')
                ->with('error', 'Your goals are pending supervisor approval and cannot be edited until a decision is made.');
        }

        // Block supervisor from editing goals once employee has started self-rating
        if ((string)Auth::id() !== (string)$employeeId
            && $this->hasIndividualStartedRating((string)$employeeId)) {
            return back()->with('error', 'Goals cannot be edited: the employee has already started their self-rating for this period.');
        }

        [$h1Status, $h2Status] = $this->getCycleStatuses($employeeId);

        $selectedYear = (int) date('Y');

        $allMasterIds = PMSEmployeeGoal::where('EmployeeId', $employeeId)->pluck('Id');

        // Section goals only (GoalType=1 or legacy null) — these are editable
        $goalDetails = PMSEmployeeGoalDetail::with('targets')
            ->whereIn('EmployeeGoalId', $allMasterIds)
            ->where('Type', 2)
            ->where('Year', $selectedYear)
            ->where(function ($q) {
                $q->where('GoalType', 1)->orWhereNull('GoalType');
            })
            ->orderBy('DisplayOrder')
            ->get();

        // Common goals (GoalType=2) — shown read-only, not editable
        $commonGoalDetails = PMSEmployeeGoalDetail::with('targets')
            ->whereIn('EmployeeGoalId', $allMasterIds)
            ->where('Type', 2)
            ->where('Year', $selectedYear)
            ->where('GoalType', 2)
            ->orderBy('DisplayOrder')
            ->get();

        $goalsJson = $goalDetails->map(fn($g) => [
            'goal_number' => (int)($g->DisplayOrder / 1000),
            'description' => $g->Description,
            'total_score' => (float)$g->Weightage,
            'year'        => $g->Year,
            'in_h1'       => (bool)$g->InH1,
            'in_h2'       => (bool)$g->InH2,
            'locked'      => ((bool)$g->InH1 && $h1Status === 1) || ((bool)$g->InH2 && $h2Status === 1),
            'tasks'       => $g->targets->map(fn($t) => [
                'description' => $t->Description,
                'weightage'   => (float)$t->Weightage,
                'target'      => $t->Target,
            ])->values(),
        ])->values();

        $isIndividual = (string)$employeeId === (string)Auth::id();

        $today      = strtotime(date('Y-m-d'));
        $inH1Window = $today >= strtotime(date(CONST_PMSSETTING_FIRSTPMSSTARTDATE))
                   && $today <= strtotime(date(CONST_PMSSETTING_FIRSTPMSENDDATE));
        $inH2Window = $today >= strtotime(date(CONST_PMSSETTING_SECONDPMSSTARTDATE))
                   && $today <= strtotime(date(CONST_PMSSETTING_SECONDPMSENDDATE));
        $h1WindowLabel = date('M j', strtotime(date(CONST_PMSSETTING_FIRSTPMSSTARTDATE)))
                       . ' – ' . date('M j', strtotime(date(CONST_PMSSETTING_FIRSTPMSENDDATE)));
        $h2WindowLabel = date('M j', strtotime(date(CONST_PMSSETTING_SECONDPMSSTARTDATE)))
                       . ' – ' . date('M j', strtotime(date(CONST_PMSSETTING_SECONDPMSENDDATE)));

        return view('automation.editgoal', compact(
            'employeeGoal', 'goalDetails', 'commonGoalDetails',
            'goalsJson', 'h1Status', 'h2Status', 'isIndividual',
            'inH1Window', 'inH2Window', 'h1WindowLabel', 'h2WindowLabel'
        ));
    }

    public function update(Request $request, $goal)
    {
        $employeeGoal = PMSEmployeeGoal::findOrFail($goal);
        $employeeId   = $employeeGoal->EmployeeId;
        $departmentId = $employeeGoal->DepartmentId;

        $cgScores = $request->input('cg_scores', []);
        [$h1Status, $h2Status] = $this->getCycleStatuses((string)$employeeId);

        $request->validate([
            'goals'                       => 'required|array|min:1',
            'goals.*.description'         => 'required|string|max:500',
            'goals.*.total_score'         => 'required|numeric|min:0|max:100',
            'goals.*.year'                => 'required|integer|min:2000|max:2100',
            'goals.*.tasks'               => 'required|array|min:1',
            'goals.*.tasks.*.description' => 'required|string|max:500',
            'goals.*.tasks.*.weightage'   => 'required|numeric|min:0',
        ]);

        foreach ($request->goals as $gIdx => $g) {
            $sum        = collect($g['tasks'] ?? [])->sum(fn($t) => floatval($t['weightage'] ?? 0));
            $totalScore = floatval($g['total_score']);
            if (round($sum, 2) > round($totalScore, 2)) {
                $goalNum = $g['goal_number'] ?? ($gIdx + 1);
                return back()->withInput()->withErrors([
                    "goals.{$gIdx}.tasks" => "Goal {$goalNum}: task weightages ({$sum}) exceed the goal total score ({$totalScore}).",
                ]);
            }
            // Reject goals that touch a cycle whose self-rating is already submitted
            $inH1 = !empty($g['in_h1']);
            $inH2 = !empty($g['in_h2']);
            if ($h1Status && $inH1) {
                $goalNum = $g['goal_number'] ?? ($gIdx + 1);
                return back()->withInput()->withErrors(['goals' =>
                    "Goal {$goalNum} includes H1 which is already submitted and locked. Uncheck H1 or use H2-only goals for this period."
                ]);
            }
            if ($h2Status && $inH2) {
                $goalNum = $g['goal_number'] ?? ($gIdx + 1);
                return back()->withInput()->withErrors(['goals' =>
                    "Goal {$goalNum} includes H2 which is already submitted and locked. Uncheck H2."
                ]);
            }
        }

        // Validate common goal task scores against their respective goal weightage
        $allMasterIds = PMSEmployeeGoal::where('EmployeeId', $employeeId)->pluck('Id');
        foreach ($cgScores as $detailId => $cgData) {
            $detail = PMSEmployeeGoalDetail::where('Id', $detailId)
                ->whereIn('EmployeeGoalId', $allMasterIds)
                ->where('GoalType', 2)
                ->first();
            if (!$detail) continue;
            $cgGoalWt   = floatval($cgData['weightage'] ?? 0);
            $cgTaskSum  = collect($cgData['tasks'] ?? [])->sum(fn($w) => floatval($w));
            if (round($cgTaskSum, 2) > round($cgGoalWt, 2)) {
                return back()->withInput()->withErrors([
                    'goals' => "Common goal \"{$detail->Description}\": task scores ({$cgTaskSum}) exceed the goal weightage ({$cgGoalWt}).",
                ]);
            }
        }

        $setBy = ((string)$employeeId === (string)Auth::id()) ? 'individual' : 'supervisor';

        // Block supervisor from saving goals once employee has started self-rating
        if ($setBy === 'supervisor' && $this->hasIndividualStartedRating((string)$employeeId)) {
            return back()->withErrors(['goals' =>
                'Goals cannot be modified: the employee has already started their self-rating for this period.'
            ]);
        }

        $saveAction     = $request->input('save_action', 'draft');
        $approvalStatus = $setBy === 'supervisor' ? ($saveAction === 'draft' ? 0 : 2) : 0;

        // Individual cannot update goals on a supervisor-set master
        if ($setBy === 'individual' && $employeeGoal->GoalSetBy === 'supervisor') {
            return back()->withErrors(['goals' => 'Cannot modify supervisor-set goals. Only your supervisor can edit these goals.']);
        }
        // Supervisor must have total score = 100 per active window cycle when publishing
        if ($setBy === 'supervisor' && $saveAction !== 'draft') {
            $activeCycles = $this->activeWindowCycles();
            if (!empty($activeCycles)) {
                $cycleError = $this->validateCycleTotals($request->goals, (string)$employeeId, $activeCycles);
                if ($cycleError) {
                    return back()->withInput()->withErrors(['goals' => $cycleError]);
                }
            }
        }

        // Individual: check per-cycle conflicts with supervisor-set goals
        if ($setBy === 'individual') {
            $conflict = $this->checkSupervisorGoalConflict((string)$employeeId, $request->goals);
            if ($conflict) {
                return back()->withInput()->withErrors(['goals' => $conflict]);
            }
        }

        // Individual submitting for approval: enforce PMS window and cycle total = 100
        if ($setBy === 'individual' && $request->input('save_action') === 'submit') {
            $today      = strtotime(date('Y-m-d'));
            $inH1Window = $today >= strtotime(date(CONST_PMSSETTING_FIRSTPMSSTARTDATE))
                       && $today <= strtotime(date(CONST_PMSSETTING_FIRSTPMSENDDATE));
            $inH2Window = $today >= strtotime(date(CONST_PMSSETTING_SECONDPMSSTARTDATE))
                       && $today <= strtotime(date(CONST_PMSSETTING_SECONDPMSENDDATE));
            if (!$inH1Window && !$inH2Window) {
                $h1Range = date('M j', strtotime(date(CONST_PMSSETTING_FIRSTPMSSTARTDATE)))
                         . ' – ' . date('M j', strtotime(date(CONST_PMSSETTING_FIRSTPMSENDDATE)));
                $h2Range = date('M j', strtotime(date(CONST_PMSSETTING_SECONDPMSSTARTDATE)))
                         . ' – ' . date('M j', strtotime(date(CONST_PMSSETTING_SECONDPMSENDDATE)));
                return back()->withInput()->withErrors(['goals' =>
                    "Goal submission is only allowed during the PMS window: H1 ({$h1Range}) or H2 ({$h2Range})."
                ]);
            }
            $cycleError = $this->validateCycleTotals($request->goals, (string)$employeeId, $this->activeWindowCycles());
            if ($cycleError) {
                return back()->withInput()->withErrors(['goals' => $cycleError]);
            }
        }

        DB::transaction(function () use ($request, $employeeId, $departmentId, $setBy, $approvalStatus, $cgScores, $allMasterIds, $h1Status, $h2Status) {
            // Delete section goals (GoalType=1 or legacy null), but never touch rows from a
            // cycle whose self-rating is already submitted — those are immutable historical data.
            $submittedYears = array_unique(array_column(array_values($request->goals), 'year'));
            if ($allMasterIds->isNotEmpty()) {
                PMSEmployeeGoalDetail::whereIn('EmployeeGoalId', $allMasterIds)
                    ->where('Type', 2)
                    ->whereIn('Year', $submittedYears)
                    ->where(function ($q) {
                        $q->where('GoalType', 1)->orWhereNull('GoalType');
                    })
                    ->where(function ($q) use ($h1Status, $h2Status) {
                        // Protect rows that belong to a submitted cycle
                        if ($h1Status) $q->where('InH1', 0);
                        if ($h2Status) $q->where('InH2', 0);
                    })
                    ->delete();
            }

            foreach ($request->goals as $gIdx => $g) {
                $goalNumber  = $g['goal_number'] ?? ($gIdx + 1);
                $inH1        = isset($g['in_h1']) ? 1 : 0;
                $inH2        = isset($g['in_h2']) ? 1 : 0;
                $year        = (int)($g['year'] ?? date('Y'));
                $targetLabel = match(true) {
                    $inH1 && $inH2 => 'H1 & H2',
                    (bool) $inH1   => 'H1',
                    (bool) $inH2   => 'H2',
                    default        => 'Full Year',
                };

                // H1 goals (including "both") → July master; pure H2 → January master
                $month        = ($inH1 || (!$inH1 && !$inH2)) ? PMS_H1_START_MONTH : PMS_H2_START_MONTH;
                $masterGoalId = $this->resolveOrCreateMaster($employeeId, $departmentId, $year, $month, $setBy, $approvalStatus);

                $goalDetailId = UUID();
                PMSEmployeeGoalDetail::create([
                    'Id'             => $goalDetailId,
                    'EmployeeGoalId' => $masterGoalId,
                    'Type'           => 2,
                    'GoalType'       => 1,
                    'DisplayOrder'   => $goalNumber * 1000,
                    'Description'    => trim($g['description']),
                    'Weightage'      => $g['total_score'],
                    'Target'         => $targetLabel,
                    'InH1'           => $inH1,
                    'InH2'           => $inH2,
                    'Year'           => $year,
                    'CreatedBy'      => Auth::id(),
                    'created_at'     => date('Y-m-d H:i:s'),
                ]);

                foreach ($g['tasks'] ?? [] as $task) {
                    if (empty(trim($task['description'] ?? ''))) continue;

                    $target = $task['target'] ?? '';
                    if ($target === 'Custom') {
                        $target = trim($task['target_custom'] ?? '');
                    }

                    PMSEmployeeGoalTarget::create([
                        'Id'           => UUID(),
                        'GoalDetailId' => $goalDetailId,
                        'Description'  => trim($task['description']),
                        'Weightage'    => $task['weightage'],
                        'Target'       => $target ?: '-',
                        'CreatedBy'    => Auth::id(),
                        'created_at'   => date('Y-m-d H:i:s'),
                    ]);
                }
            }

            // Save common goal scores (weightage assignment by supervisor)
            $now = date('Y-m-d H:i:s');
            foreach ($cgScores as $detailId => $cgData) {
                $detail = PMSEmployeeGoalDetail::where('Id', $detailId)
                    ->whereIn('EmployeeGoalId', $allMasterIds)
                    ->where('GoalType', 2)
                    ->first();
                if (!$detail) continue;
                $detail->Weightage  = floatval($cgData['weightage'] ?? 0);
                $detail->EditedBy   = Auth::id();
                $detail->updated_at = $now;
                $detail->save();
                foreach ($cgData['tasks'] ?? [] as $taskId => $taskWeight) {
                    PMSEmployeeGoalTarget::where('Id', $taskId)
                        ->where('GoalDetailId', $detailId)
                        ->update(['Weightage' => floatval($taskWeight), 'EditedBy' => Auth::id(), 'updated_at' => $now]);
                }
            }
        });

        if ($setBy === 'individual' && $request->input('save_action') === 'submit') {
            $years = array_unique(array_column(array_values($request->goals), 'year'));
            $masterIds = DB::table('pms_employeegoal as eg')
                ->join('sys_pmsnumber as pn', 'pn.Id', '=', 'eg.SysPmsNumberId')
                ->where('eg.EmployeeId', $employeeId)
                ->where('eg.GoalSetBy', 'individual')
                ->whereIn('eg.ApprovalStatus', [0, 3])
                ->whereIn(DB::raw('YEAR(pn.StartDate)'), $years)
                ->pluck('eg.Id');
            if ($masterIds->isNotEmpty()) {
                DB::table('pms_employeegoal')
                    ->whereIn('Id', $masterIds)
                    ->update(['ApprovalStatus' => 1, 'ApprovalRemark' => null,
                              'EditedBy' => Auth::id(), 'updated_at' => date('Y-m-d H:i:s')]);
            }

            return redirect()->route('individualpmsgoal')
                ->with('successmessage', 'Goals submitted for supervisor approval.');
        }

        return redirect()->route('goals.show', $goal)
            ->with('successmessage', 'Goals updated successfully.');
    }

    public function submit(Request $request, $goal)
    {
        $employeeGoal = PMSEmployeeGoal::findOrFail($goal);
        $employeeId   = $employeeGoal->EmployeeId;

        // Only supervisors/appraisers may publish — not the employee themselves
        if ((string)Auth::id() === (string)$employeeId) {
            return back()->withErrors(['error' => 'You cannot publish your own goals.']);
        }

        // All masters for this employee — needed to sum section + common goal weightages
        $allMasterIds = PMSEmployeeGoal::where('EmployeeId', $employeeId)->pluck('Id');

        // Supervisor-set masters specifically — these are the ones we publish
        $supervisorMasterIds = PMSEmployeeGoal::where('EmployeeId', $employeeId)
            ->where('GoalSetBy', 'supervisor')
            ->pluck('Id');

        if ($supervisorMasterIds->isEmpty()) {
            return back()->withErrors(['error' => 'No supervisor-set goals found to publish.']);
        }

        // Block publishing goals once employee has started self-rating
        if ($this->hasIndividualStartedRating((string)$employeeId)) {
            return back()->withErrors(['error' =>
                'Goals cannot be published: the employee has already started their self-rating for this period.'
            ]);
        }

        // Validate cycle totals for the active window cycle(s) only
        $activeCycles = $this->activeWindowCycles();
        if (!empty($activeCycles)) {
            foreach ($activeCycles as $half) {
                $col   = $half === 'H1' ? 'InH1' : 'InH2';
                $total = (float) PMSEmployeeGoalDetail::whereIn('EmployeeGoalId', $allMasterIds)
                    ->where('Type', 2)->where($col, 1)->sum('Weightage');
                $has   = PMSEmployeeGoalDetail::whereIn('EmployeeGoalId', $allMasterIds)
                    ->where('Type', 2)->where($col, 1)->exists();
                if ($has && abs($total - 100.0) > 0.005) {
                    return back()->withErrors(['error' =>
                        "Cannot publish: {$half} total goal score (section + common goals) must be exactly 100. Current total: " .
                        number_format($total, 2) . '.'
                    ]);
                }
            }
        }

        PMSEmployeeGoal::whereIn('Id', $supervisorMasterIds)->update([
            'ApprovalStatus' => 2,
            'EditedBy'       => Auth::id(),
            'updated_at'     => date('Y-m-d H:i:s'),
        ]);

        return back()->with('successmessage', 'Goals published to employee successfully.');
    }

    public function create($employeeId = null)
    {
        $targetId = $employeeId ?? Auth::id();
        $isSelf   = (string)$targetId === (string)Auth::id();

        // Individual cannot create goals when supervisor has already set goals for any cycle
        $supervisorCycles = [];
        if ($isSelf) {
            $supervisorCycles = $this->getSupervisorSetCycles((string)$targetId);
            if (!empty($supervisorCycles)) {
                // If ALL possible cycles are covered by supervisor, block entirely
                // Otherwise let them open the form — store() will block per-goal
            }
        }

        return view('automation.creategoal', [
            'nextGoalNumber'   => 1,
            'employeeId'       => $employeeId,
            'supervisorCycles' => $supervisorCycles,
            'isIndividual'     => $isSelf,
        ]);
    }

    public function store(Request $request)
    {

        $request->validate([
            'goals'                       => 'required|array|min:1',
            'goals.*.description'         => 'required|string|max:500',
            'goals.*.total_score'         => 'required|numeric|min:0|max:100',
            'goals.*.year'                => 'required|integer|min:2000|max:2100',
            'goals.*.tasks'               => 'required|array|min:1',
            'goals.*.tasks.*.description' => 'required|string|max:500',
            'goals.*.tasks.*.weightage'   => 'required|numeric|min:0',
        ]);

        $employeeId   = $request->input('employee_id') ?: Auth::id();
        $departmentId = DB::table('mas_employee')->where('Id', $employeeId)->value('DepartmentId');
        [$h1Status, $h2Status] = $this->getCycleStatuses((string)$employeeId);

        foreach ($request->goals as $gIdx => $goal) {
            $sum        = collect($goal['tasks'] ?? [])->sum(fn($t) => floatval($t['weightage'] ?? 0));
            $totalScore = floatval($goal['total_score']);
            if (round($sum, 2) > round($totalScore, 2)) {
                $goalNum = $goal['goal_number'] ?? ($gIdx + 1);
                return back()->withInput()->withErrors([
                    "goals.{$gIdx}.tasks" => "Goal {$goalNum}: task weightages ({$sum}) exceed the goal total score ({$totalScore}).",
                ]);
            }
            // Reject goals that touch a cycle whose self-rating is already submitted
            $inH1 = !empty($goal['in_h1']);
            $inH2 = !empty($goal['in_h2']);
            if ($h1Status && $inH1) {
                $goalNum = $goal['goal_number'] ?? ($gIdx + 1);
                return back()->withInput()->withErrors(['goals' =>
                    "Goal {$goalNum} includes H1 which is already submitted and locked. Use H2-only goals for this period."
                ]);
            }
            if ($h2Status && $inH2) {
                $goalNum = $goal['goal_number'] ?? ($gIdx + 1);
                return back()->withInput()->withErrors(['goals' =>
                    "Goal {$goalNum} includes H2 which is already submitted and locked."
                ]);
            }
        }
        $setBy        = ((string)$employeeId === (string)Auth::id()) ? 'individual' : 'supervisor';

        // Block supervisor from creating goals once employee has started self-rating
        if ($setBy === 'supervisor' && $this->hasIndividualStartedRating((string)$employeeId)) {
            return back()->withErrors(['goals' =>
                'Goals cannot be added: the employee has already started their self-rating for this period.'
            ]);
        }

        $saveAction     = $request->input('save_action', 'draft');
        $approvalStatus = $setBy === 'supervisor' ? ($saveAction === 'draft' ? 0 : 2) : 0;

        // Supervisor must have total score = 100 per active window cycle when publishing
        if ($setBy === 'supervisor' && $saveAction !== 'draft') {
            $activeCycles = $this->activeWindowCycles();
            if (!empty($activeCycles)) {
                $cycleError = $this->validateCycleTotals($request->goals, (string)$employeeId, $activeCycles);
                if ($cycleError) {
                    return back()->withInput()->withErrors(['goals' => $cycleError]);
                }
            }
        }

        // Individual cannot save goals for cycles where supervisor has already set goals
        if ($setBy === 'individual') {
            $conflict = $this->checkSupervisorGoalConflict((string)$employeeId, $request->goals);
            if ($conflict) {
                return back()->withInput()->withErrors(['goals' => $conflict]);
            }
        }

        // Individual submitting for approval: validate only the active window cycle total
        if ($setBy === 'individual' && $request->input('save_action') === 'submit') {
            $cycleError = $this->validateCycleTotals($request->goals, (string)$employeeId, $this->activeWindowCycles());
            if ($cycleError) {
                return back()->withInput()->withErrors(['goals' => $cycleError]);
            }
        }

        DB::transaction(function () use ($request, $employeeId, $departmentId, $setBy, $approvalStatus, $h1Status, $h2Status) {
            // Delete section goals (GoalType=1 or legacy null), but never touch rows from a
            // cycle whose self-rating is already submitted — those are immutable historical data.
            $submittedYears = array_unique(array_column(array_values($request->goals), 'year'));
            $allMasterIds   = PMSEmployeeGoal::where('EmployeeId', $employeeId)->pluck('Id');
            if ($allMasterIds->isNotEmpty()) {
                PMSEmployeeGoalDetail::whereIn('EmployeeGoalId', $allMasterIds)
                    ->where('Type', 2)
                    ->whereIn('Year', $submittedYears)
                    ->where(function ($q) {
                        $q->where('GoalType', 1)->orWhereNull('GoalType');
                    })
                    ->where(function ($q) use ($h1Status, $h2Status) {
                        if ($h1Status) $q->where('InH1', 0);
                        if ($h2Status) $q->where('InH2', 0);
                    })
                    ->delete();
            }

            foreach ($request->goals as $gIdx => $goal) {
                $goalNumber  = $goal['goal_number'] ?? ($gIdx + 1);
                $inH1        = isset($goal['in_h1']) ? 1 : 0;
                $inH2        = isset($goal['in_h2']) ? 1 : 0;
                $year        = (int)($goal['year'] ?? date('Y'));
                $targetLabel = match(true) {
                    $inH1 && $inH2 => 'H1 & H2',
                    (bool) $inH1   => 'H1',
                    (bool) $inH2   => 'H2',
                    default        => 'Full Year',
                };

                // H1 goals (including "both") → July master; pure H2 → January master
                $month        = ($inH1 || (!$inH1 && !$inH2)) ? PMS_H1_START_MONTH : PMS_H2_START_MONTH;
                $masterGoalId = $this->resolveOrCreateMaster($employeeId, $departmentId, $year, $month, $setBy, $approvalStatus);

                // ── pms_employeegoaldetail — one row per goal ─────────────────
                $goalDetailId = UUID();
                PMSEmployeeGoalDetail::create([
                    'Id'             => $goalDetailId,
                    'EmployeeGoalId' => $masterGoalId,
                    'Type'           => 2,
                    'GoalType'       => 1,
                    'DisplayOrder'   => $goalNumber * 1000,
                    'Description'    => trim($goal['description']),
                    'Weightage'      => $goal['total_score'],
                    'Target'         => $targetLabel,
                    'InH1'           => $inH1,
                    'InH2'           => $inH2,
                    'Year'           => $year,
                    'CreatedBy'      => Auth::id(),
                    'created_at'     => date('Y-m-d H:i:s'),
                ]);

                // ── pms_employeegoaltargetdetail — one row per task ────────────
                foreach ($goal['tasks'] ?? [] as $task) {
                    if (empty(trim($task['description'] ?? ''))) continue;

                    $target = $task['target'] ?? '';
                    if ($target === 'Custom') {
                        $target = trim($task['target_custom'] ?? '');
                    }

                    PMSEmployeeGoalTarget::create([
                        'Id'           => UUID(),
                        'GoalDetailId' => $goalDetailId,
                        'Description'  => trim($task['description']),
                        'Weightage'    => $task['weightage'],
                        'Target'       => $target ?: '-',
                        'CreatedBy'    => Auth::id(),
                        'created_at'   => date('Y-m-d H:i:s'),
                    ]);
                }
            }
        });

        if ($setBy === 'individual' && $request->input('save_action') === 'submit') {
            $years = array_unique(array_column(array_values($request->goals), 'year'));
            $masterIds = DB::table('pms_employeegoal as eg')
                ->join('sys_pmsnumber as pn', 'pn.Id', '=', 'eg.SysPmsNumberId')
                ->where('eg.EmployeeId', $employeeId)
                ->where('eg.GoalSetBy', 'individual')
                ->whereIn('eg.ApprovalStatus', [0, 3])
                ->whereIn(DB::raw('YEAR(pn.StartDate)'), $years)
                ->pluck('eg.Id');
            if ($masterIds->isNotEmpty()) {
                DB::table('pms_employeegoal')
                    ->whereIn('Id', $masterIds)
                    ->update(['ApprovalStatus' => 1, 'ApprovalRemark' => null,
                              'EditedBy' => Auth::id(), 'updated_at' => date('Y-m-d H:i:s')]);
            }

            return redirect()->route('individualpmsgoal')
                ->with('successmessage', 'Goals submitted for supervisor approval.');
        }

        $redirect = $setBy === 'individual'
            ? redirect()->route('individualpmsgoal')
            : redirect()->route('goals.index');
        return $redirect->with('successmessage', 'Goals saved successfully.');
    }

    public function individualPMSGoal()
    {
        $employeeId   = Auth::id();
        $selectedYear = (int) request('year', date('Y'));

        // Exclude supervisor-set masters that are still in draft (subordinate cannot see unpublished supervisor goals)
        // Also exclude 'commongoal' template records — those are blueprints, not the employee's own goal master
        $allGoalIds = DB::table('pms_employeegoal')
            ->where('EmployeeId', $employeeId)
            ->where('GoalSetBy', '!=', 'commongoal')
            ->where(function ($q) {
                $q->where('GoalSetBy', '!=', 'supervisor')
                  ->orWhere('ApprovalStatus', 2);
            })
            ->pluck('Id');

        $goalDetails = $allGoalIds->isNotEmpty()
            ? PMSEmployeeGoalDetail::with('targets')
                ->whereIn('EmployeeGoalId', $allGoalIds)
                ->where('Type', 2)
                ->where('Year', $selectedYear)
                ->orderBy('DisplayOrder')
                ->get()
            : collect();

        $goalId = $goalDetails->isNotEmpty() ? $goalDetails->first()->EmployeeGoalId : null;

        $employee = DB::table('mas_employee')
            ->where('Id', $employeeId)
            ->select('Id', 'Name', 'EmpId')
            ->first();

        $today      = strtotime(date('Y-m-d'));
        $inH1Window = $today >= strtotime(date(CONST_PMSSETTING_FIRSTPMSSTARTDATE))
                   && $today <= strtotime(date(CONST_PMSSETTING_FIRSTPMSENDDATE));
        $inH2Window = $today >= strtotime(date(CONST_PMSSETTING_SECONDPMSSTARTDATE))
                   && $today <= strtotime(date(CONST_PMSSETTING_SECONDPMSENDDATE));
        $activeCycle = $inH1Window ? 'H1' : ($inH2Window ? 'H2' : null);

        [$h1Status, $h2Status] = $this->getCycleStatuses($employeeId);

        // Derive masters from the actual displayed goal-details so the approval gate works even when
        // resolveOrCreateMaster fell back to a different-month sys_pmsnumber (e.g. no May entry,
        // falls back to January). Matching by StartMonth would miss the fallback master entirely.
        $allRelevantMasterIds = $goalDetails->pluck('EmployeeGoalId')->unique();
        $masters = $allRelevantMasterIds->isNotEmpty()
            ? DB::table('pms_employeegoal as eg')
                ->join('sys_pmsnumber as pn', 'pn.Id', '=', 'eg.SysPmsNumberId')
                ->whereIn('eg.Id', $allRelevantMasterIds)
                ->select('eg.Id', 'eg.GoalSetBy', 'eg.ApprovalStatus', 'eg.ApprovalRemark',
                         DB::raw('MONTH(pn.StartDate) as StartMonth'))
                ->get()
            : collect();

        // Map masters to cycles via which goals reference them, not by sys_pmsnumber month
        $h1GoalMasterIds     = $goalDetails->filter(fn($g) => (bool)$g->InH1)->pluck('EmployeeGoalId')->unique();
        $h2OnlyGoalMasterIds = $goalDetails->filter(fn($g) => !(bool)$g->InH1 && (bool)$g->InH2)->pluck('EmployeeGoalId')->unique();
        $julyMaster = $masters->first(fn($m) => $h1GoalMasterIds->contains($m->Id));
        $janMaster  = $h2OnlyGoalMasterIds->isNotEmpty()
            ? $masters->first(fn($m) => $h2OnlyGoalMasterIds->contains($m->Id))
            : null;

        // Which cycles already have supervisor-set goals (individual cannot add goals for those)
        $supervisorCycles = $this->getSupervisorSetCycles((string)$employeeId, $selectedYear);

        return view('automation.mypms', compact(
            'goalDetails', 'goalId', 'selectedYear', 'employee',
            'activeCycle', 'inH1Window', 'inH2Window', 'h1Status', 'h2Status',
            'julyMaster', 'janMaster', 'supervisorCycles'
        ));
    }

    public function saveIndividualPMSGoal(Request $request)
    {
        $action      = $request->input('submission_action', 'draft');
        $cycle       = $request->input('submission_cycle'); // 'H1' or 'H2'
        $statusField = $cycle === 'H1' ? 'H1Status' : ($cycle === 'H2' ? 'H2Status' : null);
        $employeeId  = Auth::id();

        // Enforce submission window for individuals (save-draft is always allowed)
        if ($action !== 'draft') {
            $today      = strtotime(date('Y-m-d'));
            $inH1Window = $today >= strtotime(date(CONST_PMSSETTING_FIRSTPMSSTARTDATE))
                       && $today <= strtotime(date(CONST_PMSSETTING_FIRSTPMSENDDATE));
            $inH2Window = $today >= strtotime(date(CONST_PMSSETTING_SECONDPMSSTARTDATE))
                       && $today <= strtotime(date(CONST_PMSSETTING_SECONDPMSENDDATE));

            $windowAllowed = ($cycle === 'H1' && $inH1Window)
                          || ($cycle === 'H2' && $inH2Window)
                          || ($action === 'request_approval' && ($inH1Window || $inH2Window));
            if (!$windowAllowed) {
                $h1Range = date('M j', strtotime(date(CONST_PMSSETTING_FIRSTPMSSTARTDATE)))
                         . ' – ' . date('M j', strtotime(date(CONST_PMSSETTING_FIRSTPMSENDDATE)));
                $h2Range = date('M j', strtotime(date(CONST_PMSSETTING_SECONDPMSSTARTDATE)))
                         . ' – ' . date('M j', strtotime(date(CONST_PMSSETTING_SECONDPMSENDDATE)));
                return back()->withErrors(['error' =>
                    "Submission is only allowed during the PMS window: H1 ({$h1Range}) or H2 ({$h2Range})."
                ]);
            }
        }

        // Load master records scoped to the current year so approval checks and cycle-status
        // checks apply only to the year being rated, not stale prior-year masters
        $savingYear = (int)date('Y');
        $allMasters = DB::table('pms_employeegoal as eg')
            ->join('sys_pmsnumber as pn', 'pn.Id', '=', 'eg.SysPmsNumberId')
            ->where('eg.EmployeeId', $employeeId)
            ->whereYear('pn.StartDate', $savingYear)
            ->select('eg.Id', 'eg.GoalSetBy', 'eg.ApprovalStatus', DB::raw('MONTH(pn.StartDate) as StartMonth'))
            ->get();

        $allMasterIds = $allMasters->pluck('Id');

        // ── Handle: individual requests supervisor approval ──────────────────
        if ($action === 'request_approval') {
            // Validate cycle totals before submitting — goals live in DB so query them directly
            $pendingMasterIds = DB::table('pms_employeegoal')
                ->where('EmployeeId', $employeeId)
                ->where('GoalSetBy', 'individual')
                ->whereIn('ApprovalStatus', [0, 3])
                ->pluck('Id');

            if ($pendingMasterIds->isNotEmpty()) {
                $h1Total = (float) PMSEmployeeGoalDetail::whereIn('EmployeeGoalId', $pendingMasterIds)
                    ->where('Type', 2)->where('InH1', 1)->sum('Weightage');
                $h2Total = (float) PMSEmployeeGoalDetail::whereIn('EmployeeGoalId', $pendingMasterIds)
                    ->where('Type', 2)->where('InH2', 1)->sum('Weightage');
                $hasH1   = PMSEmployeeGoalDetail::whereIn('EmployeeGoalId', $pendingMasterIds)
                    ->where('Type', 2)->where('InH1', 1)->exists();
                $hasH2   = PMSEmployeeGoalDetail::whereIn('EmployeeGoalId', $pendingMasterIds)
                    ->where('Type', 2)->where('InH2', 1)->exists();

                if ($hasH1 && abs($h1Total - 100.0) > 0.005) {
                    return back()->withErrors(['error' =>
                        'Cannot submit for approval: H1 total goal score must be exactly 100. Current total: ' .
                        number_format($h1Total, 2) . '.'
                    ]);
                }
                if ($hasH2 && abs($h2Total - 100.0) > 0.005) {
                    return back()->withErrors(['error' =>
                        'Cannot submit for approval: H2 total goal score must be exactly 100. Current total: ' .
                        number_format($h2Total, 2) . '.'
                    ]);
                }
            }

            DB::table('pms_employeegoal')
                ->where('EmployeeId', $employeeId)
                ->where('GoalSetBy', 'individual')
                ->whereIn('ApprovalStatus', [0, 3]) // draft or previously rejected
                ->update([
                    'ApprovalStatus' => 1,
                    'ApprovalRemark' => null,
                    'EditedBy'       => Auth::id(),
                    'updated_at'     => date('Y-m-d H:i:s'),
                ]);
            return back()->with('successmessage', 'Goals submitted for supervisor approval.');
        }

        // ── Block if this cycle is already submitted ──────────────────────────
        if ($statusField && $allMasterIds->isNotEmpty()) {
            $alreadySubmitted = DB::table('pms_employeegoal')
                ->whereIn('Id', $allMasterIds)
                ->where($statusField, 1)
                ->exists();
            if ($alreadySubmitted) {
                return back()->withErrors(['error' => "{$cycle} self-rating has already been submitted and cannot be modified."]);
            }
        }

        // ── Block if individual-set goals are not yet approved ────────────────
        if (in_array($action, ['draft', 'submit']) && $cycle) {
            $cycleColumn  = $cycle === 'H1' ? 'InH1' : 'InH2';
            $blockedMaster = $allMasters->first(function ($m) {
                return $m->GoalSetBy === 'individual' && (int)$m->ApprovalStatus !== 2;
            });
            // Confirm the blocked master actually has goals for this cycle
            if ($blockedMaster) {
                $hasCycleGoals = PMSEmployeeGoalDetail::where('EmployeeGoalId', $blockedMaster->Id)
                    ->where('Type', 2)
                    ->where($cycleColumn, 1)
                    ->exists();
                if ($hasCycleGoals) {
                    $statusLabel = match((int)$blockedMaster->ApprovalStatus) {
                        0 => 'not yet submitted for approval',
                        1 => 'pending supervisor approval',
                        3 => 'rejected by supervisor — please revise and resubmit',
                        default => 'not approved',
                    };
                    return back()->withErrors(['error' =>
                        "Cannot save {$cycle} self-rating: your goals are {$statusLabel}."
                    ]);
                }
            }
        }

        // ── On submit, total goal weightage for the cycle must equal 100 ──────
        if ($action === 'submit' && $cycle && $allMasterIds->isNotEmpty()) {
            $cycleColumn = $cycle === 'H1' ? 'InH1' : 'InH2';
            $totalWeight = (float) PMSEmployeeGoalDetail::whereIn('EmployeeGoalId', $allMasterIds)
                ->where('Type', 2)
                ->where($cycleColumn, 1)
                ->sum('Weightage');
            if (abs($totalWeight - 100.0) > 0.005) {
                return back()->withErrors(['error' =>
                    "Cannot submit {$cycle}: total goal score must be exactly 100. " .
                    "Current total is " . number_format($totalWeight, 2) . "."
                ]);
            }
        }

        $newCycleStatus = $action === 'submit' ? 1 : 0;

        DB::transaction(function () use ($request, $allMasterIds, $statusField, $newCycleStatus) {
            // Update cycle status on ALL masters for this employee
            if ($statusField && $allMasterIds->isNotEmpty()) {
                PMSEmployeeGoal::whereIn('Id', $allMasterIds)->update([
                    $statusField => $newCycleStatus,
                    'EditedBy'   => Auth::id(),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }

            foreach ($request->input('goals', []) as $goalDetailId => $goalData) {
                PMSEmployeeGoalDetail::where('Id', $goalDetailId)
                    ->update([
                        'SelfScore'   => $goalData['self_score'] ?? null,
                        'SelfRemarks' => $goalData['self_remarks'] ?? null,
                        'EditedBy'    => Auth::id(),
                        'updated_at'  => date('Y-m-d H:i:s'),
                    ]);

                foreach ($goalData['tasks'] ?? [] as $taskId => $taskData) {
                    PMSEmployeeGoalTarget::where('Id', $taskId)
                        ->update([
                            'SelfScore'   => $taskData['self_score'] ?? null,
                            'SelfRemarks' => $taskData['self_remarks'] ?? null,
                            'Achievement' => $taskData['achievement'] ?? null,
                            'EditedBy'    => Auth::id(),
                            'updated_at'  => date('Y-m-d H:i:s'),
                        ]);
                }
            }
        });

        $msg = $newCycleStatus === 1
            ? "{$cycle} self-rating submitted successfully."
            : "{$cycle} draft saved successfully.";
        return back()->with('successmessage', $msg);
    }

    // ── Import ──────────────────────────────────────────────────────────────

    public function importForm(Request $request)
    {
        $employeeId = $request->query('employeeId');

        if ($request->query('download') == '1') {
            return $this->downloadGoalTemplate();
        }

        $targetId      = $employeeId ?? Auth::id();
        $isSelf        = (string)$targetId === (string)Auth::id();
        $supervisorCycles = $isSelf ? $this->getSupervisorSetCycles((string)$targetId) : [];

        return view('automation.importgoal', [
            'goalsJson'        => null,
            'goalsArray'       => [],
            'employeeId'       => $employeeId,
            'supervisorCycles' => $supervisorCycles,
            'isIndividual'     => $isSelf,
        ]);
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls']);

        $employeeId  = $request->input('employee_id') ?: Auth::id();
        $importYear  = (int)$request->input('import_year', (int)date('Y'));

        // Block individual from importing when supervisor has set goals for any cycle in this year
        $isSelf = (string)$employeeId === (string)Auth::id();
        if ($isSelf) {
            $supervisorCycles = $this->getSupervisorSetCycles((string)$employeeId, $importYear);
            if (!empty($supervisorCycles)) {
                $cycleList = implode(', ', array_map(fn($c) => "H{$c['half']} {$c['year']}", $supervisorCycles));
                return back()->withErrors(['file' =>
                    "Cannot import: your supervisor has already set goals for {$cycleList}. " .
                    "Only the supervisor can modify those goals."
                ]);
            }
        }

        $spreadsheet = IOFactory::load($request->file('file')->getPathname());
        $rows        = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);
        array_shift($rows); // remove header row

        $goals      = [];
        $lastGoalNo = null;
        $skipGoalNo = null; // goal number being skipped due to year mismatch

        foreach ($rows as $row) {
            $taskDesc = trim($row[6] ?? '');
            if ($taskDesc === '') continue;

            $rawGoalNo = trim($row[0] ?? '');
            if ($rawGoalNo !== '' && $rawGoalNo !== null) {
                $lastGoalNo = $rawGoalNo;
                $skipGoalNo = null; // reset skip on new goal header

                // Year filtering: skip goals whose Year column doesn't match import year
                $rowYear = trim($row[3] ?? '');
                if ($rowYear !== '' && (int)$rowYear !== $importYear) {
                    $skipGoalNo = $lastGoalNo;
                }
            }
            if ($lastGoalNo === null) continue;
            if ($skipGoalNo !== null && $skipGoalNo === $lastGoalNo) continue;

            if (!isset($goals[$lastGoalNo])) {
                $h1 = strtolower(trim($row[4] ?? ''));
                $h2 = strtolower(trim($row[5] ?? ''));
                $rowYear = trim($row[3] ?? '');
                $goals[$lastGoalNo] = [
                    'goal_number' => (int)$lastGoalNo,
                    'description' => trim($row[1] ?? ''),
                    'total_score' => (float)($row[2] ?? 0),
                    'year'        => $rowYear !== '' ? (int)$rowYear : $importYear,
                    'in_h1'       => in_array($h1, ['y', 'yes', '1']),
                    'in_h2'       => in_array($h2, ['y', 'yes', '1']),
                    'tasks'       => [],
                ];
            }

            $goals[$lastGoalNo]['tasks'][] = [
                'description' => $taskDesc,
                'weightage'   => (float)($row[7] ?? 0),
                'target'      => trim($row[8] ?? '') ?: '-',
            ];
        }

        if (empty($goals)) {
            return back()->withErrors(['file' => 'No valid goal rows found for year ' . $importYear . '. Check that the Year column matches the selected PMS year.']);
        }

        $goalsArray       = array_values($goals);
        $isSelf           = (string)$employeeId === (string)Auth::id();
        $supervisorCycles = $isSelf ? $this->getSupervisorSetCycles((string)$employeeId) : [];

        return view('automation.importgoal', [
            'goalsArray'       => $goalsArray,
            'employeeId'       => $employeeId,
            'goalsJson'        => true,
            'importYear'       => $importYear,
            'supervisorCycles' => $supervisorCycles,
            'isIndividual'     => $isSelf,
        ]);
    }

    private function downloadGoalTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Goals Template');

        $headers = [
            'Goal No', 'Goal Description', 'Total Score (Goal)', 'Year',
            'H1 (Y/N)', 'H2 (Y/N)',
            'Task Description', 'Task Weightage', 'Task Target',
        ];
        foreach ($headers as $i => $header) {
            $col = Coordinate::stringFromColumnIndex($i + 1);
            $sheet->setCellValue($col . '1', $header);
            $sheet->getStyle($col . '1')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
            $sheet->getStyle($col . '1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('226b86');
        }

        $samples = [
            [1, 'Improve team productivity', 40, date('Y'), 'Y', 'N', 'Complete key project on time', 20, 'Q1'],
            [1, '',                           '',  '',        '',  '',  'Reduce rework by 30%',          20, 'Q2'],
            [2, 'Customer satisfaction',      60, date('Y'), 'N', 'Y', 'Achieve CSAT score >= 4.5',     60, 'Q3'],
        ];
        foreach ($samples as $r => $sample) {
            $row = $r + 2;
            foreach ($sample as $c => $value) {
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($c + 1) . $row, $value);
            }
        }

        foreach ([8, 40, 18, 8, 8, 8, 40, 14, 14] as $i => $w) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($i + 1))->setWidth($w);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="goal_import_template.xlsx"');
        header('Cache-Control: max-age=0');
        ob_end_clean();
        $writer->save('php://output');
        exit;
    }

    // ── Helpers ─────────────────────────────────────────────────────────────

    /**
     * Return [h1Status, h2Status] aggregated across all pms_employeegoal records.
     * H1Status and H2Status are independent fields — aggregate max() across all masters
     * regardless of which sys_pmsnumber month they were created under (the month-based
     * split breaks when resolveOrCreateMaster falls back to a non-canonical PMS entry).
     */
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

    /**
     * Find or create the pms_employeegoal master for the given employee, year,
     * and half (month=PMS_H1_START_MONTH for H1, month=PMS_H2_START_MONTH for H2).
     * Returns the master Id.
     */
    private function resolveOrCreateMaster(string $employeeId, string $departmentId, int $year, int $month, string $setBy = 'supervisor', int $approvalStatus = -1): string
    {
        $pmsId = DB::table('sys_pmsnumber')
            ->whereYear('StartDate', '=', $year)
            ->whereMonth('StartDate', '=', $month)
            ->value('Id');

        if (!$pmsId) {
            $pmsId = DB::table('sys_pmsnumber')
                ->where('StartDate', '<=', date('Y-m-d'))
                ->orderBy('StartDate', 'DESC')
                ->value('Id');
        }

        // -1 means caller did not specify: use default (supervisor=published, individual=draft)
        if ($approvalStatus === -1) {
            $approvalStatus = $setBy === 'supervisor' ? 2 : 0;
        }

        $master = PMSEmployeeGoal::where('EmployeeId', $employeeId)
            ->where('SysPmsNumberId', $pmsId)
            ->first();

        if ($master) {
            $master->update([
                'GoalSetBy'      => $setBy,
                'ApprovalStatus' => $approvalStatus,
                'ApprovalRemark' => null,
                'EditedBy'       => Auth::id(),
                'updated_at'     => date('Y-m-d H:i:s'),
            ]);
            return $master->Id;
        }

        $level1Count = DB::table('mas_hierarchy')->where('EmployeeId', $employeeId)->distinct('ReportingLevel1EmployeeId')->count('ReportingLevel1EmployeeId');
        $level2Count = DB::table('mas_hierarchy')->where('EmployeeId', $employeeId)->distinct('ReportingLevel2EmployeeId')->count('ReportingLevel2EmployeeId');

        $masterId = UUID();
        PMSEmployeeGoal::create([
            'Id'                      => $masterId,
            'SysPmsNumberId'          => $pmsId,
            'EmployeeId'              => $employeeId,
            'DepartmentId'            => $departmentId,
            'MultipleLevel1Appraiser' => $level1Count > 1 ? 1 : 0,
            'MultipleLevel2Appraiser' => $level2Count > 1 ? 1 : 0,
            'Status'                  => 0,
            'H1Status'                => 0,
            'H2Status'                => 0,
            'GoalSetBy'               => $setBy,
            'ApprovalStatus'          => $approvalStatus,
            'CreatedBy'               => Auth::id(),
            'created_at'              => date('Y-m-d H:i:s'),
        ]);

        return $masterId;
    }

    public function approveGoals(Request $request)
    {
        $employeeId = $request->input('employee_id');
        $remark     = trim($request->input('remark', ''));
        DB::table('pms_employeegoal')
            ->where('EmployeeId', $employeeId)
            ->where('GoalSetBy', 'individual')
            ->where('ApprovalStatus', 1)
            ->update([
                'ApprovalStatus' => 2,
                'ApprovalRemark' => $remark ?: null,
                'EditedBy'       => Auth::id(),
                'updated_at'     => date('Y-m-d H:i:s'),
            ]);
        return back()->with('successmessage', 'Goals approved successfully. The employee may now submit self-ratings.');
    }

    public function rejectGoals(Request $request)
    {
        $employeeId = $request->input('employee_id');
        $remark     = trim($request->input('remark', ''));
        if ($remark === '') {
            return back()->withErrors(['remark' => 'A reason is required when rejecting goals.']);
        }
        DB::table('pms_employeegoal')
            ->where('EmployeeId', $employeeId)
            ->where('GoalSetBy', 'individual')
            ->where('ApprovalStatus', 1)
            ->update([
                'ApprovalStatus' => 3,
                'ApprovalRemark' => $remark,
                'EditedBy'       => Auth::id(),
                'updated_at'     => date('Y-m-d H:i:s'),
            ]);
        return back()->with('successmessage', 'Goals rejected. The employee will be notified to revise and resubmit.');
    }

    /**
     * Returns which PMS half-year cycles are currently within their submission window.
     * An empty array means we are outside all windows.
     */
    private function activeWindowCycles(): array
    {
        $today  = strtotime(date('Y-m-d'));
        $cycles = [];
        if ($today >= strtotime(date(CONST_PMSSETTING_FIRSTPMSSTARTDATE))
         && $today <= strtotime(date(CONST_PMSSETTING_FIRSTPMSENDDATE))) {
            $cycles[] = 'H1';
        }
        if ($today >= strtotime(date(CONST_PMSSETTING_SECONDPMSSTARTDATE))
         && $today <= strtotime(date(CONST_PMSSETTING_SECONDPMSENDDATE))) {
            $cycles[] = 'H2';
        }
        return $cycles;
    }

    /**
     * Validates that goal weightages sum to exactly 100 per requested cycle.
     * $checkCycles: which cycles to enforce ('H1', 'H2', or both). Defaults to both.
     * Returns an error string on failure, or null if valid.
     */
    private function validateCycleTotals(array $goals, ?string $employeeId = null, array $checkCycles = ['H1', 'H2']): ?string
    {
        $h1Total = 0.0;
        $h2Total = 0.0;
        $hasH1   = false;
        $hasH2   = false;

        foreach ($goals as $goal) {
            $inH1  = isset($goal['in_h1']) && $goal['in_h1'];
            $inH2  = isset($goal['in_h2']) && $goal['in_h2'];
            $score = floatval($goal['total_score'] ?? 0);
            if ($inH1) { $h1Total += $score; $hasH1 = true; }
            if ($inH2) { $h2Total += $score; $hasH2 = true; }
        }

        // Include common goal (GoalType=2) scores already saved in the DB for this employee
        if ($employeeId) {
            $masterIds = DB::table('pms_employeegoal')->where('EmployeeId', $employeeId)->pluck('Id');
            if ($masterIds->isNotEmpty()) {
                $cgH1 = (float) PMSEmployeeGoalDetail::whereIn('EmployeeGoalId', $masterIds)
                    ->where('GoalType', 2)->where('InH1', 1)->sum('Weightage');
                $cgH2 = (float) PMSEmployeeGoalDetail::whereIn('EmployeeGoalId', $masterIds)
                    ->where('GoalType', 2)->where('InH2', 1)->sum('Weightage');
                $hasCgH1 = PMSEmployeeGoalDetail::whereIn('EmployeeGoalId', $masterIds)
                    ->where('GoalType', 2)->where('InH1', 1)->exists();
                $hasCgH2 = PMSEmployeeGoalDetail::whereIn('EmployeeGoalId', $masterIds)
                    ->where('GoalType', 2)->where('InH2', 1)->exists();
                if ($hasCgH1) { $h1Total += $cgH1; $hasH1 = true; }
                if ($hasCgH2) { $h2Total += $cgH2; $hasH2 = true; }
            }
        }

        if (in_array('H1', $checkCycles) && $hasH1 && abs($h1Total - 100.0) > 0.005) {
            return 'H1 total goal score (section + common goals) must be exactly 100. Current total: ' . number_format($h1Total, 2) . '.';
        }
        if (in_array('H2', $checkCycles) && $hasH2 && abs($h2Total - 100.0) > 0.005) {
            return 'H2 total goal score (section + common goals) must be exactly 100. Current total: ' . number_format($h2Total, 2) . '.';
        }

        return null;
    }

    /**
     * Returns an array of supervisor-set cycles for the employee.
     * Each entry: ['half' => 1|2, 'year' => int, 'month' => 7|1]
     * Optionally filter by year.
     */
    private function getSupervisorSetCycles(string $employeeId, ?int $year = null): array
    {
        $query = DB::table('pms_employeegoal as eg')
            ->join('sys_pmsnumber as pn', 'pn.Id', '=', 'eg.SysPmsNumberId')
            ->where('eg.EmployeeId', $employeeId)
            ->where('eg.GoalSetBy', 'supervisor')
            ->select(DB::raw('YEAR(pn.StartDate) as StartYear'), DB::raw('MONTH(pn.StartDate) as StartMonth'));

        if ($year) {
            $query->whereYear('pn.StartDate', '=', $year);
        }

        $cycles = [];
        foreach ($query->get() as $row) {
            $month = (int)$row->StartMonth;
            $cycles[] = [
                'half'  => $month === PMS_H1_START_MONTH ? 1 : 2,
                'year'  => (int)$row->StartYear,
                'month' => $month,
            ];
        }
        return $cycles;
    }

    /**
     * Returns true if the employee has begun any self-rating activity:
     * either saved a draft SelfScore or formally submitted a cycle (H1/H2Status=1).
     * Once this is true, supervisors/appraisers can no longer edit or add goals.
     */
    private function hasIndividualStartedRating(string $employeeId): bool
    {
        $hasSubmitted = DB::table('pms_employeegoal')
            ->where('EmployeeId', $employeeId)
            ->where(function ($q) { $q->where('H1Status', 1)->orWhere('H2Status', 1); })
            ->exists();

        if ($hasSubmitted) return true;

        $masterIds = DB::table('pms_employeegoal')
            ->where('EmployeeId', $employeeId)
            ->pluck('Id');

        if ($masterIds->isEmpty()) return false;

        return PMSEmployeeGoalDetail::whereIn('EmployeeGoalId', $masterIds)
            ->whereNotNull('SelfScore')
            ->exists();
    }

    /**
     * Checks if the given goals array (from the request) conflicts with supervisor-set masters.
     * Returns an error string on conflict, or null if no conflict.
     */
    private function checkSupervisorGoalConflict(string $employeeId, array $goals): ?string
    {
        $checked = [];
        foreach ($goals as $goal) {
            $year  = (int)($goal['year'] ?? date('Y'));
            $inH1  = isset($goal['in_h1']) ? 1 : 0;
            $inH2  = isset($goal['in_h2']) ? 1 : 0;
            $month = ($inH1 || (!$inH1 && !$inH2)) ? PMS_H1_START_MONTH : PMS_H2_START_MONTH;
            $key   = "{$year}-{$month}";

            if (isset($checked[$key])) continue;
            $checked[$key] = true;

            $pmsId = DB::table('sys_pmsnumber')
                ->whereYear('StartDate', '=', $year)
                ->whereMonth('StartDate', '=', $month)
                ->value('Id');

            if ($pmsId) {
                $supervisorSet = DB::table('pms_employeegoal')
                    ->where('EmployeeId', $employeeId)
                    ->where('SysPmsNumberId', $pmsId)
                    ->where('GoalSetBy', 'supervisor')
                    ->exists();

                if ($supervisorSet) {
                    $cycleLabel = $month === PMS_H1_START_MONTH ? 'H1' : 'H2';
                    return "Cannot set your own {$cycleLabel} goals for {$year}: " .
                           "your supervisor has already set goals for this cycle. " .
                           "Only the supervisor can modify these goals.";
                }
            }
        }
        return null;
    }

}
