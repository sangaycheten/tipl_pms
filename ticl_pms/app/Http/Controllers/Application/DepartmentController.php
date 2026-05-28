<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2019-01-01
 * Time: 12:58 PM
 */

namespace App\Http\Controllers\Application;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; //DB (query builder)
use Illuminate\Support\Facades\Input;
use Auth;
use App\Department;

use App\Http\Controllers\Controller;

class DepartmentController extends Controller
{
    public function getIndex()
    {
        $departments = DB::select("SELECT Id, ShortName, Name,case when coalesce(Status,0) = 1 then 'Active' else 'Inactive' end as Status from mas_department order by Status,Name");
        return view('application.departmentindex', ['departments' => $departments]);
    }

    public function getForm($id = null)
    {
        $department = [new Department()];
        $update = false;

        if ((bool) $id) {
            $update = true;
            $department = Department::find($id);
            if (!(bool) $department) {
                abort(404);
            }
        }

        return view('application.departmentform')->with('department', $department)->with('update', $update);
    }

    public function postSave(Request $request)
    {
        //FORM VALIDATION
        $this->validate(
            $request,
            [
                'Name' => 'required',
                'ShortName' => 'required',
            ],
            [
                'ShortName.required' => 'Please type a Short Name',
                'Name.required' => 'Please type a Department Name'
            ]
        );
        //END

        $inputs = Input::all();
        $save = false;
        DB::beginTransaction();
        try {
            if ((bool) $inputs['Id']) {
                $id = $inputs['Id'];
                $inputs['EditedBy'] = Auth::user()->Id;
                $inputs['updated_at'] = date('Y-m-d H:i:s');
                $object = Department::find($inputs['Id']);
                $object->fill($inputs);
                $changes = $object->getDirty();
                if (!(count($changes) == 2 && array_key_exists('updated_at', $changes) && array_key_exists('EditedBy', $changes)) && !(count($changes) == 1 && array_key_exists('updated_at', $changes))) {
                    unset($changes['EditedBy']);
                    unset($changes['updated_at']);
                    $changes['Id'] = $id;
                    $recordJson = json_encode([$changes]);
                    DB::insert("INSERT into sys_databasechangehistory (Id, TableName, EmployeeId, Deleted, Changes) VALUES (UUID(),?,?,?,?)", ['mas_department', Auth::user()->Id, 0, $recordJson]);
                }
                $object->update();
            } else {
                $saveAudit = true;
                $inputs['CreatedBy'] = Auth::user()->Id;
                unset($inputs['Id']);
                $save = true;
                $savedRecord = Department::create($inputs);
                $id = $savedRecord->Id;
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $this->saveError($e, false);
            return back()->with('errormessage', $e->getMessage());
        }

        DB::commit();
        if (isset($saveAudit) && $saveAudit) {
            $this->saveAuditTrail('mas_department', $id);
        }

        return redirect('departmentindex')->with('successmessage', 'Record has been ' . ($save ? 'saved' : 'updated') . '!');
    }

    public function getDelete($id)
    {
        try {
            $this->saveAuditTrail('mas_department', $id, 1);
            Department::where('Id', $id)->delete();
        } catch (\Exception $e) {
            $this->saveError($e, false);
            return back()->with('errormessage', "Department could not be deleted because there are Employees or other records related to this department.");
        }

        return back()->with('successmessage', 'Record has been deleted successfully');
    }
    
}