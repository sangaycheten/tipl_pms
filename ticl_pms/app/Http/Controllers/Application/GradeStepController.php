<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2019-01-01
 * Time: 12:58 PM
 */

namespace App\Http\Controllers\Application;

use App\GradeStep;
use Illuminate\Http\Request;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesResources;
use Illuminate\Support\Facades\DB; //DB (query builder)
use Illuminate\Support\Facades\Input;
use Auth;

use App\Http\Controllers\Controller;

class GradeStepController extends Controller
{
    public function getIndex(Request $request)
    {
        $perPage = 12;
        $name = $request->input('Name');
        $gradeId = $request->input('GradeId');
        $status = $request->Status;

        $condition = "1=1";
        $parameters = [];

        if ((bool) $name) {
            $condition .= " and T1.Name like ?";
            array_push($parameters, "%$name%");
        }
        if ((bool) $gradeId) {
            $condition .= " and T1.GradeId = ?";
            array_push($parameters, $gradeId);
        }
        if ($status != '') {
            $condition .= " and coalesce(T1.Status,0) = ?";
            array_push($parameters, (int) $status);
        }

        $gradesteps = DB::table('mas_gradestep as T1')
            ->whereRaw("$condition", $parameters)
            ->select('T1.Id', 'T1.PayScale', 'T1.Name as GradeStep', DB::raw("case when coalesce(T1.Status,0) = 1 then 'Active' else 'Inactive' end as Status"))
            ->orderBy('T1.Status', 'DESC')
            ->orderBy(DB::raw('SUBSTR(T1.Name,1,2)'))
            ->orderBy(DB::raw("CAST(TRIM(SUBSTR(T1.Name,LENGTH(T1.Name)-1,2)) AS INT)"))
            ->paginate($perPage);
        $gradesteps->setPath("gradestepindex");
        $grades = DB::select("SELECT Id, Name from mas_grade where coalesce(IsManagerialRole,0) = 0 and Id <> 9 order by Name");
        return view('application.gradestepindex', ['gradesteps' => $gradesteps, 'perPage' => $perPage, 'grades' => $grades]);
    }

    public function getForm($id = null)
    {
        $gradestep = [new GradeStep()];
        $grades = DB::select("SELECT Id, Name from mas_grade where coalesce(IsManagerialRole,0) = 0 and Id <> 9 order by Name");
        $update = false;
        if ((bool) $id) {
            $update = true;
            $gradestep = GradeStep::find($id);
            if (!(bool) $gradestep) {
                abort(404);
            }
        }

        return view('application.gradestepform')->with('grades', $grades)->with('gradestep', $gradestep)->with('update', $update);
    }

    public function postSave(Request $request)
    {
        //FORM VALIDATION
        $this->validate(
            $request,
            [
                'Name' => 'required',
            ],
            [
                'Name.required' => 'Please type a Name',
            ]
        );
        //END
        $inputs = Input::all();
        if (!(bool) $inputs['PayScale']) {
            $inputs['PayScale'] = NULL;
        }
        $save = false;
        DB::beginTransaction();
        try {
            if ((bool) $inputs['Id']) {
                $id = $inputs['Id'];
                $inputs['EditedBy'] = Auth::user()->Id;
                $inputs['updated_at'] = date('Y-m-d H:i:s');
                $object = GradeStep::find($inputs['Id']);
                $object->fill($inputs);
                $changes = $object->getDirty();
                if (!(count($changes) == 2 && array_key_exists('updated_at', $changes) && array_key_exists('EditedBy', $changes)) && !(count($changes) == 1 && array_key_exists('updated_at', $changes))) {
                    unset($changes['EditedBy']);
                    unset($changes['updated_at']);
                    $changes['Id'] = $id;
                    $recordJson = json_encode([$changes]);
                    DB::insert("INSERT into sys_databasechangehistory (Id, TableName, EmployeeId, Deleted, Changes) VALUES (UUID(),?,?,?,?)", ['mas_gradestep', Auth::user()->Id, 0, $recordJson]);
                }
                $object->update();
            } else {
                $saveAudit = true;
                $inputs['CreatedBy'] = Auth::user()->Id;
                unset($inputs['Id']);
                $save = true;
                $savedRecord = GradeStep::create($inputs);
                $id = $savedRecord->Id;
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $this->saveError($e, false);
            return back()->with('errormessage', $e->getMessage());
        }

        if ((bool) $inputs['PayScale']) {
            $this->updateSalaryDetailsForGradeStep($id);
        }
        DB::commit();
        if (isset($saveAudit) && $saveAudit) {
            $this->saveAuditTrail('mas_gradestep', $id);
        }

        return redirect('gradestepindex')->with('successmessage', 'Record has been ' . ($save ? 'saved' : 'updated') . '!');
    }

    public function getDelete($id)
    {
        try {
            $this->saveAuditTrail('mas_gradestep', $id, 1);
            GradeStep::where('Id', $id)->delete();
        } catch (\Exception $e) {
            $this->saveError($e, false);
            return back()->with('errormessage', "GradeStep could not be deleted because there are Employees or other records related to this gradestep.");
        }
        return back()->with('successmessage', 'Record has been deleted');
    }

    public function populate()
    {
        for ($i = 1; $i <= 12; $i++):
            $text = "T2 Step $i";
            $inputs['Name'] = $text;
            $inputs['CreatedBy'] = Auth::user()->Id;
            GradeStep::create($inputs);
        endfor;
        for ($i = 1; $i <= 12; $i++):
            $text = "T1 Step $i";
            $inputs['Name'] = $text;
            $inputs['CreatedBy'] = Auth::user()->Id;
            GradeStep::create($inputs);
        endfor;
        for ($i = 1; $i <= 9; $i++):
            $text = "P2 Step $i";
            $inputs['Name'] = $text;
            $inputs['CreatedBy'] = Auth::user()->Id;
            GradeStep::create($inputs);
        endfor;
        for ($i = 1; $i <= 12; $i++):
            $text = "P1 Step $i";
            $inputs['Name'] = $text;
            $inputs['CreatedBy'] = Auth::user()->Id;
            GradeStep::create($inputs);
        endfor;
        for ($i = 1; $i <= 3; $i++):
            $text = "Exe Step $i";
            $inputs['Name'] = $text;
            $inputs['CreatedBy'] = Auth::user()->Id;
            GradeStep::create($inputs);
        endfor;
    }

    public function updateSalaryDetailsForGradeStep($recordId = null)
    {
        if ((bool) $recordId) {
            $gradeSteps = DB::select("SELECT Id, PayScale from mas_gradestep where PayScale is not null and Id = ?", [$recordId]);
        } else {
            $gradeSteps = DB::select("SELECT Id, PayScale from mas_gradestep where PayScale is not null");
        }

        foreach ($gradeSteps as $gradeStep):
            $id = $gradeStep->Id;
            $payScale = $gradeStep->PayScale;
            if ((bool) $payScale) {
                $indexOfFirstHyphen = strpos($payScale, '-');
                $firstPay = substr($payScale, 0, $indexOfFirstHyphen);
                $firstPay = trim($firstPay);
                $afterFirstHyphen = substr($payScale, ($indexOfFirstHyphen + 1), strlen($payScale));
                $afterFirstHyphen = trim($afterFirstHyphen);
                $indexOfSecondHyphen = strpos($afterFirstHyphen, '-');
                $tillSecondHyphen = substr($afterFirstHyphen, 0, ($indexOfSecondHyphen));
                $increment = trim($tillSecondHyphen);
                $lastPay = substr($afterFirstHyphen, ($indexOfSecondHyphen + 1), strlen($afterFirstHyphen));
                $lastPay = trim($lastPay);
                DB::table('mas_gradestep')->where('Id', $id)->update(['StartingSalary' => $firstPay, 'Increment' => $increment, 'EndingSalary' => $lastPay]);
            }
        endforeach;
        return true;
    }
    
}