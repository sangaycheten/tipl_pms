<?php

namespace App\Http\Controllers\Automation;

use App\Http\Controllers\Controller;
use App\CommonGoal;
use App\PMSEmployeeGoalDetail;
use App\PMSEmployeeGoalTarget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CommonGoalController extends Controller
{
    // GoalType=2 in pms_employeegoaldetail → common goal detail row
    const GOAL_TYPE = 2;

    // ──────────────────────────────────────────────────────────────────────────
    //  INDEX
    // ──────────────────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $selectedYear = (int) $request->input('year', date('Y'));

        $goals = CommonGoal::with('assignments')
            ->where('Year', $selectedYear)
            ->orderBy('Id')
            ->get();

        // Attach goal detail list from pms_employeegoaldetail
        // Template details are identified by: GoalType=2 + CommonGoalId=X + authored by current user
        foreach ($goals as $goal) {
            $goal->goalDetails = PMSEmployeeGoalDetail::where('GoalType', self::GOAL_TYPE)
                ->where('CommonGoalId', $goal->Id)
                ->whereIn('EmployeeGoalId', function ($q) {
                    $q->select('Id')->from('pms_employeegoal')
                      ->where('EmployeeId', Auth::id());
                })
                ->orderBy('DisplayOrder')
                ->get(['Id', 'Description', 'Weightage', 'InH1', 'InH2', 'DisplayOrder']);

            $goal->goalCount = $goal->goalDetails->count();
            $goal->isLocked  = $this->isLockedByActivity((int) $goal->Id, (int) $goal->Year);
        }

        return view('commongoals.index', compact('goals', 'selectedYear'));
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  CREATE
    // ──────────────────────────────────────────────────────────────────────────
    public function create(Request $request)
    {
        $selectedYear   = (int) $request->input('year', date('Y'));
        $nextGoalNumber = 1;

        $departments = DB::table('mas_department')
            ->where('Status', 1)->orderBy('Name')
            ->get(['Id', 'Name']);

        return view('commongoals.create', compact('selectedYear', 'nextGoalNumber', 'departments'));
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  AJAX: employees by dept
    // ──────────────────────────────────────────────────────────────────────────
    public function getEmployeesByDept(Request $request)
    {
        $deptIds = array_filter(explode(',', $request->input('dept_ids', '')));
        if (empty($deptIds)) return response()->json([]);

        $sections  = DB::table('mas_section')
            ->whereIn('DepartmentId', $deptIds)->where('Status', 1)
            ->orderBy('DepartmentId')->orderBy('Name')
            ->get(['Id', 'Name', 'DepartmentId']);

        $employees = DB::table('mas_employee')
            ->whereIn('DepartmentId', $deptIds)->where('Status', 1)
            ->orderBy('DepartmentId')->orderBy('Name')
            ->get(['Id', 'Name', 'DepartmentId', 'SectionId', 'EmpId']);

        $deptMap    = DB::table('mas_department')
            ->whereIn('Id', $deptIds)->where('Status', 1)->orderBy('Name')
            ->get(['Id', 'Name'])->keyBy('Id');
        $sectionMap = $sections->keyBy('Id');

        $result = [];
        foreach ($deptMap as $dId => $dept) {
            $deptSections = $sections->where('DepartmentId', $dId)->values();
            $sectionsData = [];
            foreach ($deptSections as $sec) {
                $secEmps = $employees->where('DepartmentId', $dId)
                                     ->where('SectionId', $sec->Id)->values();
                $sectionsData[] = [
                    'sec_id'    => $sec->Id,
                    'sec_name'  => $sec->Name,
                    'employees' => $secEmps->map(fn($e) => [
                        'id' => $e->Id, 'name' => $e->Name, 'emp_id' => $e->EmpId,
                    ])->values(),
                ];
            }
            $noSec = $employees->where('DepartmentId', $dId)
                               ->filter(fn($e) => is_null($e->SectionId) || !$sectionMap->has($e->SectionId))
                               ->values();
            $result[] = [
                'dept_id'              => $dept->Id,
                'dept_name'            => $dept->Name,
                'sections'             => $sectionsData,
                'no_section_employees' => $noSec->map(fn($e) => [
                    'id' => $e->Id, 'name' => $e->Name, 'emp_id' => $e->EmpId,
                ])->values(),
            ];
        }

        return response()->json($result);
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  STORE
    // ──────────────────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'year'                        => 'required|integer|min:2000|max:2100',
            'goals'                       => 'required|array|min:1',
            'goals.*.description'         => 'required|string|max:500',
            'goals.*.tasks'               => 'required|array|min:1',
            'goals.*.tasks.*.description' => 'required|string|max:500',
            'assigned_employees'          => 'nullable|array',
            'assigned_employees.*'        => 'integer',
        ]);

        $year        = (int) $request->year;
        $saveAction  = $request->input('save_action', 'draft');
        $assignedEmp = $request->input('assigned_employees', []);
        $now         = date('Y-m-d H:i:s');

        DB::transaction(function () use ($request, $year, $saveAction, $assignedEmp, $now) {
            $master = CommonGoal::create([
                'Year'       => $year,
                'Title'      => $request->input('title', 'Common Goals ' . $year),
                'Status'     => $saveAction === 'publish' ? 'published' : 'draft',
                'CreatedBy'  => Auth::id(),
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // Save goals into the supervisor's own pms_employeegoal master row
            $this->saveGoalDetails($master->Id, $request->goals, $year, $now);
            $this->saveAssignments($master->Id, $assignedEmp, $now);

            if ($saveAction === 'publish' && !empty($assignedEmp)) {
                $this->publishToEmployees($master->Id, $assignedEmp, $request->goals, $year, $now);
            }
        });

        return redirect()->route('commongoal.index', ['year' => $year])
                         ->with('successmessage', 'Common goals saved successfully.');
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  SHOW
    // ──────────────────────────────────────────────────────────────────────────
    public function show(int $id)
    {
        $commonGoal = CommonGoal::with('assignments')->findOrFail($id);

        // Template details: GoalType=2 + CommonGoalId=X, in the supervisor's own master
        $goalDetails = PMSEmployeeGoalDetail::with('targets')
            ->where('GoalType', self::GOAL_TYPE)
            ->where('CommonGoalId', $commonGoal->Id)
            ->whereIn('EmployeeGoalId', function ($q) {
                $q->select('Id')->from('pms_employeegoal')
                  ->where('EmployeeId', Auth::id());
            })
            ->orderBy('DisplayOrder')
            ->get();

        $empIds = $commonGoal->assignments->pluck('EmployeeId')->toArray();
        $assignedEmployees = collect();
        if (!empty($empIds)) {
            $assignedEmployees = DB::table('mas_employee as e')
                ->join('mas_department as d', 'd.Id', '=', 'e.DepartmentId')
                ->leftJoin('mas_section as s', 's.Id', '=', 'e.SectionId')
                ->whereIn('e.Id', $empIds)
                ->orderBy('d.Name')->orderBy('e.Name')
                ->get(['e.Id', 'e.Name', 'e.EmpId', 'd.Name as DeptName', 's.Name as SectionName']);
        }

        $isLocked = $this->isLockedByActivity($id);

        $publishConfirmMsg = $commonGoal->Status === 'published'
            ? 'Re-push goals to all assigned employees? This will overwrite any previously published copies.'
            : 'Publish these common goals to all assigned employees? Their supervisors will receive them to set individual weightages.';

        return view('commongoals.show', compact('commonGoal', 'goalDetails', 'assignedEmployees', 'isLocked', 'publishConfirmMsg'));
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  EDIT
    // ──────────────────────────────────────────────────────────────────────────
    public function edit(int $id)
    {
        $commonGoal = CommonGoal::with('assignments')->findOrFail($id);

        if ($this->isLockedByActivity($id)) {
            return redirect()->route('commongoal.show', $id)
                ->with('infomessage', 'This common goal set cannot be edited because one or more assigned employees have already started their self-rating.');
        }

        $goalDetails = PMSEmployeeGoalDetail::with('targets')
            ->where('GoalType', self::GOAL_TYPE)
            ->where('CommonGoalId', $commonGoal->Id)
            ->whereIn('EmployeeGoalId', function ($q) {
                $q->select('Id')->from('pms_employeegoal')
                  ->where('EmployeeId', Auth::id());
            })
            ->orderBy('DisplayOrder')
            ->get();

        $goalsJson = $goalDetails->map(fn($g) => [
            'goal_number' => (int) ($g->DisplayOrder / 1000),
            'description' => $g->Description,
            'year'        => $commonGoal->Year,
            'in_h1'       => (bool) $g->InH1,
            'in_h2'       => (bool) $g->InH2,
            'tasks'       => $g->targets->map(fn($t) => [
                'description'   => $t->Description,
                'target'        => $t->Target,
                'target_custom' => '',
            ])->values()->toArray(),
        ])->values()->toArray();

        $nextGoalNumber = $goalDetails->isNotEmpty()
            ? ((int) ($goalDetails->max('DisplayOrder') / 1000)) + 1
            : 1;

        $departments = DB::table('mas_department')
            ->where('Status', 1)->orderBy('Name')
            ->get(['Id', 'Name']);

        $assignedEmpIds = $commonGoal->assignments->pluck('EmployeeId')->toArray();
        $assignedEmpDetails = collect();
        if (!empty($assignedEmpIds)) {
            $assignedEmpDetails = DB::table('mas_employee as e')
                ->join('mas_department as d', 'd.Id', '=', 'e.DepartmentId')
                ->leftJoin('mas_section as s', 's.Id', '=', 'e.SectionId')
                ->whereIn('e.Id', $assignedEmpIds)
                ->orderBy('d.Name')->orderBy('e.Name')
                ->get(['e.Id', 'e.Name', 'e.EmpId', 'e.DepartmentId', 'e.SectionId',
                       'd.Name as DeptName', 's.Name as SectionName']);
        }
        $preSelectedDeptIds = $assignedEmpDetails->pluck('DepartmentId')->unique()->values()->toArray();

        return view('commongoals.edit', compact(
            'commonGoal', 'goalsJson', 'nextGoalNumber',
            'departments', 'assignedEmpDetails', 'preSelectedDeptIds'
        ));
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  UPDATE
    // ──────────────────────────────────────────────────────────────────────────
    public function update(Request $request, int $id)
    {
        $request->validate([
            'goals'                       => 'required|array|min:1',
            'goals.*.description'         => 'required|string|max:500',
            'goals.*.tasks'               => 'required|array|min:1',
            'goals.*.tasks.*.description' => 'required|string|max:500',
            'assigned_employees'          => 'nullable|array',
            'assigned_employees.*'        => 'integer',
        ]);

        $commonGoal  = CommonGoal::findOrFail($id);

        if ($this->isLockedByActivity($id)) {
            return redirect()->route('commongoal.show', $id)
                ->with('infomessage', 'This common goal set cannot be edited because one or more assigned employees have already started their self-rating.');
        }

        $year        = $commonGoal->Year;
        $saveAction  = $request->input('save_action', 'draft');
        $assignedEmp = $request->input('assigned_employees', []);
        $now         = date('Y-m-d H:i:s');

        DB::transaction(function () use ($request, $commonGoal, $year, $saveAction, $assignedEmp, $now) {
            $commonGoal->Status     = $saveAction === 'publish' ? 'published' : 'draft';
            $commonGoal->EditedBy   = Auth::id();
            $commonGoal->updated_at = $now;
            $commonGoal->save();

            // Delete all detail rows (template + published) for this common goal, then re-save
            $this->deleteAllDetails($commonGoal->Id);
            $this->saveGoalDetails($commonGoal->Id, $request->goals, $year, $now);

            $commonGoal->assignments()->delete();
            $this->saveAssignments($commonGoal->Id, $assignedEmp, $now);

            if ($saveAction === 'publish' && !empty($assignedEmp)) {
                $this->publishToEmployees($commonGoal->Id, $assignedEmp, $request->goals, $year, $now);
            }
        });

        return redirect()->route('commongoal.index', ['year' => $year])
                         ->with('successmessage', 'Common goals updated successfully.');
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  DESTROY
    // ──────────────────────────────────────────────────────────────────────────
    public function destroy(int $id)
    {
        $commonGoal = CommonGoal::findOrFail($id);
        $year       = $commonGoal->Year;

        if ($this->isLockedByActivity($id)) {
            return redirect()->route('commongoal.show', $id)
                ->with('infomessage', 'This common goal set cannot be deleted because one or more assigned employees have already started their self-rating.');
        }

        DB::transaction(function () use ($commonGoal) {
            $this->deleteAllDetails($commonGoal->Id);
            $commonGoal->assignments()->delete();
            $commonGoal->delete();
        });

        return redirect()->route('commongoal.index', ['year' => $year])
                         ->with('successmessage', 'Common goal set deleted successfully.');
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  PUBLISH (one-click from show page, without re-submitting the form)
    // ──────────────────────────────────────────────────────────────────────────
    public function publish(int $id)
    {
        $commonGoal = CommonGoal::with('assignments')->findOrFail($id);

        $year   = $commonGoal->Year;
        $now    = date('Y-m-d H:i:s');
        $empIds = $commonGoal->assignments->pluck('EmployeeId')->toArray();

        // Read template details (from supervisor's own master row)
        $templateDetails = PMSEmployeeGoalDetail::with('targets')
            ->where('GoalType', self::GOAL_TYPE)
            ->where('CommonGoalId', $commonGoal->Id)
            ->whereIn('EmployeeGoalId', function ($q) {
                $q->select('Id')->from('pms_employeegoal')
                  ->where('EmployeeId', Auth::id());
            })
            ->orderBy('DisplayOrder')
            ->get();

        DB::transaction(function () use ($commonGoal, $year, $empIds, $templateDetails, $now) {
            // Remove any previously published copies (keep supervisor template rows)
            $templateIds = $templateDetails->pluck('Id');
            $staleIds = DB::table('pms_employeegoaldetail')
                ->where('CommonGoalId', $commonGoal->Id)
                ->where('GoalType', self::GOAL_TYPE)
                ->whereNotIn('Id', $templateIds->toArray())
                ->pluck('Id');

            if ($staleIds->isNotEmpty()) {
                DB::table('pms_employeegoaltargetdetail')
                    ->whereIn('GoalDetailId', $staleIds)->delete();
                DB::table('pms_employeegoaldetail')
                    ->whereIn('Id', $staleIds)->delete();
            }

            // Push goals into each assigned employee's master row
            foreach ($empIds as $empId) {
                foreach ($templateDetails as $tpl) {
                    $month    = ($tpl->InH1 || (!$tpl->InH1 && !$tpl->InH2)) ? PMS_H1_START_MONTH : PMS_H2_START_MONTH;
                    $masterId = $this->findOrCreateEmployeeMaster((int)$empId, $year, $month, $now);

                    $goalDetailId = UUID();
                    PMSEmployeeGoalDetail::create([
                        'Id'                  => $goalDetailId,
                        'EmployeeGoalId'      => $masterId,
                        'Type'                => 2,
                        'GoalType'            => self::GOAL_TYPE,
                        'CommonGoalId'        => $commonGoal->Id,
                        'DisplayOrder'        => $tpl->DisplayOrder,
                        'Description'         => $tpl->Description,
                        'Weightage'           => $tpl->Weightage,
                        'Target'              => $tpl->Target,
                        'InH1'                => $tpl->InH1,
                        'InH2'                => $tpl->InH2,
                        'IsReadyForEmployee'  => 0, // supervisor must set weightages before employee can see this
                        'Year'                => $year,
                        'CreatedBy'           => Auth::id(),
                        'created_at'          => $now,
                    ]);

                    foreach ($tpl->targets as $tgt) {
                        PMSEmployeeGoalTarget::create([
                            'Id'           => UUID(),
                            'GoalDetailId' => $goalDetailId,
                            'Description'  => $tgt->Description,
                            'Weightage'    => $tgt->Weightage,
                            'Target'       => $tgt->Target,
                            'CreatedBy'    => Auth::id(),
                            'created_at'   => $now,
                        ]);
                    }
                }
            }

            $commonGoal->Status     = 'published';
            $commonGoal->EditedBy   = Auth::id();
            $commonGoal->updated_at = $now;
            $commonGoal->save();
        });

        $count = count($empIds);
        return redirect()->route('commongoal.show', $id)
            ->with('successmessage', "Common goals pushed to {$count} employee(s) successfully.");
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  IMPORT TEMPLATE PAGE
    // ──────────────────────────────────────────────────────────────────────────
    public function importTemplate(Request $request)
    {
        $selectedYear = (int) $request->input('year', date('Y'));
        return view('commongoals.import', compact('selectedYear'));
    }

    public function importProcess(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt',
            'year' => 'nullable|integer|min:2000|max:2100',
        ]);

        $selectedYear = (int) $request->input('year', date('Y'));

        $handle = fopen($request->file('file')->getPathname(), 'r');
        $rows   = [];
        while (($row = fgetcsv($handle)) !== false) {
            $rows[] = $row;
        }
        fclose($handle);

        if (empty($rows)) {
            return back()->withErrors(['file' => 'The uploaded file is empty.']);
        }

        array_shift($rows); // remove header row

        $goals     = [];
        $goalIndex = 0;
        $lastKey   = null;
        $skipKey   = null;

        foreach ($rows as $row) {
            $taskDesc = trim($row[5] ?? '');
            if ($taskDesc === '') continue;

            $goalDesc = trim($row[0] ?? '');

            if ($goalDesc !== '') {
                $goalIndex++;
                $lastKey = $goalIndex;
                $skipKey = null;

                $rowYear = (int) trim($row[2] ?? $selectedYear);
                if ($rowYear !== $selectedYear) {
                    $skipKey = $lastKey;
                    continue;
                }

                $h1 = strtolower(trim($row[3] ?? ''));
                $h2 = strtolower(trim($row[4] ?? ''));

                $entry = [
                    'goal_number' => $goalIndex,
                    'description' => $goalDesc,
                    'total_score' => (float) ($row[1] ?? 0),
                    'year'        => $rowYear,
                    'tasks'       => [],
                ];
                if (in_array($h1, ['y', 'yes', '1'])) $entry['in_h1'] = 1;
                if (in_array($h2, ['y', 'yes', '1'])) $entry['in_h2'] = 1;

                $goals[$lastKey] = $entry;
            }

            if ($lastKey === null || $skipKey === $lastKey || !isset($goals[$lastKey])) continue;

            $goals[$lastKey]['tasks'][] = [
                'description' => $taskDesc,
                'weightage'   => (float) ($row[6] ?? 0),
                'target'      => trim($row[7] ?? '') ?: '-',
            ];
        }

        if (empty($goals)) {
            return back()->withErrors(['file' => 'No valid goal rows found. Ensure the Year column matches ' . $selectedYear . '.']);
        }

        $now        = date('Y-m-d H:i:s');
        $goalsArray = array_values($goals);

        DB::transaction(function () use ($selectedYear, $goalsArray, $now) {
            $master = CommonGoal::create([
                'Year'       => $selectedYear,
                'Title'      => 'Common Goals ' . $selectedYear,
                'Status'     => 'draft',
                'CreatedBy'  => Auth::id(),
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $this->saveGoalDetails($master->Id, $goalsArray, $selectedYear, $now);
        });

        return redirect()->route('commongoal.index', ['year' => $selectedYear])
                         ->with('successmessage', 'Common goals imported successfully as draft. You can now assign employees and publish.');
    }

    // ══════════════════════════════════════════════════════════════════════════
    //  PRIVATE HELPERS
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Save goal template details into the supervisor's own main pms_employeegoal row.
     * No separate 'commongoal' row — one row per employee per PMS cycle, always.
     */
    private function saveGoalDetails(int $commonGoalId, array $goals, int $year, string $now): void
    {
        $supervisorId = Auth::id();

        foreach ($goals as $gIdx => $g) {
            $goalNumber  = $g['goal_number'] ?? ($gIdx + 1);
            $inH1        = isset($g['in_h1']) ? 1 : 0;
            $inH2        = isset($g['in_h2']) ? 1 : 0;
            $targetLabel = match(true) {
                $inH1 && $inH2 => 'H1 & H2',
                (bool) $inH1   => 'H1',
                (bool) $inH2   => 'H2',
                default        => 'Full Year',
            };

            $month    = ($inH1 || (!$inH1 && !$inH2)) ? PMS_H1_START_MONTH : PMS_H2_START_MONTH;
            $masterId = $this->findOrCreateEmployeeMaster($supervisorId, $year, $month, $now);

            $goalDetailId = UUID();
            PMSEmployeeGoalDetail::create([
                'Id'             => $goalDetailId,
                'EmployeeGoalId' => $masterId,
                'Type'           => 2,
                'GoalType'       => self::GOAL_TYPE,
                'CommonGoalId'   => $commonGoalId,
                'DisplayOrder'   => $goalNumber * 1000,
                'Description'    => trim($g['description']),
                'Weightage'      => 0,   // set by section supervisor per employee
                'Target'         => $targetLabel,
                'InH1'           => $inH1,
                'InH2'           => $inH2,
                'Year'           => $year,
                'CreatedBy'      => $supervisorId,
                'created_at'     => $now,
            ]);

            foreach ($g['tasks'] ?? [] as $task) {
                if (empty(trim($task['description'] ?? ''))) continue;
                $target = $task['target'] ?? '';
                if ($target === 'Custom') $target = trim($task['target_custom'] ?? '');
                PMSEmployeeGoalTarget::create([
                    'Id'           => UUID(),
                    'GoalDetailId' => $goalDetailId,
                    'Description'  => trim($task['description']),
                    'Weightage'    => 0,   // set by section supervisor per employee
                    'Target'       => $target ?: '-',
                    'CreatedBy'    => $supervisorId,
                    'created_at'   => $now,
                ]);
            }
        }
    }

    /**
     * Publish common goals into each assigned employee's main pms_employeegoal row.
     * Scores (Weightage) are intentionally 0 — assigned later by each section supervisor.
     */
    private function publishToEmployees(int $commonGoalId, array $empIds, array $goals, int $year, string $now): void
    {
        foreach ($empIds as $empId) {
            foreach ($goals as $gIdx => $g) {
                $goalNumber  = $g['goal_number'] ?? ($gIdx + 1);
                $inH1        = isset($g['in_h1']) ? 1 : 0;
                $inH2        = isset($g['in_h2']) ? 1 : 0;
                $targetLabel = match(true) {
                    $inH1 && $inH2 => 'H1 & H2',
                    (bool) $inH1   => 'H1',
                    (bool) $inH2   => 'H2',
                    default        => 'Full Year',
                };

                $month    = ($inH1 || (!$inH1 && !$inH2)) ? PMS_H1_START_MONTH : PMS_H2_START_MONTH;
                $masterId = $this->findOrCreateEmployeeMaster((int)$empId, $year, $month, $now);

                $goalDetailId = UUID();
                PMSEmployeeGoalDetail::create([
                    'Id'                  => $goalDetailId,
                    'EmployeeGoalId'      => $masterId,
                    'Type'                => 2,
                    'GoalType'            => self::GOAL_TYPE,
                    'CommonGoalId'        => $commonGoalId,
                    'DisplayOrder'        => $goalNumber * 1000,
                    'Description'         => trim($g['description']),
                    'Weightage'           => 0,
                    'Target'              => $targetLabel,
                    'InH1'                => $inH1,
                    'InH2'                => $inH2,
                    'IsReadyForEmployee'  => 0, // supervisor must set weightages before employee can see this
                    'Year'                => $year,
                    'CreatedBy'           => Auth::id(),
                    'created_at'          => $now,
                ]);

                foreach ($g['tasks'] ?? [] as $task) {
                    if (empty(trim($task['description'] ?? ''))) continue;
                    $target = $task['target'] ?? '';
                    if ($target === 'Custom') $target = trim($task['target_custom'] ?? '');
                    PMSEmployeeGoalTarget::create([
                        'Id'           => UUID(),
                        'GoalDetailId' => $goalDetailId,
                        'Description'  => trim($task['description']),
                        'Weightage'    => 0,
                        'Target'       => $target ?: '-',
                        'CreatedBy'    => Auth::id(),
                        'created_at'   => $now,
                    ]);
                }
            }
        }
    }

    /**
     * Find the employee's existing pms_employeegoal row for this PMS half-year,
     * or create one if none exists. Never creates a 'commongoal' template row —
     * one row per employee per PMS cycle, always.
     */
    private function findOrCreateEmployeeMaster(int $empId, int $year, int $month, string $now): string
    {
        $deptId = DB::table('mas_employee')->where('Id', $empId)->value('DepartmentId');

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

        $existing = DB::table('pms_employeegoal')
            ->where('EmployeeId', $empId)
            ->where('SysPmsNumberId', $pmsId)
            ->value('Id');

        if ($existing) return $existing;

        $level1Count = DB::table('mas_hierarchy')->where('EmployeeId', $empId)
            ->distinct('ReportingLevel1EmployeeId')->count('ReportingLevel1EmployeeId');
        $level2Count = DB::table('mas_hierarchy')->where('EmployeeId', $empId)
            ->distinct('ReportingLevel2EmployeeId')->count('ReportingLevel2EmployeeId');

        $masterId = UUID();
        DB::table('pms_employeegoal')->insert([
            'Id'                      => $masterId,
            'SysPmsNumberId'          => $pmsId,
            'EmployeeId'              => $empId,
            'DepartmentId'            => $deptId,
            'MultipleLevel1Appraiser' => $level1Count > 1 ? 1 : 0,
            'MultipleLevel2Appraiser' => $level2Count > 1 ? 1 : 0,
            'Status'                  => 0,
            'H1Status'                => 0,
            'H2Status'                => 0,
            'GoalSetBy'               => 'supervisor',
            'ApprovalStatus'          => 2,
            'CreatedBy'               => Auth::id(),
            'created_at'              => $now,
        ]);

        return $masterId;
    }

    /**
     * Delete ALL pms_employeegoaldetail rows (and their targets) for this common goal set.
     * Identified purely by CommonGoalId on the detail row — no pms_employeegoal.CommonGoalId needed.
     */
    private function deleteAllDetails(int $commonGoalId): void
    {
        $detailIds = DB::table('pms_employeegoaldetail')
            ->where('CommonGoalId', $commonGoalId)
            ->where('GoalType', self::GOAL_TYPE)
            ->pluck('Id');

        if ($detailIds->isNotEmpty()) {
            DB::table('pms_employeegoaltargetdetail')
                ->whereIn('GoalDetailId', $detailIds)->delete();
            DB::table('pms_employeegoaldetail')
                ->whereIn('Id', $detailIds)->delete();
        }
    }

    /**
     * Save employee assignment records.
     */
    private function saveAssignments(int $masterId, array $empIds, string $now): void
    {
        if (empty($empIds)) return;

        $empRecords = DB::table('mas_employee')
            ->whereIn('Id', $empIds)->where('Status', 1)
            ->get(['Id', 'DepartmentId', 'SectionId'])->keyBy('Id');

        $rows = [];
        foreach ($empIds as $eId) {
            if (!$empRecords->has($eId)) continue;
            $emp    = $empRecords[$eId];
            $rows[] = [
                'CommonGoalId' => $masterId,
                'EmployeeId'   => $eId,
                'DepartmentId' => $emp->DepartmentId,
                'SectionId'    => $emp->SectionId,
                'created_at'   => $now,
            ];
        }

        if (!empty($rows)) {
            DB::table('pms_common_goal_assignments')->insert($rows);
        }
    }

    /**
     * Returns true if any assigned employee has started their self-rating
     * (saved a draft score or formally submitted a cycle).
     * Once this is true the common goal set's structure (descriptions/tasks/targets) becomes immutable.
     * Weightages are set by each employee's L1 appraiser via the regular goal edit flow.
     */
    private function isLockedByActivity(int $commonGoalId, int $year = 0): bool
    {
        if (!$year) {
            $year = (int) DB::table('pms_common_goals')->where('Id', $commonGoalId)->value('Year');
        }

        $empIds = DB::table('pms_common_goal_assignments')
            ->where('CommonGoalId', $commonGoalId)
            ->pluck('EmployeeId');

        if ($empIds->isEmpty()) return false;

        // Only consider pms_employeegoal rows that belong to the common goal's own year.
        // Past PMS cycles from previous years must not lock the current goal set.
        $yearMasterIds = DB::table('pms_employeegoal as eg')
            ->join('sys_pmsnumber as pn', 'pn.Id', '=', 'eg.SysPmsNumberId')
            ->whereIn('eg.EmployeeId', $empIds)
            ->whereYear('pn.StartDate', $year)
            ->pluck('eg.Id');

        if ($yearMasterIds->isEmpty()) return false;

        // Case 1: any assigned employee has formally submitted a cycle in the goal year
        $hasSubmitted = DB::table('pms_employeegoal')
            ->whereIn('Id', $yearMasterIds)
            ->where(function ($q) { $q->where('H1Status', 1)->orWhere('H2Status', 1); })
            ->exists();

        if ($hasSubmitted) return true;

        // Case 2: any assigned employee has saved a self-rating draft this year
        return PMSEmployeeGoalDetail::whereIn('EmployeeGoalId', $yearMasterIds)
            ->where('Year', $year)
            ->whereNotNull('SelfScore')
            ->exists();
    }

}
