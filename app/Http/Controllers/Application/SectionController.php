<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2019-01-01
 * Time: 12:58 PM
 */

namespace App\Http\Controllers\Application;

use App\Section;
use Illuminate\Http\Request;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB; //DB (query builder)
use Auth;

use App\Http\Controllers\Controller;

class SectionController extends Controller
{
    public function getIndex(Request $request)
    {
        $perPage = 10;
        $departmentId = $request->input('DepartmentId');
        $name = $request->input('Name');
        $status = $request->Status;

        $condition = "1=1";
        $parameters = [];

        if ((bool) $departmentId) {
            $condition .= " and T1.DepartmentId = ?";
            array_push($parameters, $departmentId);
        }
        if ((bool) $name) {
            $condition .= " and T1.Name like ?";
            array_push($parameters, "%$name%");
        }
        if ($status != '') {
            $condition .= " and coalesce(T1.Status,0) = ?";
            array_push($parameters, (int) $status);
        }

        $departments = $this->fetchActiveDepartments();
        $sections = DB::table('mas_section as T1')
            ->join('mas_department as T2', 'T2.Id', '=', 'T1.DepartmentId')
            ->whereRaw("$condition", $parameters)
            ->select('T1.Id', 'T1.Name as Section', 'T2.Name as Department', DB::raw("case when coalesce(T1.Status,0) = 1 then 'Active' else 'Inactive' end as Status"))
            ->orderBy('T1.Status', 'DESC')
            ->orderBy('T2.Name')
            ->orderBy('T1.Name')
            ->paginate($perPage);
        return view('application.sectionindex', ['sections' => $sections, 'departments' => $departments, 'perPage' => $perPage]);
    }

    public function getForm($id = null)
    {
        $section = [new Section()];
        $update = false;
        if ((bool) $id) {
            $update = true;
            $section = Section::find($id);
            if (!(bool) $section) {
                abort(404);
            }
        }

        $departments = $this->fetchActiveDepartments();
        return view('application.sectionform')->with('section', $section)->with('departments', $departments)->with('update', $update);
    }

    public function postSave(Request $request)
    {
        //FORM VALIDATION
        $this->validate(
            $request,
            [
                'Name' => 'required',
                'DepartmentId' => 'required'
            ],
            [
                'Name.required' => 'Please type a Section Name',
                'DepartmentId.required' => 'Please specifiy under which Department this Section is',
            ]
        );
        //END
        $inputs = request()->all();
        $save = false;
        DB::beginTransaction();
        try {
            if ((bool) $inputs['Id']) {
                $id = $inputs['Id'];
                $inputs['EditedBy'] = Auth::user()->Id;
                $inputs['updated_at'] = date('Y-m-d H:i:s');
                $object = Section::find($inputs['Id']);
                $object->fill($inputs);
                $changes = $object->getDirty();
                if (!(count($changes) == 2 && array_key_exists('updated_at', $changes) && array_key_exists('EditedBy', $changes)) && !(count($changes) == 1 && array_key_exists('updated_at', $changes))) {
                    unset($changes['EditedBy']);
                    unset($changes['updated_at']);
                    $changes['Id'] = $id;
                    $recordJson = json_encode([$changes]);
                    DB::insert("INSERT into sys_databasechangehistory (Id, TableName, EmployeeId, Deleted, Changes) VALUES (UUID(),?,?,?,?)", ['mas_section', Auth::user()->Id, 0, $recordJson]);
                }
                $object->update();
            } else {
                $saveAudit = true;
                $inputs['CreatedBy'] = Auth::user()->Id;
                unset($inputs['Id']);
                $save = true;
                $savedRecord = Section::create($inputs);
                $id = $savedRecord->Id;
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $this->saveError($e, false);
            return back()->with('errormessage', $e->getMessage());
        }

        DB::commit();
        if (isset($saveAudit) && $saveAudit) {
            $this->saveAuditTrail('mas_section', $id);
        }

        return redirect('sectionindex')->with('successmessage', 'Record has been ' . ($save ? 'saved' : 'updated') . '!');
    }

    public function getDelete($id)
    {
        try {
            $this->saveAuditTrail('mas_section', $id, 1);
            Section::where('Id', $id)->delete();
        } catch (\Exception $e) {
            $this->saveError($e, false);
            return back()->with('errormessage', "Section could not be deleted because there are Employees or other records related to this section.");
        }
        return back()->with('successmessage', 'Record has been deleted');
    }
    
}