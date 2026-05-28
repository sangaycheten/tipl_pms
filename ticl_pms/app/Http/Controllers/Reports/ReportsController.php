<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2019-01-01
 * Time: 12:58 PM
 */

namespace App\Http\Controllers\Reports;

use App\PositionDepartment;
use App\PositionDepartmentRating;
use App\PositionDepartmentRatingCriteria;
use Illuminate\Http\Request;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesResources;
use Illuminate\Support\Facades\DB; //DB (query builder)
use Illuminate\Support\Facades\Input;
use Maatwebsite\Excel\Facades\Excel;
use Auth;
use App\Position;

use App\Http\Controllers\Controller;

class ReportsController extends Controller
{
    public function getPMSComparisionEmployees()
    {
        $pmsPeriodArray = [];
        $pmsResultData = [];
        $parameters = [];
        $employees = [];
        $hasParams = false;
        $pmsPeriods = $this->pmsPeriodsForReports();
        $appointmentDates = DB::select("SELECT count(distinct T2.EmpId) as Number,T1.DateOfAppointment from mas_employee T1 join pms_historical T2 on T2.EmpId = T1.EmpId where T1.DateOfAppointment is not null group by T1.DateOfAppointment");
        $designations = DB::table('mas_designation')->orderBy("Name")->get(array('Id', 'Name'));
        $gradeSteps = DB::table('mas_gradestep')->orderBy("Name")->get(['Id', 'Name']);
        $employeeList = $this->getAllEmployees();

        $appointmentDate = Input::get('AppointmentDate');
        $designation = Input::get('Designation');
        $gradeStep = Input::get('GradeStep');
        $pmsPeriod = Input::get('PMSPeriod');
        $employeeId = Input::get('EmployeeId');

        $condition = " 1=1";
        if ((bool) $appointmentDate) {
            $condition .= " and T2.DateOfAppointment = ?";
            $parameters[] = $appointmentDate;
            $hasParams = true;
        }
        if ((bool) $designation) {
            $condition .= " and T2.DesignationId = ?";
            $parameters[] = $designation;
            $hasParams = true;
        }
        if ((bool) $gradeStep) {
            $condition .= " and T2.GradeStepId = ?";
            $parameters[] = $gradeStep;
            $hasParams = true;
        }
        if (!empty($employeeId)) {
            $hasParams = true;
            $conditionAppend = "";
            $empConditionCount = 0;
            foreach ($employeeId as $singleEmployeeId):
                if ((bool) $singleEmployeeId) {
                    $empConditionCount += 1;
                    if ($conditionAppend != "") {
                        $conditionAppend .= ",";
                    }
                    $conditionAppend .= "?";
                    $parameters[] = $singleEmployeeId;
                }
            endforeach;
            if ($empConditionCount > 0) {
                $conditionAppend = " and T2.Id in ($conditionAppend)";
                $condition .= $conditionAppend;
            }
        }

        if ((bool) $pmsPeriod && !empty($pmsPeriod) && !(count($pmsPeriod) == 1 && $pmsPeriod[0] == '')) {
            $pmsPeriodArray = $pmsPeriod;
        } else {
            foreach ($pmsPeriods as $pmsPeriod):
                $pmsPeriodArray[] = $pmsPeriod->Id;
            endforeach;
        }

        $pmsPeriodString = (implode(",", $pmsPeriodArray));
        if ($hasParams && (bool) $pmsPeriodString) {
            $employees = DB::select("SELECT distinct T2.Id, T2.Name, T2.EmpId, T2.BasicPay as 'Basic Pay', T4.ShortName as Dept, T2.CIDNo, T2.DateOfAppointment as DoA, T2.DateOfAppointment as DateOfAppointmentRaw, case when coalesce(T2.NoProbation,0) = 0 and A.PayScale is not null then DATE_ADD(T2.DateOfAppointment, INTERVAL 6 MONTH) else T2.DateOfAppointment end as DateOfRegularization, T3.Name as Designation, T2.JobLocation as 'Work Location', A.Name as Grade from pms_historical T1 join mas_employee T2 on TRIM(T2.EmpId) = TRIM(T1.EmpId) join mas_gradestep A on A.Id = T2.GradeStepId join mas_designation T3 on T3.Id = T2.DesignationId join mas_department T4 on T4.Id = T2.DepartmentId left join mas_section T5 on T5.Id = T2.SectionId where T1.PMSNumberId in (" . $pmsPeriodString . ") and$condition", $parameters);
            foreach ($employees as $employee):
                foreach ($pmsPeriodArray as $pmsPeriodId):
                    $pmsResultData[$employee->Id][$pmsPeriodId] = DB::select("SELECT T1.PMSResult,T1.PMSScore,T1.PMSRemarks from pms_historical T1 where T1.PMSNumberId = ? and T1.EmpId = ?", [$pmsPeriodId, $employee->EmpId]);
                endforeach;
            endforeach;
        }

        return view('reports.pmscomparisionemployees', ['employeeList' => $employeeList, 'employees' => $employees, 'pmsPeriodArray' => $pmsPeriodArray, 'pmsResultData' => $pmsResultData, 'appointmentDates' => $appointmentDates, 'designations' => $designations, 'gradeSteps' => $gradeSteps, 'pmsPeriods' => $pmsPeriods]);
    }

    public function getPMSScoreReport()
    {
        $parameters = [];
        $condition = " 1=1";
        $departments = $this->fetchActiveDepartments();
        $sections = $this->fetchSections();
        $pmsPeriods = DB::select("SELECT T1.Id, T1.PMSNumber, T1.StartDate from sys_pmsnumber T1 where T1.PMSNumber > 0 and T1.Status = 2 union 
            select T1.Id, T1.PMSNumber, T1.StartDate from sys_pmsnumber T1 where T1.StartDate < CURDATE() and T1.Status <> 2 order by PMSNumber");
        $pmsYears = DB::select("SELECT distinct YEAR(T1.StartDate) as Year from sys_pmsnumber T1 where T1.PMSNumber > 0 and T1.Status = 2 union all 
            select distinct YEAR(T1.StartDate) as Year from sys_pmsnumber T1 where T1.StartDate < CURDATE() and T1.Status <> 2 order by Year");
        
        $outcomes = DB::select("SELECT * from mas_pmsoutcome order by Id");
        $pmsPeriodArray = [];
        $currentPMSQuery = DB::table('sys_pmsnumber')->where('StartDate', '<=', date('Y-m-d'))->orderBy('StartDate', 'DESC')->pluck('StartDate');
        $pmsStartDate = $currentPMSQuery[0];
        $departmentId = Input::get('DepartmentId');
        $employeeIds = Input::get('EmployeeId');
        $sectionId = Input::get('SectionId');
        $pmsPeriod = Input::get('PMSPeriod');
        $fromYear = Input::get("FromYear");
        $toYear = Input::get("ToYear");
        $parameters[] = $pmsStartDate;

        if ($fromYear) {
            if (!$toYear) {
                $toYear = $pmsYears[count($pmsYears) - 1]->Year;
            }
            // Status = 2 and
            $pmsPeriodsFromYears = DB::select("SELECT Id from sys_pmsnumber where YEAR(StartDate) >= ? and YEAR(StartDate) <= ?", [$fromYear, $toYear]);
            foreach ($pmsPeriodsFromYears as $pmsPeriod):
                $pmsPeriodArray[] = $pmsPeriod->Id;
            endforeach;
        } else {
            if ((bool) $pmsPeriod && !empty($pmsPeriod) && !(count($pmsPeriod) == 1 && $pmsPeriod[0] == '')) {
                $pmsPeriodArray = $pmsPeriod;
            } else {
                foreach ($pmsPeriods as $pmsPeriod):
                    $pmsPeriodArray[] = $pmsPeriod->Id;
                endforeach;
            }
        }

        if ((bool) $sectionId) {
            $employees = $this->getSectionEmployees($sectionId, false, false);
        } else {
            if ((bool) $departmentId) {
                $employees = $this->getDepartmentEmployees($departmentId, false, false);
            }
        }

        //FETCH REPORT DATA
        if ((bool) $departmentId) {
            $queryAppend = '';
            foreach ($pmsPeriodArray as $pmsPeriodId):
                if ($queryAppend != '') {
                    $queryAppend .= ",";
                }

                $queryAppend .= " coalesce((select coalesce(A.PMSResult,'') from pms_historical A where A.EmpId = T2.EmpId and A.PMSNumberId = ?),'') as '$pmsPeriodId Result'";
                $queryAppend .= ", coalesce((select coalesce(A.PMSScore,'') from pms_historical A where A.EmpId = T2.EmpId and A.PMSNumberId = ?),'') as '$pmsPeriodId Score'";
                $queryAppend .= ", coalesce((select coalesce(A.PMSRemarks,'') from pms_historical A where A.EmpId = T2.EmpId and A.PMSNumberId = ?),'') as '$pmsPeriodId Remarks'";
                $queryAppend .= ", (select A.Id from pms_historical A where A.EmpId = T2.EmpId and A.PMSNumberId = ?) as '$pmsPeriodId Id'";
                $queryAppend .= ", (select A.PMSSubmissionId from pms_historical A where A.EmpId = T2.EmpId and A.PMSNumberId = ?) as '$pmsPeriodId SubmissionId'";
                
                $parameters[] = $pmsPeriodId;
                $parameters[] = $pmsPeriodId;
                $parameters[] = $pmsPeriodId;
                $parameters[] = $pmsPeriodId;
                $parameters[] = $pmsPeriodId;
            endforeach;

            if ((bool) $employeeIds) {
                $employeeCondition = "";
                foreach ($employeeIds as $employeeId):
                    if ($employeeCondition == '') {
                        $employeeCondition .= " and T2.Id in (";
                    } else {
                        $employeeCondition .= ",";
                    }
                    $employeeCondition .= "?";
                    $parameters[] = $employeeId;
                endforeach;
                if ($employeeCondition != "") {
                    $employeeCondition .= ")";
                    $condition .= $employeeCondition;
                }
            } else {
                if ((bool) $sectionId) {
                    $employees = $this->getSectionEmployees($sectionId, false, false);
                    $condition .= " and T2.SectionId = ?";
                    $parameters[] = $sectionId;
                } else {
                    if ((bool) $departmentId) {
                        $employees = $this->getDepartmentEmployees($departmentId, false, false);
                        $condition .= " and T2.DepartmentId = ?";
                        $parameters[] = $departmentId;
                    }
                }
            }

            $query = "SELECT distinct T2.Id, T2.Name as Employee,T2.DateOfAppointment as DateOfAppointmentRaw, case when coalesce(T2.NoProbation,0) = 0 and C.PayScale is not null then DATE_ADD(T2.DateOfAppointment, INTERVAL 6 MONTH) else T2.DateOfAppointment end as DateOfRegularization,  C.Name as GradeStep, T2.EmpId, (select pp.SavedPMSOutcomeId from viewpmssubmissionwithlaststatus pp where pp.EmployeeId = T2.Id and pp.SubmissionTime >= ? order by pp.SubmissionTime DESC limit 1) as SavedPMSOutcomeId, T3.Name as Designation, T2.JobLocation, DATE_FORMAT(T2.DateOfAppointment,'%D %b, %Y') as DateOfAppointment, T2.BasicPay, T2.CIDNo, T4.ShortName as Department, T5.Name as Section,$queryAppend from mas_employee T2 join mas_gradestep C on C.Id = T2.GradeStepId join mas_designation T3 on T3.Id = T2.DesignationId join mas_department T4 on T4.Id = T2.DepartmentId left join mas_section T5 on T5.Id = T2.SectionId where T2.EmpId in (select distinct EmpId from pms_historical) and $condition order by T4.ShortName, T5.Name, T2.Name";
            $result = DB::select("$query", $parameters);
        } else {
            $result = [];
        }
        //END FETCH REPORT DATA

        if (!empty($result)) {
            if (Input::has('export') && Input::get('export') == 'excel') {
                Excel::create("PMS Result_" . date('Y_m_d_H_i_s'), function ($excel) use ($outcomes, $result, $employees, $pmsPeriodArray, $pmsPeriods, $departments, $sections) {
                    $excel->sheet("Sheet", function ($sheet) use ($outcomes, $result, $employees, $pmsPeriodArray, $pmsPeriods, $departments, $sections) {
                        $sheet->loadView('exports.pmsscorereport', ['outcomes' => $outcomes, 'result' => $result, 'employees' => isset($employees) ? $employees : [], 'pmsPeriodArray' => $pmsPeriodArray, 'pmsPeriods' => $pmsPeriods, 'departments' => $departments, 'sections' => $sections]);
                    });
                })->download('xlsx');
                return view('reports.pmsscorereport', ['outcomes' => $outcomes, 'result' => $result, 'employees' => isset($employees) ? $employees : [], 'pmsPeriodArray' => $pmsPeriodArray, 'pmsPeriods' => $pmsPeriods, 'departments' => $departments, 'sections' => $sections]);
            }
        }

        return view('reports.pmsscorereport', ['pmsYears' => $pmsYears, 'outcomes' => $outcomes, 'result' => $result, 'employees' => isset($employees) ? $employees : [], 'pmsPeriodArray' => $pmsPeriodArray, 'pmsPeriods' => $pmsPeriods, 'departments' => $departments, 'sections' => $sections]);
    }

    public function getPMSScoreReportData()
    {
        $pmsPeriods = $this->pmsPeriodsForReports();
        $pmsPeriodArray = [];
        $parameters = [];
        $employees = [];
        $condition = " 1=1";

        $departmentId = Input::get('DepartmentId');
        $employeeId = Input::get('EmployeeId');
        $sectionId = Input::get('SectionId');
        $pmsPeriod = Input::get('PMSPeriod');

        if ((bool) $pmsPeriod && !empty($pmsPeriod) && !(count($pmsPeriod) == 1 && $pmsPeriod[0] == '')) {
            $pmsPeriodArray = $pmsPeriod;
        } else {
            foreach ($pmsPeriods as $pmsPeriod):
                $pmsPeriodArray[] = $pmsPeriod->Id;
            endforeach;
        }

        $queryAppend = '';
        foreach ($pmsPeriodArray as $pmsPeriodId):
            if ($queryAppend != '') {
                $queryAppend .= ",";
            }
            $queryAppend .= " coalesce((select coalesce(A.PMSResult,'') from pms_historical A where A.EmpId = T2.EmpId and A.PMSNumberId = ?),'') as '$pmsPeriodId Result'";
            $queryAppend .= ", coalesce((select coalesce(A.PMSScore,'') from pms_historical A where A.EmpId = T2.EmpId and A.PMSNumberId = ?),'') as '$pmsPeriodId Score'";
            $queryAppend .= ", coalesce((select coalesce(A.PMSRemarks,'') from pms_historical A where A.EmpId = T2.EmpId and A.PMSNumberId = ?),'') as '$pmsPeriodId Remarks'";
            $parameters[] = $pmsPeriodId;
            $parameters[] = $pmsPeriodId;
            $parameters[] = $pmsPeriodId;
        endforeach;

        if ((bool) $employeeId) {
            $condition .= " and T2.Id = ?";
            $parameters[] = $employeeId;
            $hasParams = true;
        } else {
            if ((bool) $sectionId) {
                $employees = $this->getSectionEmployees($sectionId, false, false);
                $condition .= " and T2.SectionId = ?";
                $parameters[] = $sectionId;
                $hasParams = true;
            } else {
                if ((bool) $departmentId) {
                    $employees = $this->getDepartmentEmployees($departmentId, false, false);
                    $condition .= " and T2.DepartmentId = ?";
                    $parameters[] = $departmentId;
                    $hasParams = true;
                }
            }
        }

        $query = "SELECT distinct T2.Id, T2.Name as Employee,T2.DateOfAppointment as DateOfAppointmentRaw, case when coalesce(T2.NoProbation,0) = 0 
            and C.PayScale is not null then DATE_ADD(T2.DateOfAppointment, INTERVAL 6 MONTH) else T2.DateOfAppointment end as DateOfRegularization, 
            C.Name as GradeStep, T2.EmpId, T3.Name as Designation, T2.JobLocation, DATE_FORMAT(T2.DateOfAppointment,'%D %b, %Y') as DateOfAppointment, 
            T2.BasicPay, T2.CIDNo, T4.ShortName as Department, T5.Name as Section,$queryAppend from mas_employee T2 join mas_gradestep C on C.Id = T2.GradeStepId 
            join mas_designation T3 on T3.Id = T2.DesignationId join mas_department T4 on T4.Id = T2.DepartmentId left join mas_section T5 on T5.Id = T2.SectionId 
            where T2.EmpId in (select distinct EmpId from pms_historical) and $condition order by T4.ShortName, T5.Name";
        $result = DB::select("$query", $parameters);

        return response()->json($result);
    }

    public function getSectionWisePerformance()
    {
        $departments = $this->fetchActiveDepartments();
        $pmsPeriods = $this->pmsPeriodsForReports();
        $pmsPeriodArray = [];
        $parameters = [];
        $condition = " 1=1";

        $departmentId = Input::get('DepartmentId');
        $pmsPeriod = Input::get('PMSPeriod');

        if ((bool) $pmsPeriod && !empty($pmsPeriod) && !(count($pmsPeriod) == 1 && $pmsPeriod[0] == '')) {
            $pmsPeriodArray = $pmsPeriod;
        } else {
            foreach ($pmsPeriods as $pmsPeriod):
                $pmsPeriodArray[] = $pmsPeriod->Id;
            endforeach;
        }

        $queryAppend = '';
        foreach ($pmsPeriodArray as $pmsPeriodId):
            if ($queryAppend != '') {
                $queryAppend .= ",";
            }
            $queryAppend .= "coalesce((select AVG(coalesce(A.PMSScore,'')) from pms_historical A join mas_employee B on B.EmpId = A.EmpId where A.PMSScore > 0 and B.SectionId = T1.Id and A.PMSNumberId = ?),'') as '$pmsPeriodId Score'";
            $parameters[] = $pmsPeriodId;
        endforeach;

        if ((bool) $departmentId) {
            $condition .= " and T1.DepartmentId = ?";
            $parameters[] = $departmentId;
        }

        $query = "SELECT distinct T1.Id, T1.Name as Section, T2.ShortName as Department, $queryAppend from mas_section T1 join mas_department T2 on T2.Id = T1.DepartmentId where $condition order by T2.ShortName,T1.Name";
        $result = DB::select("$query", $parameters);
        return view('reports.pmsscoresection')->with('pmsPeriodArray', $pmsPeriodArray)->with('pmsPeriods', $pmsPeriods)->with('result', $result)->with('departments', $departments);
    }

    public function getDepartmentWisePerformance()
    {
        $departments = $this->fetchActiveDepartments();
        $pmsPeriods = $this->pmsPeriodsForReports();
        $pmsPeriodArray = [];
        $parameters = [];
        $condition = " 1=1";

        $pmsPeriod = Input::get('PMSPeriod');
        $departmentId = Input::get('DepartmentId');

        if ((bool) $pmsPeriod && !empty($pmsPeriod) && !(count($pmsPeriod) == 1 && $pmsPeriod[0] == '')) {
            $pmsPeriodArray = $pmsPeriod;
        } else {
            foreach ($pmsPeriods as $pmsPeriod):
                $pmsPeriodArray[] = $pmsPeriod->Id;
            endforeach;
        }

        $queryAppend = '';
        foreach ($pmsPeriodArray as $pmsPeriodId):
            if ($queryAppend != '') {
                $queryAppend .= ",";
            }
            $queryAppend .= "coalesce((select AVG(coalesce(A.PMSScore,'')) from pms_historical A join mas_employee B on B.EmpId = A.EmpId where A.PMSScore > 0 and B.DepartmentId = T1.Id and A.PMSNumberId = ?),'') as '$pmsPeriodId Score'";
            $parameters[] = $pmsPeriodId;
        endforeach;

        if ((bool) $departmentId) {
            $condition .= " and T1.Id = ?";
            $parameters[] = $departmentId;
        }

        $query = "SELECT T1.ShortName as Department, $queryAppend from mas_department T1 where $condition order by T1.ShortName";
        $result = DB::select("$query", $parameters);
        return view('reports.pmsscoredepartment')->with('departments', $departments)->with('pmsPeriodArray', $pmsPeriodArray)->with('pmsPeriods', $pmsPeriods)->with('result', $result);
    }

    public function getOrganizationalPerformance()
    {
        $pmsPeriods = $this->pmsPeriodsForReports();
        $pmsPeriodArray = [];
        $parameters = [];
        $condition = " 1=1";

        $departmentId = Input::get('DepartmentId');
        $pmsPeriod = Input::get('PMSPeriod');

        if ((bool) $pmsPeriod && !empty($pmsPeriod) && !(count($pmsPeriod) == 1 && $pmsPeriod[0] == '')) {
            $pmsPeriodArray = $pmsPeriod;
        } else {
            foreach ($pmsPeriods as $pmsPeriod):
                $pmsPeriodArray[] = $pmsPeriod->Id;
            endforeach;
        }

        $queryAppend = '';
        foreach ($pmsPeriodArray as $pmsPeriodId):
            if ($queryAppend != '') {
                $queryAppend .= ",";
            }
            $queryAppend .= "coalesce((select AVG(coalesce(A.PMSScore,'')) from pms_historical A join mas_employee B on B.EmpId = A.EmpId where A.PMSScore > 0 and A.PMSNumberId = ?),'') as '$pmsPeriodId Score'";
            $parameters[] = $pmsPeriodId;
        endforeach;

        $query = "SELECT 'Tashi InfoComm Limited' as Organization, $queryAppend";
        $result = DB::select("$query", $parameters);
        return view('reports.pmsscoreorganization')->with('pmsPeriodArray', $pmsPeriodArray)->with('pmsPeriods', $pmsPeriods)->with('result', $result);
    }

    public function getAuditTrailReport()
    {
        $perPage = 8;
        $userId = Input::get('UserId');
        $tableName = Input::get('TableName');
        $deleted = Input::get('Deleted');
        $fromDate = Input::get('FromDate');
        $toDate = Input::get('ToDate');

        $append = '1=1';
        $parameters = [];
        if ((bool) $userId) {
            $append .= " and T1.EmployeeId = ?";
            $parameters[] = $userId;
        }
        if ((bool) $tableName) {
            $append .= " and SUBSTR(T1.TableName,5) = ?";
            $parameters[] = $tableName;
        }
        if ((bool) $fromDate) {
            $append .= " and T1.ChangedOn >= ?";
            $parameters[] = "$fromDate 00:00:00";
        }
        if ((bool) $toDate) {
            $append .= " and T1.ChangedOn <= ?";
            $parameters[] = "$toDate 23:59:59";
        }
        if ($deleted !== '') {
            if ($deleted === '0' || $deleted === '1') {
                $append .= " and T1.Deleted = ?";
                $parameters[] = $deleted;
            }
        }

        $adminUsers = DB::select("SELECT distinct T2.Id, T2.Name from sys_databasechangehistory T1 join mas_employee T2 on T2.Id = T1.EmployeeId");
        $tables = DB::select("SELECT distinct TableName from sys_databasechangehistory");
        $reportData = DB::table('sys_databasechangehistory as T1')
            ->join('mas_employee as T2', 'T2.Id', '=', 'T1.EmployeeId')
            ->whereRaw("$append", $parameters)
            ->select('T2.Name', 'T1.TableName', DB::raw("DATE_FORMAT(T1.ChangedOn,'%D %b, %Y %l:%i %p') as ChangedOn"), DB::raw("case when T1.Deleted = 1 then 'Yes' else 'No' end as Deleted"), 'T1.Changes')
            ->orderBy("T1.ChangedOn", "DESC")
            ->paginate($perPage);
        return view('reports.audittrail')->with('adminUsers', $adminUsers)->with('tables', $tables)->with('perPage', $perPage)->with('reportData', $reportData);
    }

    public function getEligibleForMeritorious(Request $request)
    {
        if ((Auth::user()->RoleId <> 1) && !in_array(Auth::user()->PositionId, [CONST_POSITION_HOD, CONST_POSITION_MD])) {
            abort(404);
        }

        $this->populateFirstPMSNumberId();
        $queryCondition = "";
        $queryParam = [];
        if ($request->has("DepartmentId")) {
            $queryCondition .= " and T1.DepartmentId = ?";
            $queryParam[] = $request->input("DepartmentId");
        }

        $outstandingEligible = DB::select("SELECT T1.Id, T1.EmpId, T2.OutstandingCount as Requirement, T1.CIDNo, T3.ShortName as Department, coalesce(T4.Name,T3.ShortName) as Section,
            (select max(A.PMSNumber) from view_pmshistorical A where A.EmpId = T1.EmpId and A.ExcludeFromIncentives = 0 and
            TRIM(A.PMSResult) in ('RSP','MSP','MSP+SI','MSP+DI','MDP','MDP+SI','MDP+DI','MSP+PP','MSP+PP+SI','MSP+PP+DI')) AS `Round of Last Reward`,
            T1.Name as Employee, T5.Name as Designation, T6.Name as GradeStep,
            (select count(A.Id) from view_pmshistorical A where A.EmpId = T1.EmpId and A.PMSScore >= 92 and A.PMSNumber > COALESCE(`Round of Last Reward`,1) and A.PMSNumber >= T1.FullPMSRoundFrom ) as `Achieved`
            from mas_employee T1 JOIN pms_promotioncriteria T2 on T1.GradeStepId = T2.FromGradeStepId join mas_department T3 on T3.Id = T1.DepartmentId
            left join mas_section T4 on T4.Id = T1.SectionId JOIN mas_designation T5 on T5.Id = T1.DesignationId join mas_gradestep T6 on T6.Id = T1.GradeStepId
            where T1.Status = 1 $queryCondition having `Achieved` >= Requirement ORDER BY T3.Name, T4.Name, T1.Name", $queryParam);

        $append = " and 1=1";
        $outstandingEligibleIds = [];
        foreach ($outstandingEligible as $outstandingSingle):
            $append .= " and T1.Id <> ?";
            $queryParam[] = $outstandingSingle->Id;
	    $outstandingEligibleIds[] = $outstandingSingle->Id;

	    // Assigning MSP To Particular Employee
            $employeeId = $outstandingSingle->Id;
            $empId = $outstandingSingle->EmpId;
            $employeeSubmissionStatus = DB::select("SELECT A.PMSNumberId, A.PMSSubmissionId, A.PMSResult FROM pms_historical A WHERE A.EmpId = ? ORDER BY A.PMSNumberId DESC LIMIT 1 ", [$empId]);
            $pmsSubmissionId = $employeeSubmissionStatus[0]->PMSSubmissionId;

            DB::update("UPDATE pms_submission B SET B.SavedPMSOutcomeId = 4, B.PMSOutcomeId = NULL WHERE B.Id = ? AND B.EmployeeId = ? ", [$pmsSubmissionId, $employeeId]);
        endforeach;

        $outstandingAndGoodEligible = DB::select("SELECT T1.Id, T1.EmpId, T2.OutstandingAndGoodCount as Requirement, T1.Name as Employee, T5.Name as Designation, T6.Name as GradeStep, T1.CIDNo, T3.ShortName as Department, coalesce(T4.Name,T3.ShortName) as Section,
            (select max(A.PMSNumber) from view_pmshistorical A where A.EmpId = T1.EmpId and A.ExcludeFromIncentives = 0 and
            TRIM(A.PMSResult) in ('RSP','MSP','MSP+SI','MSP+DI','MDP','MDP+SI','MDP+DI','MSP+PP','MSP+PP+SI','MSP+PP+DI')) AS `Round of Last Reward`,
            (select count(A.Id) from view_pmshistorical A where A.EmpId = T1.EmpId and A.PMSScore >= 80 and A.PMSNumber > COALESCE(`Round of Last Reward`,1) and A.PMSNumber >= T1.FullPMSRoundFrom) as `Achieved`
            from mas_employee T1 JOIN pms_promotioncriteria T2 on T1.GradeStepId = T2.FromGradeStepId join mas_department T3 on T3.Id = T1.DepartmentId left join mas_section T4 on T4.Id = T1.SectionId JOIN
            mas_designation T5 on T5.Id = T1.DesignationId join mas_gradestep T6 on T6.Id = T1.GradeStepId where T1.Status = 1 $queryCondition$append having `Achieved` >= Requirement ORDER BY T3.Name, T4.Name, T1.Name", $queryParam);

        $outstandingAndGoodEligibleIds = [];
        foreach ($outstandingAndGoodEligible as $outstandingAndGoodSingle):
            $append .= " and T1.Id <> ?";
            $queryParam[] = $outstandingAndGoodSingle->Id;
	    $outstandingAndGoodEligibleIds[] = $outstandingAndGoodSingle->Id;

	    // Assigning MSP To Particular Employee
            $employeeId = $outstandingAndGoodSingle->Id;
            $empId = $outstandingAndGoodSingle->EmpId;
            $employeeSubmissionStatus = DB::select("SELECT A.PMSNumberId, A.PMSSubmissionId, A.PMSResult FROM pms_historical A WHERE A.EmpId = ? ORDER BY A.PMSNumberId DESC LIMIT 1 ", [$empId]);
            $pmsSubmissionId = $employeeSubmissionStatus[0]->PMSSubmissionId;

            DB::update("UPDATE pms_submission B SET B.SavedPMSOutcomeId = 4, B.PMSOutcomeId = NULL WHERE B.Id = ? AND B.EmployeeId = ? ", [$pmsSubmissionId, $employeeId]);
        endforeach;

        if (Input::has('export') && Input::get('export') == 'excel') {
            Excel::create("Eligible_for_Meritorious_" . date('Y_m_d_H_i_s'), function ($excel) use ($outstandingEligibleIds, $outstandingAndGoodEligibleIds, $outstandingAndGoodEligible, $outstandingEligible) {
                $excel->sheet("Sheet", function ($sheet) use ($outstandingEligibleIds, $outstandingAndGoodEligibleIds, $outstandingAndGoodEligible, $outstandingEligible) {
                    $sheet->loadView('exports.eligibleformeritorious', ['outstandingAndGoodEligible' => $outstandingAndGoodEligible, 'outstandingEligible' => $outstandingEligible]);
                });
            })->download('xlsx');
            return view('reports.eligibleformeritorious', ['departments' => $this->fetchActiveDepartments(), 'outstandingAndGoodEligible' => $outstandingAndGoodEligible, 'outstandingEligible' => $outstandingEligible]);
        }

        return view("reports.eligibleformeritorious")
            ->with('departments', $this->fetchActiveDepartments())
            ->with('outstandingEligible', $outstandingEligible)
            ->with('outstandingEligibleIds', $outstandingEligibleIds)
            ->with('outstandingAndGoodEligible', $outstandingAndGoodEligible)
            ->with('outstandingAndGoodEligibleIds', $outstandingAndGoodEligibleIds);
    }

    public function getEligibleForLoa(Request $request)
    {
        if ((Auth::user()->RoleId <> 1) && !in_array(Auth::user()->PositionId, [CONST_POSITION_HOD, CONST_POSITION_MD])) {
            abort(404);
        }

        $this->populateFirstPMSNumberId();
        $queryCondition = "";
        $queryParam = [];
        if ($request->has("DepartmentId")) {
            $queryCondition .= " and T1.DepartmentId = ?";
            $queryParam[] = $request->input("DepartmentId");
        }

        $append = " and 1=1";

        $currentRound = DB::table('sys_pmsnumber')->where('StartDate', '<=', date('Y-m-d'))->orderBy('StartDate', 'DESC')->value('PMSNumber');
        $threeRoundsIncludingCurrent = DB::table("sys_pmsnumber")->whereRaw("PMSNumber <= ?", [$currentRound])->orderBy("PMSNumber", "DESC")->take(3)->pluck("PMSNumber");
        $threeRoundsIncludingCurrentString = implode(",", $threeRoundsIncludingCurrent);

        $loaEligible = DB::select("SELECT T1.Id,T1.EmpId, 3 as Requirement,(select max(A.PMSNumber) from view_pmshistorical A
            where A.EmpId = T1.EmpId and TRIM(coalesce(A.PMSResult,'No Action')) in ('RSP','MSP','MSP+SI','MSP+DI','MSP+PP','MSP+PP+SI','MSP+PP+DI',
                'MDP','MDP+SI','MDP+DI','DI','LoA','MSP + SH','MSP + SH (Manager)','Technical Supervisor','Section Head','SI + SH','MSP & Section Head','SH','LoI','SI','PP',
                'PP + SI','PP+DI','PP to TS','PP+DI+LoA','PP+SI','Letter by AND','LLW','MDP and Change in Designation','LAI','LAI + Mentoring')) AS `Round of Last Action`, T1.Name as Employee, T5.Name as Designation, T6.Name as GradeStep,
            T1.CIDNo, T3.ShortName as Department, coalesce(T4.Name,T3.ShortName) as Section, (select count(A.Id) from view_pmshistorical A where A.EmpId = T1.EmpId and A.PMSScore >= 92
            and A.PMSNumber > COALESCE(`Round of Last Action`,1) and A.PMSNumber in ($threeRoundsIncludingCurrentString) and A.PMSNumber >= T1.FullPMSRoundFrom ) as `Achieved` from mas_employee T1 join mas_department T3 on T3.Id = T1.DepartmentId left join mas_section T4 on T4.Id = T1.SectionId JOIN
            mas_designation T5 on T5.Id = T1.DesignationId join mas_gradestep T6 on T6.Id = T1.GradeStepId where T1.Status = 1 $queryCondition$append having `Achieved` >= Requirement ORDER BY T3.Name, T4.Name, T1.Name", $queryParam);
	
	foreach ($loaEligible as $loa):
            // Assigning LoA To Particular Employee
            $employeeId = $loa->Id;
            $empId = $loa->EmpId;
            $employeeSubmissionStatus = DB::select("SELECT A.PMSNumberId, A.PMSSubmissionId, A.PMSResult FROM pms_historical A WHERE A.EmpId = ? ORDER BY A.PMSNumberId DESC LIMIT 1 ", [$empId]);
            $pmsSubmissionId = $employeeSubmissionStatus[0]->PMSSubmissionId;

            $getSubmissionPmsOutcome = DB::select("SELECT A.SavedPMSOutcomeId FROM pms_submission A WHERE A.Id = ? AND A.EmployeeId = ? ", [$pmsSubmissionId, $employeeId]);
            $pmsOutcomeId = (int) $getSubmissionPmsOutcome[0]->SavedPMSOutcomeId;

            $pmsOutcomeIdList = [4, 17];
            if (!in_array($pmsOutcomeId, $pmsOutcomeIdList)) {
                // if not in MSP & RSP
                DB::update("UPDATE pms_submission B SET B.SavedPMSOutcomeId = 11, B.PMSOutcomeId = NULL WHERE B.Id = ? AND B.EmployeeId = ? ", [$pmsSubmissionId, $employeeId]);
            }
        endforeach;

        if (Input::has('export') && Input::get('export') == 'excel') {
            Excel::create("Eligible_for_LoA_" . date('Y_m_d_H_i_s'), function ($excel) use ($loaEligible) {
                $excel->sheet("Sheet", function ($sheet) use ($loaEligible) {
                    $sheet->loadView('exports.eligibleforloa', ['loaEligible' => $loaEligible]);
                });
            })->download('xlsx');
            return view('reports.eligibleforloa', ['departments' => $this->fetchActiveDepartments(), 'loaEligible' => $loaEligible]);
        }

        return view("reports.eligibleforloa")
            ->with('departments', $this->fetchActiveDepartments())
            ->with('loaEligible', $loaEligible);
    }

    public function getEligibleForRegular(Request $request)
    {
        if ((Auth::user()->RoleId <> 1) && !in_array(Auth::user()->PositionId, [CONST_POSITION_HOD, CONST_POSITION_MD])) {
            abort(404);
        }

        $this->populateFirstPMSNumberId();
        $queryCondition = "";
        $queryParam = [];
        if ($request->has("DepartmentId")) {
            $queryCondition .= " and T1.DepartmentId = ?";
            $queryParam[] = $request->input("DepartmentId");
        }

        // Poor Performance with score less than 70

        $append = " and 1=1";

        $regularPromotionEligible = DB::select("SELECT DISTINCT T1.Id,T1.EmpId, T2.RegularPromotionCount AS Requirement, T1.DateOfAppointment,
            CASE WHEN COALESCE(T1.NoProbation,0) = 0 AND T6.PayScale IS NOT NULL THEN DATE_ADD(T1.DateOfAppointment, INTERVAL 6 MONTH) ELSE T1.DateOfAppointment END AS DateOfRegularization,
            (SELECT MAX(A.PMSNumber) FROM view_pmshistorical A WHERE A.EmpId = T1.EmpId AND A.ExcludeFromIncentives = 0 AND
            TRIM(A.PMSResult) IN ('RSP','MSP','MSP+SI','MSP+DI','MDP','MDP+SI','MDP+DI','MSP+PP','MSP+PP+SI','MSP+PP+DI', 'MDP and Change in Designation','MSP + SH','MSP + SI','MSP + SH (Manager)','MSP & Section Head')) AS `Round of Last Reward`,
            T1.Name AS Employee, T5.Name AS Designation, T6.Name AS GradeStep, T1.CIDNo, T3.ShortName AS Department, COALESCE(T4.Name,T3.ShortName) AS Section,
            (SELECT CASE WHEN LEAD(A.PMSScore) OVER (ORDER BY A.Id) IS NOT NULL THEN 0 ELSE 1 END AS PMSRound FROM view_pmshistorical A WHERE A.EmpId = T1.EmpId AND A.PMSScore < 70 AND A.PMSNumber > COALESCE(`Round of Last Reward`,1) AND A.PMSNumber >= T1.FullPMSRoundFrom HAVING COUNT(A.Id) > 1) as PoorPerformance,
            (SELECT COUNT(A.Id) - IFNULL(PoorPerformance, 0) FROM view_pmshistorical A WHERE A.EmpId = T1.EmpId AND A.PMSNumber > COALESCE(`Round of Last Reward`,1) AND A.PMSNumber >= T1.FullPMSRoundFrom) AS Achieved,
            (SELECT COUNT(ROUND((DATEDIFF(DATE_FORMAT(SYSDATE(), '%Y%m%d'), DATE_FORMAT(DateOfRegularization, '%Y%m%d'))) / 182.5, 2)) FROM view_pmshistorical A WHERE A.EmpId = T1.EmpId AND A.PMSNumber > COALESCE(`Round of Last Reward`,1) AND A.PMSNumber >= T1.FullPMSRoundFrom) AS AchievementDOR
            FROM mas_employee T1 JOIN pms_promotioncriteria T2 ON T1.GradeStepId = T2.FromGradeStepId JOIN mas_department T3 ON T3.Id = T1.DepartmentId LEFT JOIN mas_section T4 ON T4.Id = T1.SectionId JOIN mas_designation T5 ON T5.Id = T1.DesignationId JOIN mas_gradestep T6 ON T6.Id = T1.GradeStepId
            WHERE T1.Status = 1 $queryCondition$append HAVING Achieved >= Requirement OR AchievementDOR >= Requirement ORDER BY T3.Name, T4.Name, T1.Name ", $queryParam);

        $append = " and 1=1";
        $regularPromotionEligibleIds = [];
        foreach ($regularPromotionEligible as $regularPromotion):
            $append .= " and T1.Id <> ?";
            $queryParam[] = $regularPromotion->Id;
	    $regularPromotionEligibleIds[] = $regularPromotion->Id;

	    // Assigning RSP To Particular Employee
            $employeeId = $regularPromotion->Id;
            $empId = $regularPromotion->EmpId;
            $employeeSubmissionStatus = DB::select("SELECT A.PMSNumberId, A.PMSSubmissionId, A.PMSResult FROM pms_historical A WHERE A.EmpId = ? ORDER BY A.PMSNumberId DESC LIMIT 1 ", [$empId]);
            $pmsSubmissionId = $employeeSubmissionStatus[0]->PMSSubmissionId;

            DB::update("UPDATE pms_submission B SET B.SavedPMSOutcomeId = 17, B.PMSOutcomeId = NULL WHERE B.Id = ? AND B.EmployeeId = ? ", [$pmsSubmissionId, $employeeId]);
        endforeach;

        if (Input::has('export') && Input::get('export') == 'excel') {
            Excel::create("Eligible_for_Regular_" . date('Y_m_d_H_i_s'), function ($excel) use ($regularPromotionEligible, $regularPromotionEligibleIds) {
                $excel->sheet("Sheet", function ($sheet) use ($regularPromotionEligible, $regularPromotionEligibleIds) {
                    $sheet->loadView('exports.eligibleforregular', ['regularPromotionEligible' => $regularPromotionEligible, 'regularPromotionEligibleIds' => $regularPromotionEligibleIds]);
                });
            })->download('xlsx');
            return view('reports.eligibleforregular', ['departments' => $this->fetchActiveDepartments(), 'regularPromotionEligibleIds' => $regularPromotionEligibleIds, 'regularPromotionEligible' => $regularPromotionEligible]);
        }

        return view("reports.eligibleforregular")
            ->with('departments', $this->fetchActiveDepartments())
            ->with('regularPromotionEligible', $regularPromotionEligible)
            ->with('regularPromotionEligibleIds', $regularPromotionEligibleIds);
    }

    public function getLowPerformingEmployees(Request $request)
    {
        if ((Auth::user()->RoleId <> 1) && !in_array(Auth::user()->PositionId, [CONST_POSITION_HOD, CONST_POSITION_MD])) {
            abort(404);
        }

        $queryCondition = "";
        $queryParam = [];
        $append = "1=1";

        //FETCH Last 3, Last 2, Last Round and store in strings to use as query condition
        $currentRound = DB::table('sys_pmsnumber')->where('StartDate', '<=', date('Y-m-d'))->orderBy('StartDate', 'DESC')->value('PMSNumber');
        $threeRoundsIncludingCurrent = DB::table("sys_pmsnumber")->whereRaw("PMSNumber <= ?", [$currentRound])->orderBy("PMSNumber", "DESC")->take(3)->pluck("PMSNumber");
        $threeRoundsIncludingCurrentString = implode(",", $threeRoundsIncludingCurrent);
        $twoRoundsIncludingCurrent = DB::table("sys_pmsnumber")->whereRaw("PMSNumber <= ?", [$currentRound])->orderBy("PMSNumber", "DESC")->take(2)->pluck("PMSNumber");
        $twoRoundsIncludingCurrentString = implode(",", $twoRoundsIncludingCurrent);
        $oneRoundIncludingCurrent = DB::table("sys_pmsnumber")->whereRaw("PMSNumber <= ?", [$currentRound])->orderBy("PMSNumber", "DESC")->take(1)->pluck("PMSNumber");
        $oneRoundIncludingCurrentString = implode(",", $oneRoundIncludingCurrent);
        //END

        //Liable for Compulsary Retirement - Poor scores in last 3 pms rounds
        $level3 = DB::select("SELECT T1.Id,T1.EmpId, 3 as Requirement, T1.Name as Employee, T5.Name as Designation, T6.Name as GradeStep, 
            T1.CIDNo, T3.ShortName as Department, coalesce(T4.Name,T3.ShortName) as Section, (select count(A.Id) from view_pmshistorical A where A.EmpId = T1.EmpId and A.PMSScore < 70 
            and A.PMSNumber in ($threeRoundsIncludingCurrentString) ) as `Achieved` from mas_employee T1 join mas_department T3 on T3.Id = T1.DepartmentId left join mas_section T4 on T4.Id = T1.SectionId JOIN 
            mas_designation T5 on T5.Id = T1.DesignationId join mas_gradestep T6 on T6.Id = T1.GradeStepId where T1.Status = 1 and $append$queryCondition having `Achieved` >= Requirement ORDER BY T3.Name, T4.Name, T1.Name", $queryParam);

        //ADD Id of employees fulfilling above condition to query condition in order to exclude them from subsequent lists
        foreach ($level3 as $level3Single):
            $append .= " and T1.Id <> ?";
            $queryParam[] = $level3Single->Id;
        endforeach;

        //Liable for Letter of Last Warning - Poor scores in last 2 pms rounds
        $level2 = DB::select("SELECT T1.Id,T1.EmpId, 2 as Requirement, T1.Name as Employee, T5.Name as Designation, T6.Name as GradeStep, 
            T1.CIDNo, T3.ShortName as Department, coalesce(T4.Name,T3.ShortName) as Section, (select count(A.Id) from view_pmshistorical A where A.EmpId = T1.EmpId and A.PMSScore < 70 
            and A.PMSNumber in ($twoRoundsIncludingCurrentString) ) as `Achieved` from mas_employee T1 join mas_department T3 on T3.Id = T1.DepartmentId left join mas_section T4 on T4.Id = T1.SectionId JOIN 
            mas_designation T5 on T5.Id = T1.DesignationId join mas_gradestep T6 on T6.Id = T1.GradeStepId where T1.Status = 1 and $append$queryCondition having `Achieved` >= Requirement ORDER BY T3.Name, T4.Name, T1.Name", $queryParam);

        //ADD Id of employees fulfilling above condition to query condition in order to exclude them from subsequent lists
        foreach ($level2 as $level2Single):
            $append .= " and T1.Id <> ?";
            $queryParam[] = $level2Single->Id;
        endforeach;

        //Liable asking Improvement - Poor scores in last 1 pms rounds
        $level1 = DB::select("SELECT T1.Id,T1.EmpId, 1 as Requirement, T1.Name as Employee, T5.Name as Designation, T6.Name as GradeStep, 
            T1.CIDNo, T3.ShortName as Department, coalesce(T4.Name,T3.ShortName) as Section, (select count(A.Id) from view_pmshistorical A where A.EmpId = T1.EmpId and A.PMSScore < 70 
            and A.PMSNumber in ($oneRoundIncludingCurrentString) ) as `Achieved` from mas_employee T1 join mas_department T3 on T3.Id = T1.DepartmentId left join mas_section T4 on T4.Id = T1.SectionId JOIN 
            mas_designation T5 on T5.Id = T1.DesignationId join mas_gradestep T6 on T6.Id = T1.GradeStepId where T1.Status = 1 and $append$queryCondition having `Achieved` >= Requirement ORDER BY T3.Name, T4.Name, T1.Name", $queryParam);

        return view("reports.lowperformingemployees")
            ->with('departments', $this->fetchActiveDepartments())
            ->with('level1', $level1)
            ->with('level2', $level2)
            ->with('level3', $level3);
    }

    public function populateFirstPMSNumberId()
    {
        $employees = DB::select("SELECT Id, EmpId, DateOfAppointment,NoProbation,FullPMSRoundFrom from mas_employee where FullPMSRoundFrom is null and coalesce(Status,0)=1");
        foreach ($employees as $employee):
            $id = $employee->Id;
            $noProbation = $employee->NoProbation;
            $dateOfAppointment = $employee->DateOfAppointment;
            if ((int) $noProbation === 1) {
                $dateOfRegularization = $dateOfAppointment;
            } else {
                $dateOfRegularizationRaw = date_add(date_create($dateOfAppointment), date_interval_create_from_date_string("6 months"));
                $dateOfRegularization = $dateOfRegularizationRaw->format("Y-m-d");
            }
            $closestPMSRoundQuery = DB::table("sys_pmsnumber")->whereRaw("StartDate >= ?", [$dateOfRegularization])->orderBy("StartDate")->take(1)->get(['Id', 'PMSNumber']);
            $closestPMSRound = count($closestPMSRoundQuery) ? (int) $closestPMSRoundQuery[0]->PMSNumber : 0;
            $closestPMSRound += 1;
            DB::table("mas_employee")->where("Id", $id)->update(['FullPMSRoundFrom' => $closestPMSRound]);
        endforeach;
    }

    public function withDrawalEligibleEmployee($pmsSubmissionId, $employeeId)
    {
        $message = "Eligible Employee Successfully WithDrawal.";
        try {
            $this->saveAuditTrail('pms_submission', $pmsSubmissionId, $employeeId);
            DB::update("UPDATE pms_submission A SET A.WithDrawal = 1 WHERE A.Id = ? AND A.EmployeeId = ? ", [$pmsSubmissionId, $employeeId]);
        } catch (\Exception $e) {
            return back()->with('errormessage', "Eligible Employee Cannot be WithDrawal. Please Try again Later.");
        }

        return back()->with('successmessage', $message);
    }

}
