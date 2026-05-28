<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2024-01-29
 * Time: 02:05 PM
 */

namespace App\Http\Controllers\Application;

use Illuminate\Http\Request;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB; //DB (query builder)
use Auth;
use App\RegionsCriteria;

use App\Http\Controllers\Controller;

use Illuminate\Routing\Redirector;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\Foundation\Application;


class RegionsCriteriaController extends Controller
{
    public function getIndex(Request $request)
    {
        $perPage = 15;
        $departmentId = $request->input('DepartmentId');
        $employeeName = $request->input('EmployeeName');
        $employeeId = $request->input('EmployeeId');

        $parameters = [];
        $condition = " T1.RoleId <> 1 ";

        if ((bool) $departmentId) {
            $condition .= " and T1.DepartmentId = ?";
            array_push($parameters, $departmentId);
        }
        if ((bool) $employeeName) {
            $condition .= " and T1.Name like ?";
            array_push($parameters, "%$employeeName%");
        }
        if ((bool) $employeeId) {
            $condition .= " and T1.EmpId = ?";
            array_push($parameters, "$employeeId");
        }

        $departments = $this->fetchActiveDepartments();

        $regionsemployees = DB::table('mas_employee as T1')
            ->join('mas_department as T2', 'T2.Id', '=', 'T1.DepartmentId')
            ->join('mas_section as T3', 'T3.DepartmentId', '=', 'T2.Id')
            ->join('mas_designation as T4', 'T4.Id', '=', 'T1.DesignationId')
            ->join('mas_pmsregions_criteria as T5', 'T5.EmployeeId', '=', 'T1.Id')
            ->selectRaw("T5.Id,T1.Id as EmployeeId,T1.EmpId,T1.Name as EmployeeName,T4.Name as Designation,T2.ShortName as Department,T3.Name AS Section, T1.Status, T5.EmployeeStatus,
                (SELECT GROUP_CONCAT(concat(M.Name,'<br/>(',O.Name,', ',N.ShortName,')') SEPARATOR '<br/><br/>') FROM mas_hierarchy L JOIN mas_employee M ON M.Id = L.ReportingLevel1EmployeeId JOIN mas_department N on N.Id = M.DepartmentId JOIN mas_designation O ON O.Id = M.DesignationId WHERE L.EmployeeId = T1.Id) AS Level1,
                (SELECT GROUP_CONCAT(concat(M.Name,'<br/>(',O.Name,', ',N.ShortName,')') SEPARATOR '<br/><br/>') FROM mas_hierarchy L JOIN mas_employee M ON M.Id = L.ReportingLevel2EmployeeId JOIN mas_department N on N.Id = M.DepartmentId JOIN mas_designation O ON O.Id = M.DesignationId WHERE L.EmployeeId = T1.Id) AS Level2")
            ->whereRaw("$condition", $parameters)
	    ->groupBy('T1.EmpId')
            ->where('T1.RoleId', '<>', 1)
            // ->where('T1.Status', 1)
	    ->paginate($perPage);

	for ($i = 0; $i < count($regionsemployees); $i++) {
            $userId = $regionsemployees[$i]->EmployeeId;
            $empId = $regionsemployees[$i]->EmpId;
            $employeeStatus = $regionsemployees[$i]->Status;
            DB::update("UPDATE mas_pmsregions_criteria A SET A.EmpId = ?, A.EmployeeStatus = ? WHERE A.EmployeeId = ? ", [$empId, $employeeStatus, $userId]);
        }

	return view('application.regioncriteriaindex', ['regionsemployees' => $regionsemployees, 'departments' => $departments, 'perPage' => $perPage]);
    }

    public function getForm($id = null)
    {
        $update = false;
        $regionemployees = new RegionsCriteria();
        $level1employees = $this->getLevel1AppraiserEmployees();
        $employeedetail = [];
	$employees = $this->getAllEmployees();

	$regionAndMaps = [];
        $regionMarketingMaps = [];

        if ((bool) $id) {
            $update = true;
            $regionemployees = RegionsCriteria::where("EmployeeId", $id)->first();
            $employeedetail = $this->getEmployeeDetails($id);

            if (!(bool) $regionemployees) {
                abort(404);
	    }

	    $regionAndMaps = DB::table('mas_pmsregions_criteria')->where('EmployeeId', $id)->pluck("Level1ANDAppraiserId");
            $regionMarketingMaps = DB::table('mas_pmsregions_criteria')->where('EmployeeId', $id)->pluck("Level1MarketingAppraiserId");
        }

        return view('application.regioncriteriaform', ['update' => $update, 'regionemployees' => $regionemployees, 'level1employees' => $level1employees, 'employeedetail' => $employeedetail, 'employees' => $employees, 'regionAndMaps' => $regionAndMaps, 'regionMarketingMaps' => $regionMarketingMaps]);
    }

    public function postSave(Request $request)
    {
        //FORM VALIDATION
        $this->validate
        (
            $request,
            [
                'EmployeeId' => 'required',
                'Level1ANDWeightage' => 'required',
                'Level1ANDAppraiserId' => 'required',
                'Level1MarketingWeightage' => 'required',
                'Level1MarketingAppraiserId' => 'required',
            ],
            [
                'EmployeeId.required' => 'Please select Employee',
                'Level1ANDWeightage.required' => 'Level 1 AND Weightage field is required',
                'Level1ANDAppraiserId' => 'Please select Level 1 AND Appraiser Employee',
                'Level1MarketingWeightage.required' => 'Level 1 Marketing Weightage field is required',
                'Level1MarketingAppraiserId' => 'Please select Level 1 Marketing Appraiser Employee',
            ]
        );
        //END

        $saveArray = [];
        $idArray = [];
        $employeeId = $request->input('EmployeeId');
        $andLevel1EmployeeIds = $request->input('Level1ANDAppraiserId');
        $marketingLevel1EmployeeIds = $request->input('Level1MarketingAppraiserId');

        $inputs = $request->except('Level1ANDAppraiserId', 'Level1MarketingAppraiserId');
        $save = false;
        DB::beginTransaction();
        try {
            DB::table('mas_pmsregions_criteria')->where('EmployeeId', $inputs['EmployeeId'])->delete();
            $inputs['Id'] = UUID();
            if ((bool) $marketingLevel1EmployeeIds[0]) {
                $inputs['Level1MarketingAppraiserId'] = $marketingLevel1EmployeeIds[0];
            } else {
                $inputs['Level1MarketingAppraiserId'] = NULL;
            }

            $inputs['Level1ANDAppraiserId'] = $andLevel1EmployeeIds[0];
            $inputs['Level1ANDWeightage'] = 45;
            $inputs['Level1MarketingWeightage'] = 25;
            $inputs['Level2Weightage'] = 30;
            $save = true;
            RegionsCriteria::create($inputs);

            array_push($idArray, $inputs['Id']);
            foreach ($andLevel1EmployeeIds as $key => $value):
                if ($key > 0 && (bool) $value) {
                    $saveArray['Id'] = UUID();
                    $saveArray['EmployeeId'] = $inputs['EmployeeId'];
                    $saveArray['Level1ANDWeightage'] = 45;
                    $saveArray['Level1ANDAppraiserId'] = $value;
                    $saveArray['Level1MarketingWeightage'] = 25;
                    $saveArray['Level1MarketingAppraiserId'] = NULL;
                    $saveArray['Level2Weightage'] = 30;
                    $saveArray['DisplayOrder'] = $key + 1;
                    RegionsCriteria::create($saveArray);
                    array_push($idArray, $saveArray['Id']);
                }
            endforeach;

            foreach ($marketingLevel1EmployeeIds as $key => $value):
                if ($key > 0 && (bool) $value) {
                    $saveArray['Id'] = UUID();
                    $saveArray['EmployeeId'] = $inputs['EmployeeId'];
                    $saveArray['Level1ANDWeightage'] = 45;
                    $saveArray['Level1ANDAppraiserId'] = NULL;
                    $saveArray['Level1MarketingWeightage'] = 25;
                    $saveArray['Level1MarketingAppraiserId'] = $value;
                    $saveArray['Level2Weightage'] = 30;
                    $saveArray['DisplayOrder'] = $key + 1;
                    RegionsCriteria::create($saveArray);
                    array_push($idArray, $saveArray['Id']);
                }
            endforeach;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->saveError($e, false);
            return back()->with('errormessage', $e->getMessage());
        }

        DB::commit();

        foreach ($idArray as $id):
            $this->saveAuditTrail('mas_pmsregions_criteria', $id);
        endforeach;

        $employeedetail = $this->getEmployeeDetails($employeeId);
        $employeename = $employeedetail[0]->EmployeeName;

        return redirect('regioncriteriaindex')->with('successmessage', "Region criteria for $employeename has been " . ($save ? "submitted" : "updated") . " .");
    }

    public function getDelete($id)
    {
        $employeeDetails = DB::select('SELECT e.Name AS EmployeeName FROM mas_employee e JOIN mas_pmsregions_criteria r ON r.EmployeeId = e.Id WHERE r.Id = ? ', [$id]);
        $employeename = $employeeDetails[0]->EmployeeName;

        try {
            $this->saveAuditTrail('mas_pmsregions_criteria', $id, 1);
            RegionsCriteria::where('Id', $id)->delete();
        } catch (\Exception $e) {
            $this->saveError($e, false);
            return back()->with('errormessage', "Region criteria for $employeename could not be deleted.");
        }

        return redirect('regioncriteriaindex')->with('successmessage', "Region criteria for $employeename has been deleted.");
    }

}
