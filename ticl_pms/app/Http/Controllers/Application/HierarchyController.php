<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2019-01-01
 * Time: 12:58 PM
 */

namespace App\Http\Controllers\Application;

use Illuminate\Http\Request;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesResources;
use Illuminate\Support\Facades\DB; //DB (query builder)
use Illuminate\Support\Facades\Input;
use Auth;
use App\Hierarchy;

use App\Http\Controllers\Controller;

class HierarchyController extends Controller
{
    public function getIndex(Request $request)
    {
        $perPage = 15;
        $departmentId = $request->input('DepartmentId');
        $designationLocation = $request->input('DesignationId');
        $name = $request->input('Name');
        $empId = $request->input('EmpId');

        $condition = "coalesce(T1.Status,0) = 1";
        $parameters = [];

        if ((bool) $departmentId) {
            $condition .= " and T1.DepartmentId = ?";
            array_push($parameters, $departmentId);
        }
        if ((bool) $designationLocation) {
            $condition .= " and D.Id = ?";
            array_push($parameters, $designationLocation);
        }
        if ((bool) $name) {
            $condition .= " and T1.Name like ?";
            array_push($parameters, "%$name%");
        }
        if ((bool) $empId) {
            $condition .= " and T1.EmpId = ?";
            array_push($parameters, "$empId");
        }

        $departments = $this->fetchActiveDepartments();
        $designationLocations = DB::table('mas_employee as T1')
            ->join('mas_department as T2', 'T2.Id', '=', 'T1.DepartmentId')
            ->join('mas_designation as T3', 'T3.Id', '=', 'T1.DesignationId')
            ->orderBy('T3.Name')
            ->whereNotNull('T1.DesignationId')
            ->distinct()
            ->selectRaw("T3.Id,T3.Name, GROUP_CONCAT(distinct concat('\"',T1.DepartmentId,'\"') SEPARATOR ',') as DepartmentIds")
            ->groupBy('T3.Id')->get();
        $hierarchies = DB::table('mas_employee as T1')
            ->join('mas_department as A', 'A.Id', '=', 'T1.DepartmentId') //CHANGE TO SECTION LATER
            ->join('mas_designation as D', 'D.Id', '=', 'T1.DesignationId')
            ->selectRaw("T1.Id,T1.CIDNo,T1.EmpId,T1.Name,D.Name as Designation,A.ShortName as Department,case when coalesce(T1.Status,0) = 1 then 'Active' else 'In-Active' end as EmployeeStatus,(select GROUP_CONCAT(concat(M.Name,'<br/>(',O.Name,', ',N.ShortName,')') SEPARATOR '<br/><br/>') from mas_hierarchy L join mas_employee M on M.Id = L.ReportingLevel1EmployeeId join mas_department N on N.Id = M.DepartmentId join mas_designation O on O.Id = M.DesignationId where L.EmployeeId = T1.Id) as Level1,(select GROUP_CONCAT(concat(M.Name,'<br/>(',O.Name,', ',N.ShortName,')') SEPARATOR '<br/><br/>') from mas_hierarchy L join mas_employee M on M.Id = L.ReportingLevel2EmployeeId join mas_department N on N.Id = M.DepartmentId join mas_designation O on O.Id = M.DesignationId where L.EmployeeId = T1.Id) as Level2")
            ->orderBy('EmployeeStatus')
            ->orderBy('A.Name')
            ->orderBy('T1.Name')
            ->whereRaw("$condition", $parameters)
            ->where('T1.RoleId', '<>', 1)
            ->paginate($perPage);
        $hierarchies->setPath("hierarchyindex");
        return view('application.hierarchyindex', ['hierarchies' => $hierarchies, 'designationLocations' => $designationLocations, 'departments' => $departments, 'perPage' => $perPage]);
    }

    public function getForm($id)
    {
        $update = false;
        $employees = $this->getAllEmployees();
        $hierarchy = DB::select("SELECT T1.Id, T1.Name as Employee,T1.EmpId, A.ShortName as Department, O.Name as Designation, T2.ReportingLevel1EmployeeId, T2.ReportingLevel2EmployeeId from mas_employee T1 join mas_designation O on O.Id = T1.DesignationId join mas_department as A on A.Id = T1.DepartmentId left join mas_hierarchy T2 on T2.EmployeeId = T1.Id and T2.DisplayOrder = 1 where T1.Id = ?", [$id]);
        $hierarchy2 = DB::select("SELECT T1.Id, T1.Name as Employee,T1.EmpId, A.ShortName as Department, O.Name as Designation, T2.ReportingLevel1EmployeeId, T2.ReportingLevel2EmployeeId from mas_employee T1 join mas_designation O on O.Id = T1.DesignationId join mas_department as A on A.Id = T1.DepartmentId left join mas_hierarchy T2 on T2.EmployeeId = T1.Id and T2.DisplayOrder = 2 where T1.Id = ?", [$id]);
        $hierarchy3 = DB::select("SELECT T1.Id, T1.Name as Employee,T1.EmpId, A.ShortName as Department, O.Name as Designation, T2.ReportingLevel1EmployeeId, T2.ReportingLevel2EmployeeId from mas_employee T1 join mas_designation O on O.Id = T1.DesignationId join mas_department as A on A.Id = T1.DepartmentId left join mas_hierarchy T2 on T2.EmployeeId = T1.Id and T2.DisplayOrder = 3 where T1.Id = ?", [$id]);
	$hierarchy4 = DB::select("SELECT T1.Id, T1.Name as Employee,T1.EmpId, A.ShortName as Department, O.Name as Designation, T2.ReportingLevel1EmployeeId, T2.ReportingLevel2EmployeeId from mas_employee T1 join mas_designation O on O.Id = T1.DesignationId join mas_department as A on A.Id = T1.DepartmentId left join mas_hierarchy T2 on T2.EmployeeId = T1.Id and T2.DisplayOrder = 4 where T1.Id = ?", [$id]);
	if (!(bool) $hierarchy) {
            abort(404);
        }
        $hasHierarchy = $hierarchy[0]->ReportingLevel1EmployeeId;
        if ((bool) $hasHierarchy) {
            $update = true;
        }

        return view('application.hierarchyform')->with('update', $update)->with('hierarchy', $hierarchy)->with('hierarchy2', $hierarchy2)->with('hierarchy3', $hierarchy3)->with('hierarchy4', $hierarchy4)->with('employees', $employees);
    }

    public function postSave(Request $request)
    {
        $saveArray = [];
        $idArray = [];
        $reportingLevel1EmployeeIds = $request->input('ReportingLevel1EmployeeId');
        $reportingLevel2EmployeeIds = $request->input('ReportingLevel2EmployeeId');
        //FORM VALIDATION
        $this->validate
        (
            $request,
            [
                'EmployeeId' => 'required',
                'ReportingLevel1EmployeeId' => 'required',
            ],
            [
                'EmployeeId.required' => 'Employee field is required',
                'ReportingLevel1EmployeeId' => 'Please select Reports To',
            ]
        );
        //END
        $inputs = Input::except('ReportingLevel1EmployeeId', 'ReportingLevel2EmployeeId');
        $save = false;
        DB::beginTransaction();
        try {
            DB::table('mas_hierarchy')->where('EmployeeId', $inputs['EmployeeId'])->delete();
            $inputs['Id'] = UUID();
            if (!(bool) $reportingLevel2EmployeeIds[0]) {
                $inputs['ReportingLevel2EmployeeId'] = NULL;
            } else {
                $inputs['ReportingLevel2EmployeeId'] = $reportingLevel2EmployeeIds[0];
            }
            $inputs['ReportingLevel1EmployeeId'] = $reportingLevel1EmployeeIds[0];
            $inputs['CreatedBy'] = Auth::user()->Id;
            $save = true;
            Hierarchy::create($inputs);
            array_push($idArray, $inputs['Id']);
            foreach ($reportingLevel1EmployeeIds as $key => $value):
                if ($key > 0 && (bool) $value) {
                    $saveArray['Id'] = UUID();
                    $saveArray['EmployeeId'] = $inputs['EmployeeId'];
                    $saveArray['ReportingLevel1EmployeeId'] = $value;
                    $saveArray['ReportingLevel2EmployeeId'] = NULL;
                    $saveArray['DisplayOrder'] = $key + 1;
                    $saveArray['CreatedBy'] = Auth::user()->Id;
                    Hierarchy::create($saveArray);
                    array_push($idArray, $saveArray['Id']);
                }
            endforeach;
            foreach ($reportingLevel2EmployeeIds as $key => $value):
                if ($key > 0 && (bool) $value) {
                    $saveArray['Id'] = UUID();
                    $saveArray['EmployeeId'] = $inputs['EmployeeId'];
                    $saveArray['ReportingLevel2EmployeeId'] = $value;
                    $saveArray['ReportingLevel1EmployeeId'] = NULL;
                    $saveArray['DisplayOrder'] = $key + 1;
                    $saveArray['CreatedBy'] = Auth::user()->Id;
                    Hierarchy::create($saveArray);
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
            $this->saveAuditTrail('mas_hierarchy', $id);
        endforeach;
        $redirect = Input::get('redirect');
        return redirect($redirect)->with('successmessage', 'Record has been ' . ($save ? 'saved' : 'updated') . '!');
    }
    
}
