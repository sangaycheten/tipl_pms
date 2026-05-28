<?php

namespace App\Http\Controllers\Application;

use App\PMSEmployeeGoal;
use App\PMSEmployeeGoalDetail;
use App\PMSSubmission;
use App\PMSSubmissionDetail;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Auth;
use App\Http\Controllers\Controller;

class AuditPMSSubmissionController extends Controller
{
    public function getAuditEmployeeIndex()
    {
        $today = date('Y-m-d');
        $pmsPeriod = DB::select("SELECT B.PMSNumber, B.StartDate, B.Status FROM sys_pmsnumber B WHERE B.StartDate <= ? AND B.PMSNumber != 0 AND B.PMSNumber >= 19 ORDER BY B.PMSNumber DESC LIMIT 1 ", [$today]);
        $pmsPeriodId = $pmsPeriod[0]->PMSNumber;
	$pmsStartDate = $pmsPeriod[0]->StartDate;
	$pmsStatusId = $pmsPeriod[0]->Status;

        $employees = DB::select("SELECT T1.Id, S.Name AS Section, D.Name AS Department, P.Id AS GoalDefinitionId,P.Status AS GoalDefinitionStatus, T1.Name as Employee, T2.PMSOutcomeId, T2.Id AS PMSSubmissionId, O.Name AS Designation, Z.Name AS Position, T2.LastStatusId, T3.Name AS Status,
            T2.Id AS SubmissionId FROM (mas_employee T1 JOIN mas_designation O ON O.Id = T1.DesignationId JOIN mas_department D ON D.Id = T1.DepartmentId LEFT JOIN mas_section S ON S.Id = T1.SectionId
            LEFT JOIN mas_hierarchy B ON B.EmployeeId = T1.Id) LEFT JOIN pms_employeegoal P ON P.EmployeeId = T1.Id AND P.SysPmsNumberId = ? JOIN mas_gradestep Z ON Z.Id = T1.GradeStepId LEFT JOIN mas_position A ON A.Id = T1.PositionId
            LEFT JOIN (viewpmssubmissionwithlaststatus T2 JOIN mas_pmsstatus T3 ON T3.Id = T2.LastStatusId) ON T2.EmployeeId = T1.Id AND T2.SubmissionTime >= ?
            WHERE COALESCE(T1.Status,0) = 1 AND T1.DepartmentId = 12 ORDER BY A.DisplayOrder, T1.Name ", [$pmsPeriodId, $pmsStartDate]);

        return view('audits.auditemployeeindex', ['employees' => $employees, 'pmsPeriodId' => $pmsPeriodId, 'pmsStatusId' => $pmsStatusId]);
    }

    public function auditEmployeePmsSubmission($employeeId, $pmsPeriodId)
    {
        $employee = DB::select("SELECT A.EmpId, A.CIDNo, A.Name as Employee, A.PositionId, A.DepartmentId FROM mas_employee A WHERE A.Id = ? ", [$employeeId]);
        $positionId = $employee[0]->PositionId;
        $departmentId = $employee[0]->DepartmentId;

        $appraisalStructure = DB::select("SELECT a.ReportingLevel1EmployeeId, a.ReportingLevel2EmployeeId FROM mas_hierarchy a WHERE a.EmployeeId = ? ", [$employeeId]);
        $appraiserLevel1Id = $appraisalStructure[0]->ReportingLevel1EmployeeId;
        $appraiserLevel2Id = $appraisalStructure[0]->ReportingLevel2EmployeeId;

        $hasLevel2 = 0;
        if (!empty($appraiserLevel2Id)) {
            $hasLevel2 = 1;
        }

        $positionDepartment = DB::select("SELECT a.Id FROM mas_positiondepartment a WHERE a.PositionId = ? AND a.DepartmentId = ? ", [$positionId, $departmentId]);
        $positionDepartmentId = $positionDepartment[0]->Id;
        $positionDepartmentRating = DB::select("SELECT a.Id, a.WeightageForLevel1, a.WeightageForLevel2 FROM mas_positiondepartmentrating a WHERE a.PositionDepartmentId = ? ", [$positionDepartmentId]);
        $positionDepartmentRatingId = $positionDepartmentRating[0]->Id;
        $weightageforlevel1 = $positionDepartmentRating[0]->WeightageForLevel1;
        $weightageforlevel2 = $positionDepartmentRating[0]->WeightageForLevel2;
        $positionDepartmentRatingCriteria = DB::select("SELECT * FROM mas_positiondepartmentratingcriteria a WHERE a.PositionDepartmentRatingId = ? ", [$positionDepartmentRatingId]);

        return view('audits.auditemployeepmssubmission')->with('employee', $employee)->with('hasLevel2', $hasLevel2)->with('weightageforlevel1', $weightageforlevel1)
            ->with('weightageforlevel2', $weightageforlevel2)->with('employeeId', $employeeId)->with('pmsPeriodId', $pmsPeriodId)
            ->with('positionDepartmentRatingCriteria', $positionDepartmentRatingCriteria)->with('positionDepartmentRatingId', $positionDepartmentRatingId)
            ->with('positionDepartmentRatingId', $positionDepartmentRatingId);
    }

    public function saveAuditEmployeePmsSubmission(Request $request)
    {
        $employeeId = $request->EmployeeId;
        $appraisalStructure = DB::select("SELECT a.ReportingLevel1EmployeeId, a.ReportingLevel2EmployeeId FROM mas_hierarchy a WHERE a.EmployeeId = ? ", [$employeeId]);
        $appraiserLevel1Id = $appraisalStructure[0]->ReportingLevel1EmployeeId;
        $appraiserLevel2Id = $appraisalStructure[0]->ReportingLevel2EmployeeId;

        $employee = $request->Employee;
        $cidNo = $request->CIDNo;
        $empId = $request->EmpId;
        $departmentId = $request->DepartmentId;
        $pmsId = $request->PmsNumberId;
        $remark = $request->Remarks;
        $positionDepartmentRatingId = $request->PositionDepartmentRatingId;

        $file = $request->file('File');
        $file2 = $request->file('File2');

        $extension = (bool) $file ? $file->getClientOriginalExtension() : 'xxx';
        $extension2 = (bool) $file2 ? $file2->getClientOriginalExtension() : 'xxx';

        if ($file != NULL && !$this->in_arrayi($extension, ['xls', 'xlsx', 'pdf', 'jpg', 'jpeg', 'gif', 'png', 'doc', 'docx', 'ods', 'ots', 'odt', 'ott', 'oth', 'odm'])) {
            return back()->with('errormessage', 'Wrong file format. Permitted file formats are image files or excel or word documents');
        }

        if ($file2 != NULL && !$this->in_arrayi($extension2, ['xls', 'xlsx', 'pdf', 'jpg', 'jpeg', 'gif', 'png', 'doc', 'docx', 'ods', 'ots', 'odt', 'ott', 'oth', 'odm'])) {
            return back()->with('errormessage', 'Wrong file format. Permitted file formats are image files or excel or word documents');
        }

        $directory = 'uploads/' . date('Y') . '/' . date('m');
        if ($file != NULL) {
            $fileName = 'PMS File_' . $empId . '_' . randomString() . randomString() . '.' . $file->getClientOriginalExtension();
            $file->move($directory, $fileName);
        } else {
            $fileName = NULL;
        }

        if ($file2 != NULL) {
            $fileName2 = 'PMS Additional Document_' . $empId . '_' . randomString() . randomString() . '.' . $file2->getClientOriginalExtension();
            $file2->move($directory, $fileName2);
        }

        $empDetails = DB::select("SELECT T1.DesignationId, T1.PositionId, T1.SectionId, T2.PayScale, T1.BasicPay, T1.GradeStepId FROM mas_employee T1 JOIN mas_gradestep T2 ON T2.Id = T1.GradeStepId WHERE T1.EmpId = ? ", [$empId]);

        $submission['Id'] = $submissionId = UUID();
        $submission['EmployeeId'] = $employeeId;
        $submission['DepartmentId'] = $departmentId;
        $submission['DesignationId'] = $empDetails[0]->DesignationId;
        $submission['SectionId'] = $empDetails[0]->SectionId;
        $submission['PositionId'] = $empDetails[0]->PositionId;
        $submission['PayScale'] = $empDetails[0]->PayScale;
        $submission['BasicPay'] = $empDetails[0]->BasicPay;
        $submission['GradeStepId'] = $empDetails[0]->GradeStepId;
        $submission['WeightageForLevel1'] = $weightageForLevel1 = round($request->WeightageForLevel1, 2);
        $submission['WeightageForLevel2'] = $weightageForLevel2 = round($request->WeightageForLevel2, 2);

        if ($file != NULL):
            $submission['FilePath'] = $directory . '/' . $fileName;
        endif;

        if ($file2 != NULL) {
            $submission['File2Path'] = $directory . '/' . $fileName2;
        }

        $submission['SubmissionTime'] = date('Y-m-d H:i:s');
        $submission['CreatedBy'] = Auth::user()->Id;
        $submission['created_at'] = date('Y-m-d H:i:s');
        PMSSubmission::create($submission);

        $inputs = $request->input('pmssubmission');
        foreach ($inputs as $detail):
            $submissiondetail['Id'] = UUID();
            $submissiondetail['SubmissionId'] = $submissionId;
            $submissiondetail['SubmissionId'] = $submissionId;
            $submissiondetail['AssessmentArea'] = $detail['Description'];
            $submissiondetail['Weightage'] = $detail['Weightage'];
            $submissiondetail['Weightage'] = $detail['Weightage'];
            $submissiondetail['ApplicableToLevel2'] = $applicableToLevel2 = $detail['ApplicableToLevel2'];
            $submissiondetail['SelfRating'] = $detail['SelfRating'];
            $submissiondetail['Level1Rating'] = $detail['Level1Rating'];

            if (!empty($detail['Level2Rating'])) {
                if ($applicableToLevel2 != 0) {
                    $submissiondetail['Level2Rating'] = $detail['Level2Rating'];
                }
            }

            $submissiondetail['CreatedBy'] = Auth::user()->Id;
            PMSSubmissionDetail::create($submissiondetail);
        endforeach;

	// pms submission history
	$submissionStartTime = date('Y-m-d H:i:s');
        $level1appraiserTime = date('Y-m-d H:i:s', strtotime('+10 minutes', strtotime($submissionStartTime)));
        $level2appraiserTime = date('Y-m-d H:i:s', strtotime('+10 minutes', strtotime($level1appraiserTime)));
	
        DB::table('pms_submissionhistory')->insert(['Id' => UUID(), 'SubmissionId' => $submissionId, 'PMSStatusId' => CONST_PMSSTATUS_SUBMITTED, 'StatusUpdateTime' => $submissionStartTime, 'StatusByEmployeeId' => $employeeId]);

        if (!empty($appraiserLevel1Id) && empty($appraiserLevel2Id)) {
            DB::table('pms_submissionhistory')->insert(['Id' => UUID(), 'Remarks' => $remark, 'SubmissionId' => $submissionId, 'PMSStatusId' => CONST_PMSSTATUS_APPROVED, 'StatusUpdateTime' => $level1appraiserTime, 'StatusByEmployeeId' => $appraiserLevel1Id]);
        } else {
            DB::table('pms_submissionhistory')->insert(['Id' => UUID(), 'SubmissionId' => $submissionId, 'PMSStatusId' => CONST_PMSSTATUS_VERIFIED, 'StatusUpdateTime' => $level1appraiserTime, 'StatusByEmployeeId' => $appraiserLevel1Id]);
        }

        if (!empty($appraiserLevel2Id)) {
            DB::table('pms_submissionhistory')->insert(['Id' => UUID(), 'Remarks' => $remark, 'SubmissionId' => $submissionId, 'PMSStatusId' => CONST_PMSSTATUS_APPROVED, 'StatusUpdateTime' => $level2appraiserTime, 'StatusByEmployeeId' => $appraiserLevel2Id]);
        }

        // pms submission historical
        DB::delete("DELETE FROM pms_historical where PMSNumberId = ? and EmpId = ?", [$pmsId, $empId]);
        DB::table('pms_submission')->where('Id', $submissionId)->update(['PMSOutcomeId' => CONST_PMSOUTCOME_NOACTION]);

        $sumOfLevel1Weightage = DB::select("SELECT SUM(a.Weightage) as Level1WeightageTotal FROM mas_positiondepartmentratingcriteria a WHERE a.PositionDepartmentRatingId = ? AND a.ApplicableToLevel2 IN (1,0)", [$positionDepartmentRatingId]);
        $sumOfWeightageLevel1 = round($sumOfLevel1Weightage[0]->Level1WeightageTotal, 2);

        $sumOfLevel2Weightage = DB::select("SELECT SUM(a.Weightage) as Level2WeightageTotal FROM mas_positiondepartmentratingcriteria a WHERE a.PositionDepartmentRatingId = ? AND a.ApplicableToLevel2 != 0 ", [$positionDepartmentRatingId]);
        $sumOfWeightageLevel2 = round($sumOfLevel2Weightage[0]->Level2WeightageTotal, 2);

	$totalLevel1Score = round($request->TotalLevel1Score, 2);
	
	if (!empty($appraiserLevel2Id)) {
            $totalLevel2Score = round($request->TotalLevel2Score, 2);
	}

        $totalNormalizedScore = 0.00;

        if (!empty($appraiserLevel1Id) && empty($appraiserLevel2Id)) {
            $level1NormalizedScore = round((($totalLevel1Score / $sumOfWeightageLevel1) * $weightageForLevel1), 2);
            $totalNormalizedScore = $level1NormalizedScore;
        }

        if (!empty($appraiserLevel2Id)) {
            $level1NormalizedScore = round((($totalLevel1Score / $sumOfWeightageLevel1) * $weightageForLevel1), 2);
            $level2NormalizedScore = round((($totalLevel2Score / $sumOfWeightageLevel2) * $weightageForLevel2), 2);
            $totalNormalizedScore = $level1NormalizedScore + $level2NormalizedScore;
	}

        DB::insert("INSERT INTO pms_historical (CIDNo, EmpId, PMSNumberId, PMSSubmissionId, PMSSCore, PMSResult, PMSRemarks) SELECT T2.CIDNo,T2.EmpId, ?,?, ?, D.Name, T1.FinalRemarks from pms_submission T1 join mas_employee T2 on T2.Id = T1.EmployeeId left join mas_pmsoutcome D on D.Id = T1.PMSOutcomeId where T1.Id = ?", [$pmsId, $submissionId, $totalNormalizedScore, $submissionId]);

        return redirect('auditemployeeindex')->with('successmessage', 'PMS Submitted For ' . $employee . (' EmpId: ' . $empId) . ' has been ' . 'saved.');
    }

}
