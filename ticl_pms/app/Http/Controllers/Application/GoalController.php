<?php

namespace App\Http\Controllers\Application;

use Illuminate\Http\Request;

use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Helper\Sample;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB; //DB (query builder)
use Auth;
use App\PMSEmployeeGoal;
use App\PMSEmployeeGoalDetail;
use App\PMSEmployeeGoalHistory;
use Maatwebsite\Excel\Facades\Excel;

require_once '../phpspreadsheet/vendor/autoload.php';

class GoalController extends Controller
{
    public function getList()
    {
        $today = strtotime(date('Y-m-d'));
        $withinFirstPMSOfYear = false;
        $withinSecondPMSOfYear = false;
        $notWithinPMSPeriod = false;
	$currentRound = "";
        if ($today >= strtotime(date('Y-07-01')) && $today <= strtotime(date('Y-07-31'))) {
            $currentRound = date('Y') . " H1";
            $withinSecondPMSOfYear = true;
        } else {
            if ($today >= strtotime(date('Y-01-01')) && $today <= strtotime(date('Y-01-31'))) {
                $currentRound = ((int) date('Y') - 1) . " H2";
                $withinFirstPMSOfYear = true;
            }
        }

        if (!$withinSecondPMSOfYear && !$withinFirstPMSOfYear) {
            $notWithinPMSPeriod = true;
            if ($today > strtotime(date('Y-07-31')) && $today <= strtotime(date('Y-01-31'))) {
                $currentRound = date('Y') . " H2";
            } else {
                $currentRound = date('Y') . " H1";
            }
	}

        $isAppraiser = 0;
        $userPositionId = Auth::user()->PositionId;
        if (!in_array($userPositionId, [CONST_POSITION_HOD, CONST_POSITION_HOS, CONST_POSITION_MD])) {
            if (!in_array($userPositionId, [CONST_POSITION_HOD, CONST_POSITION_HOS, CONST_POSITION_MD])) {
                $isAppraiser = DB::table('mas_hierarchy as T1')->join('mas_employee as T2', 'T2.Id', '=', 'T1.EmployeeId')->whereRaw("(T1.ReportingLevel1EmployeeId = ? or T1.ReportingLevel2EmployeeId = ?) and coalesce(T2.Status,0) = 1", [Auth::user()->Id, Auth::user()->Id])->count();
                if ($isAppraiser > 0) {
                    $userPositionId = CONST_POSITION_HOS;
                }
            }
        }

        if ($notWithinPMSPeriod) {
            $statusQuery = DB::table('sys_pmsnumber')->where('StartDate', ">", date('Y-m-d'))->orderBy('StartDate', 'ASC')->take(1)->get(['Status', 'PMSNumber', 'StartDate']);
            $currentPMSNumber = $statusQuery[0]->PMSNumber;
            $currentPMSStartDate = $statusQuery[0]->StartDate;
            $currentPMSEndDate = date_format(date_create($currentPMSStartDate), "t M, Y");
        } else {
            $statusQuery = DB::table('sys_pmsnumber')->where('StartDate', "<=", date('Y-m-d'))->orderBy('StartDate', 'DESC')->take(1)->get(['Status', 'PMSNumber', 'StartDate']);
            $currentPMSNumber = $statusQuery[0]->PMSNumber;
            $currentPMSStartDate = $statusQuery[0]->StartDate;
            $currentPMSEndDate = date_format(date_create($currentPMSStartDate), "t M, Y");
        }

        $currentPmsPeriod = "PMS $currentPMSNumber (" . date_format(date_create($currentPMSStartDate), "j M, Y") . " to $currentPMSEndDate)";
        $units = [];
        $employees = [];

        if ($userPositionId == CONST_POSITION_HOS) {
            $type = 1;
            $employees = DB::select("SELECT T1.Id, concat(T1.Name,', ',V.ShortName,' Department') as Name, T2.PMSOutcomeId, T2.Id as PMSSubmissionId, P.Id as GoalDefinitionId,P.Status as GoalDefinitionStatus, O.Name as Designation, Z.Name as Position, T2.LastStatusId, T3.Name as Status, T2.Id as SubmissionId from (mas_employee T1 join mas_department V on V.Id = T1.DepartmentId join mas_designation O on O.Id = T1.DesignationId left join mas_hierarchy B on B.EmployeeId = T1.Id) left join pms_employeegoal P on P.EmployeeId = T1.Id and P.SysPmsNumberId = ? join mas_gradestep Z on Z.Id = T1.GradeStepId left join mas_position A on A.Id = T1.PositionId left join (viewpmssubmissionwithlaststatus T2 join mas_pmsstatus T3 on T3.Id = T2.LastStatusId) on T2.EmployeeId = T1.Id and T2.SubmissionTIme >= ? where coalesce(T1.Status,0) = 1 and (B.ReportingLevel1EmployeeId = ? or B.Reportinglevel2EmployeeId = ?) order by A.DisplayOrder,T1.Name", [$currentPMSNumber, $currentPMSStartDate, Auth::user()->Id, Auth::user()->Id]);
        } else if ($userPositionId == CONST_POSITION_HOD) {
            $type = 2;
            $units = DB::select("SELECT distinct T1.Id, concat(T2.ShortName, ' | ', T1.Name) as Name from mas_section T1 join mas_department T2 on T2.Id = T1.DepartmentId join (mas_employee A join mas_hierarchy B on B.EmployeeId = A.Id) on A.SectionId = T1.Id where B.ReportingLevel1EmployeeId = ? and coalesce(T1.Status,0) = 1 and coalesce(A.Status,0) = 1 order by T1.Name", [Auth::user()->Id]);
            foreach ($units as $section):
                $employees[$section->Id] = DB::select("SELECT T1.Id,'' as Section, P.Id as GoalDefinitionId,P.Status as GoalDefinitionStatus, T1.Name, T2.PMSOutcomeId, T2.Id as PMSSubmissionId, O.Name as Designation, Z.Name as Position, T2.LastStatusId, T3.Name as Status, T2.Id as SubmissionId from (mas_employee T1 join mas_designation O on O.Id = T1.DesignationId left join mas_hierarchy B on B.EmployeeId = T1.Id) left join pms_employeegoal P on P.EmployeeId = T1.Id and P.SysPmsNumberId = ? join mas_gradestep Z on Z.Id = T1.GradeStepId left join mas_position A on A.Id = T1.PositionId left join (viewpmssubmissionwithlaststatus T2 join mas_pmsstatus T3 on T3.Id = T2.LastStatusId) on T2.EmployeeId = T1.Id and T2.SubmissionTime >= ? where coalesce(T1.Status,0) = 1 and (B.ReportingLevel1EmployeeId = ?) and T1.SectionId = ? order by A.DisplayOrder,T1.Name", [$currentPMSNumber, $currentPMSStartDate, Auth::user()->Id, $section->Id]);
            endforeach;
        } else {
            abort(404);
        }

        return view('goals.employeelist')->with('currentRound', $currentRound)->with('withinFirstPMSOfYear', $withinFirstPMSOfYear)->with('withinSecondPMSOfYear', $withinSecondPMSOfYear)->with('currentPmsPeriod', $currentPmsPeriod)->with('notWithinPMSPeriod', $notWithinPMSPeriod)->with('type', $type)->with('units', $units)->with('employees', $employees);
    }

    public function getIndex($id, $round = false)
    {
        /* PROFILE */
        $data['EmployeeId'] = $id;
        $data['details'] = DB::select("SELECT T1.EmpId,T1.Status,T3.GradeId,T1.PositionId,T1.CIDNo,case when coalesce(T1.NoProbation,0) = 0 and T4.PayScale is not null
            then DATE_ADD(T1.DateOfAppointment, INTERVAL 6 MONTH) else T1.DateOfAppointment end as DateOfRegularization, (select GROUP_CONCAT(concat(P.Name,' (',Q.Name,')') SEPARATOR '<br/>') from mas_hierarchy O join mas_employee P on P.Id = O.ReportingLevel1EmployeeId join mas_designation Q on Q.Id = P.DesignationId where O.EmployeeId = T1.Id) as Level1Name,
            (select GROUP_CONCAT(concat(P.Name,' (',Q.Name,')') SEPARATOR '<br/>') from mas_hierarchy O join mas_employee P on P.Id = O.ReportingLevel2EmployeeId join mas_designation Q on Q.Id = P.DesignationId where O.EmployeeId = T1.Id) as Level2Name, T4.Name as GradeStep, T4.PayScale, 
            T1.Extension, T1.MobileNo, T1.DateOfBirth, T1.DateOfAppointment,T1.ProfilePicPath,T1.Extension,T1.Name,B.Name as DesignationLocation, 
            T2.Name as Department,A.Name as Section, concat(Z1.Name,case when Z2.Id is null then '' else concat(' - Reporting to ',Z2.Name) end) as 
            Position from mas_employee T1 left join mas_designation B on B.Id = T1.DesignationId join mas_department T2 on T2.Id = T1.DepartmentId left 
            join mas_section A on A.Id = T1.SectionId left join (mas_position T3 join mas_grade Z1 on Z1.Id = T3.GradeId left join mas_supervisor Z2 
            on Z2.Id = T3.SupervisorId) on T3.Id = T1.PositionId left join mas_gradestep T4 on T4.Id = T1.GradeStepId /*left join (mas_hierarchy W1 
            join mas_employee W2 on W2.Id = W1.ReportingLevel1EmployeeId left join mas_designation V1 on V1.Id = W2.DesignationId left join 
            mas_employee W3 left join mas_designation V2 on V2.Id = W3.DesignationId on W3.Id = W1.ReportingLevel2EmployeeId) on W1.EmployeeId = T1.Id
            */where T1.Id = ?", [$id]);
        if (count($data['details']) == 0) {
            abort(404);
        }
        /* END PROFILE */
        $today = strtotime(date('Y-m-d'));
        $withinFirstPMSOfYear = false;
        $withinSecondPMSOfYear = false;
        $notWithinPMSPeriod = false;

        if ($today >= strtotime(date('Y-07-01')) && $today <= strtotime(date('Y-07-31'))) {
            $withinSecondPMSOfYear = true;
        } else {
            if ($today >= strtotime(date('Y-01-01')) && $today <= strtotime(date('Y-01-31'))) {
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

        $data['goalId'] = DB::table('pms_employeegoal')
            ->where("SysPmsNumberId", $data['nextPMSId'])
            ->where('EmployeeId', $id)
            ->value("Id");
        $data['goalDetails'] = [new PMSEmployeeGoalDetail()];
        $data['onmDetails'] = [new PMSEmployeeGoalDetail()];
        $data['goalSubmissionHistory'] = [new PMSEmployeeGoalHistory()];
        if ((bool) $data['goalId']) {
            $data['isDefined'] = true;
            $data['onmDetails'] = DB::table("pms_employeegoaldetail")
                ->where("EmployeeGoalId", $data['goalId'])
                ->where('Type', 1)
                ->orderBy('DisplayOrder')
                ->get(['Id', 'Description', 'DisplayOrder', 'Weightage', 'Target', 'Achievement', 'SelfScore']);
            if (empty($data['onmDetails'])) {
                $data['onmDetails'] = [new PMSEmployeeGoalDetail()];
            }
            $data['goalDetails'] = DB::table("pms_employeegoaldetail")
                ->where("EmployeeGoalId", $data['goalId'])
                ->where('Type', 2)
                ->orderBy('DisplayOrder')
                ->get(['Id', 'Description', 'DisplayOrder', 'Weightage', 'Target', 'Achievement', 'SelfScore']);
            if (empty($data['goalDetails'])) {
                $data['goalDetails'] = [new PMSEmployeeGoalDetail()];
            }
        }

        return view('goals.index', $data);
    }

    public function postSave(Request $request)
    {
        $id = $request->Id;
        $inputs['EmployeeId'] = $request->EmployeeId;
        $inputs['DepartmentId'] = $request->DepartmentId;
        $inputs['SysPmsNumberId'] = $request->SysPmsNumberId;
        $inputs['Status'] = $request->Status;
        $employee = DB::table('mas_employee')
            ->where('Id', $request->EmployeeId)
            ->selectRaw("concat(Name, ' (',EmpId,')') as Employee")
            ->value("Employee");
        $save = true;
        if ((bool) $id) {
            $save = false;
            $inputs['EditedBy'] = Auth::id();
            $inputs['updated_at'] = date("Y-m-d H:i:s");
            $updateObject = PMSEmployeeGoal::find($id);
            $updateObject->fill($inputs);
            $updateObject->update();
        } else {
            $inputs['Id'] = $id = UUID();
            $inputs['CreatedBy'] = Auth::id();
            PMSEmployeeGoal::create($inputs);
        }

        $detailIds = [];
        $onmInputs = $request->goaldetailonm;
        foreach ($onmInputs as $key => $onmInput):
            if ((bool) $onmInput['Description'] && (bool) $onmInput['Weightage'] && (bool) $onmInput['Target']) {
                $onmInput['EmployeeGoalId'] = $id;
                $onmInput['Type'] = 1;
                if ((bool) $onmInput['Id']) {
                    $onmInput['EditedBy'] = Auth::id();
                    $onmInput['updated_at'] = date('Y-m-d H:i:s');
                    $updateObject = PMSEmployeeGoalDetail::find($onmInput["Id"]);
                    $updateObject->fill($onmInput);
                    $updateObject->update();
                } else {
                    $onmInput['Id'] = UUID();
                    $onmInput['CreatedBy'] = Auth::id();
                    PMSEmployeeGoalDetail::create($onmInput);
                }
                array_push($detailIds, $onmInput['Id']);
            }
        endforeach;

        $goalInputs = $request->goaldetailpna;
        foreach ($goalInputs as $key => $goalInput):
            if ((bool) $goalInput['Description'] && (bool) $goalInput['Weightage'] && (bool) $goalInput['Target']) {
                $goalInput['EmployeeGoalId'] = $id;
                $goalInput['Type'] = 2;
                if ((bool) $goalInput['Id']) {
                    $goalInput['EditedBy'] = Auth::id();
                    $goalInput['updated_at'] = date('Y-m-d H:i:s');
                    $updateObject = PMSEmployeeGoalDetail::find($goalInput["Id"]);
                    $updateObject->fill($goalInput);
                    $updateObject->update();
                } else {
                    $goalInput['Id'] = UUID();
                    $goalInput['CreatedBy'] = Auth::id();
                    PMSEmployeeGoalDetail::create($goalInput);
                }
                array_push($detailIds, $goalInput['Id']);
            }
        endforeach;

        DB::table("pms_employeegoaldetail")->where('EmployeeGoalId', $id)->whereNotIn("Id", $detailIds)->delete();
        return redirect('pmsgoal')->with('successmessage', "Goal for $employee has been " . ($save ? "saved" : "updated"));
    }

    public function getMyGoals(Request $request)
    {
        $data['inaccessible'] = true;
        $currentPMSStatus = DB::table('sys_pmsnumber')
            ->where('StartDate', '<', date('Y-m-d'))
            ->orderBy("StartDate", "DESC")
            ->take(1)
            ->value('Status');
        if ($currentPMSStatus == 1 || $currentPMSStatus == null) {
            $pmsDetails = DB::table('sys_pmsnumber')
                ->where('StartDate', '<', date('Y-m-d'))
                ->orderBy("StartDate", "DESC")
                ->take(1)
                ->get(['Id', 'StartDate']);
            $nextPMSId = $pmsDetails[0]->Id;
            $currentPMSStartDate = $pmsDetails[0]->StartDate;
            $pmsSubmissionQuery = DB::table("viewpmssubmissionwithlaststatus")
                ->where("EmployeeId", Auth::id())
                ->whereRaw("DATE_FORMAT(SubmissionTime,'%Y-%m-%d') >= ?", [$currentPMSStartDate])
                ->get(["LastStatusId", "StatusByEmployeeId"]);
            $status = count($pmsSubmissionQuery) > 0 ? $pmsSubmissionQuery[0]->LastStatusId : false;
            $statusByEmployeeId = count($pmsSubmissionQuery) > 0 ? $pmsSubmissionQuery[0]->StatusByEmployeeId : false;

            if (($status == CONST_PMSSTATUS_SENTBACKBYVERIFIER) || ($status == CONST_PMSSTATUS_DRAFT && $statusByEmployeeId == Auth::id())) {
                $data['inaccessible'] = false;
            }
            if (count($pmsSubmissionQuery) == 0) {
                $data['inaccessible'] = false;
            }
        } else {
            $data['inaccessible'] = false;
            $nextPMSId = DB::table("sys_pmsnumber")
                ->where('StartDate', '>', date('Y-m-d'))
                ->orderBy('StartDate')
                ->take(1)
                ->value("Id");
        }

        $data['goalId'] = DB::table('pms_employeegoal')
            ->where('SysPmsNumberId', $nextPMSId)
            ->where('EmployeeId', Auth::id())
            ->value('Id');
        $data['onmTargets'] = DB::table('pms_employeegoal as T1')
            ->join('pms_employeegoaldetail as T2', 'T2.EmployeeGoalId', '=', 'T1.Id')
            ->where('T1.Id', $data['goalId'])
            ->where('T2.Type', 1)
            ->orderBy('T2.DisplayOrder')
            ->get(['T2.Id', 'T2.Description', 'T2.DisplayOrder', 'T2.Weightage', 'T2.Target', 'T2.SelfScore', 'T2.SelfRemarks']);
        $data['goalTargets'] = DB::table('pms_employeegoal as T1')
            ->join('pms_employeegoaldetail as T2', 'T2.EmployeeGoalId', '=', 'T1.Id')
            ->where('T1.Id', $data['goalId'])
            ->where('T2.Type', 2)
            ->orderBy('T2.DisplayOrder')
            ->get(['T2.Id', 'T2.Description', 'T2.DisplayOrder', 'T2.Weightage', 'T2.Target', 'T2.SelfScore', 'T2.SelfRemarks']);
        return view('goals.selfgoals', $data);
    }

    public function postSaveScore(Request $request)
    {
        $inputs = $request->input();
        $onmscores = isset($inputs['goaldetailonm']) ? $inputs['goaldetailonm'] : [];
        $pnascores = isset($inputs['goaldetailpna']) ? $inputs['goaldetailpna'] : [];
        foreach ($onmscores as $key => $onmscore):
            $id = $onmscore['Id'];
            $updateObject = PMSEmployeeGoalDetail::find($id);
            $updateObject->fill($onmscore);
            $updateObject->update();
        endforeach;
        foreach ($pnascores as $key => $pnascore):
            $id = $pnascore['Id'];
            $updateObject = PMSEmployeeGoalDetail::find($id);
            $updateObject->fill($pnascore);
            $updateObject->update();
        endforeach;
        $redirect = "mypmsgoal";
        $message = "Your goal achievements have been recorded";
        if (isset($inputs['Redirect'])) {
            $redirect = $inputs['Redirect'];
            $message = "Your scoring of employee's goal achievements have been saved";
        }

        return redirect($redirect)->with('successmessage', $message);
    }

    public function fetchSubordinateGoals(Request $request)
    {
        $id = $request->id;
        $data['id'] = $id;
        $employeeQuery = DB::table("pms_submission as T1")
            ->join('mas_employee as T2', 'T2.Id', '=', 'T1.EmployeeId')
            ->where('T1.Id', $id)
            ->get(["T1.EmployeeId", 'T2.Name', 'T2.EmpId']);

        if (count($employeeQuery) === 0) {
            return abort("401");
        }

        $employeeId = $employeeQuery[0]->EmployeeId;
        $data['Employee'] = $employeeQuery[0]->Name . " (" . $employeeQuery[0]->EmpId . ")";
        $currentPMSStatus = DB::table('sys_pmsnumber')
            ->where('StartDate', '<', date('Y-m-d'))
            ->orderBy("StartDate", "DESC")
            ->take(1)
            ->value('Status');
        if ($currentPMSStatus == 1 || $currentPMSStatus == null) {
            $pmsDetails = DB::table('sys_pmsnumber')
                ->where('StartDate', '<', date('Y-m-d'))
                ->orderBy("StartDate", "DESC")
                ->take(1)
                ->get(['Id', 'StartDate']);
            $nextPMSId = $pmsDetails[0]->Id;
        } else {
            $nextPMSId = DB::table("sys_pmsnumber")
                ->where('StartDate', '>', date('Y-m-d'))
                ->orderBy('StartDate')
                ->take(1)
                ->value("Id");
        }
        $data['goalId'] = DB::table('pms_employeegoal')
            ->where('SysPmsNumberId', $nextPMSId)
            ->where('EmployeeId', $employeeId)
            ->value('Id');
        $data['onmTargets'] = DB::table('pms_employeegoal as T1')
            ->join('pms_employeegoaldetail as T2', 'T2.EmployeeGoalId', '=', 'T1.Id')
            ->where('T1.Id', $data['goalId'])
            ->where('T2.Type', 1)
            ->orderBy('T2.DisplayOrder')
            ->get(['T2.Id', 'T2.Description', 'T2.DisplayOrder', 'T2.Weightage', 'T2.Target', 'T2.SelfScore', 'T2.SelfRemarks', 'T2.Level1Score', 'T2.Level1Remarks', 'T2.Level2Remarks']);
        $data['goalTargets'] = DB::table('pms_employeegoal as T1')
            ->join('pms_employeegoaldetail as T2', 'T2.EmployeeGoalId', '=', 'T1.Id')
            ->where('T1.Id', $data['goalId'])
            ->where('T2.Type', 2)
            ->orderBy('T2.DisplayOrder')
            ->get(['T2.Id', 'T2.Description', 'T2.DisplayOrder', 'T2.Weightage', 'T2.Target', 'T2.SelfScore', 'T2.SelfRemarks', 'T2.Level1Score', 'T2.Level1Remarks', 'T2.Level2Remarks']);
        return view('goals.subordinategoals', $data);
    }

    public function fetchSubordinateGoalsL2(Request $request)
    {
        $id = $request->id;
        $data['id'] = $id;
        $employeeQuery = DB::table("pms_submission as T1")
            ->join('mas_employee as T2', 'T2.Id', '=', 'T1.EmployeeId')
            ->where('T1.Id', $id)
            ->get(["T1.EmployeeId", 'T2.Name', 'T2.EmpId']);
        if (count($employeeQuery) === 0) {
            return abort("401");
        }
        $employeeId = $employeeQuery[0]->EmployeeId;
        $data['Employee'] = $employeeQuery[0]->Name . " (" . $employeeQuery[0]->EmpId . ")";
        $currentPMSStatus = DB::table('sys_pmsnumber')
            ->where('StartDate', '<', date('Y-m-d'))
            ->orderBy("StartDate", "DESC")
            ->take(1)
            ->value('Status');
        if ($currentPMSStatus == 1 || $currentPMSStatus == null) {
            $pmsDetails = DB::table('sys_pmsnumber')
                ->where('StartDate', '<', date('Y-m-d'))
                ->orderBy("StartDate", "DESC")
                ->take(1)
                ->get(['Id', 'StartDate']);
            $nextPMSId = $pmsDetails[0]->Id;
        } else {
            $nextPMSId = DB::table("sys_pmsnumber")
                ->where('StartDate', '>', date('Y-m-d'))
                ->orderBy('StartDate')
                ->take(1)
                ->value("Id");
        }
        $data['goalId'] = DB::table('pms_employeegoal')
            ->where('SysPmsNumberId', $nextPMSId)
            ->where('EmployeeId', $employeeId)
            ->value('Id');
        $data['onmTargets'] = DB::table('pms_employeegoal as T1')
            ->join('pms_employeegoaldetail as T2', 'T2.EmployeeGoalId', '=', 'T1.Id')
            ->where('T1.Id', $data['goalId'])
            ->where('T2.Type', 1)
            ->orderBy('T2.DisplayOrder')
            ->get(['T2.Id', 'T2.Description', 'T2.DisplayOrder', 'T2.Weightage', 'T2.Target', 'T2.SelfScore', 'T2.SelfRemarks', 'T2.Level1Score', 'T2.Level1Remarks', 'T2.Level2Remarks']);
        $data['goalTargets'] = DB::table('pms_employeegoal as T1')
            ->join('pms_employeegoaldetail as T2', 'T2.EmployeeGoalId', '=', 'T1.Id')
            ->where('T1.Id', $data['goalId'])
            ->where('T2.Type', 2)
            ->orderBy('T2.DisplayOrder')
            ->get(['T2.Id', 'T2.Description', 'T2.DisplayOrder', 'T2.Weightage', 'T2.Target', 'T2.SelfScore', 'T2.SelfRemarks', 'T2.Level1Score', 'T2.Level1Remarks', 'T2.Level2Remarks']);
        return view('goals.subordinategoalsl2', $data);
    }

    public function uploadKPIFile(Request $request)
    {
        $data['type'] = $request->input('type');
        $file = $request->file('file');
        $spreadsheet = IOFactory::load($file);
        $data['sheetData'] = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);
        unset($data['sheetData'][0]);
        $data['sheetRowCount'] = count($data['sheetData']);
        $html = view('goals.loadgoalsfromexcel', $data);
        return $html;
    }

}
