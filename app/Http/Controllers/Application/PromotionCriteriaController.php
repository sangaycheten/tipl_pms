<?php

namespace App\Http\Controllers\Application;

use App\PromotionCriteria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; //DB (query builder)

use App\Http\Controllers\Controller;

class PromotionCriteriaController extends Controller
{
    public function getIndex(Request $request)
    {
        $promotioncriteria = DB::select("SELECT p.Id, p.FromGradeStepId, p.ToGradeStepId, p.OutstandingCount, p.OutstandingAndGoodCount, p.RegularPromotionCount,
	    (SELECT g.Name FROM mas_gradestep g WHERE p.FromGradeStepId = g.Id ) AS FromGradeStepName, (SELECT g.Name FROM mas_gradestep g WHERE p.ToGradeStepId = g.Id ) AS ToGradeStepName 
	    FROM pms_promotioncriteria p JOIN mas_gradestep b ON b.Id = p.FromGradeStepId WHERE b.Status = 1 ");

        return view('application.promotioncriteriaindex', ['promotioncriteria' => $promotioncriteria]);
    }

    public function getForm($id = null)
    {
        $update = false;
        $promotioncriteria = [new PromotionCriteria()];

        $gradestep = DB::select("SELECT Id, GradeId, Name, PayScale FROM mas_gradestep ");

        if ((bool) $id) {
            $update = true;
            $promotioncriteria = PromotionCriteria::find($id);

            if (!(bool) $promotioncriteria) {
                abort(404);
            }
        }

        return view('application.promotioncriteriaform', ['update' => $update, 'gradestep' => $gradestep, 'promotioncriteria' => $promotioncriteria]);
    }

    public function postSave(Request $request)
    {
        //FORM VALIDATION
        $this->validate
        (
            $request,
            [
                'FromGradeStepId' => 'required',
                'ToGradeStepId' => 'required',
                'OutstandingCount' => 'required',
                'OutstandingAndGoodCount' => 'required',
                'RegularPromotionCount' => 'required',
            ],
            [
                'FromGradeStepId.required' => 'Please select grade step (from)',
                'ToGradeStepId.required' => 'Please select grade step (to)',
                'OutstandingCount' => 'Please enter outstanding count for promotion',
                'OutstandingAndGoodCount.required' => 'Please enter outstanding and good count for promotion',
                'RegularPromotionCount' => 'Please enter regular count for promotion',
            ]
        );
        //END

        $inputs = $request->all();
        $id = $request->input('Id');

        $inputs['FromGradeStepId'] = $fromgradestepId = $request->input('FromGradeStepId');
        $inputs['ToGradeStepId'] = $togradestepId = $request->input('ToGradeStepId');
        $inputs['OutstandingCount'] = $request->input('OutstandingCount');
        $inputs['OutstandingAndGoodCount'] = $request->input('OutstandingAndGoodCount');
        $inputs['RegularPromotionCount'] = $request->input('RegularPromotionCount');

        $save = true;
        if ((bool) $id) {
            $save = false;
            $updateObject = PromotionCriteria::find($id);
            $updateObject->fill($inputs);
            $updateObject->update();
        } else {
            $save = true;
            $promotionCriteriaExists = DB::select("SELECT COUNT(*) CriteriaExists FROM pms_promotioncriteria WHERE FromGradeStepId = ? AND ToGradeStepId = ? ", [$fromgradestepId, $togradestepId]);
            $countExists = $promotionCriteriaExists[0]->CriteriaExists;

            if ($countExists == 0) {
                PromotionCriteria::create($inputs);
            } else {
                return redirect('promotioncriteriainput')->with('errormessage', "Cannot be added since criteria already exists in Promotion Criteria.");
            }
        }

        $detailIds = [];
        if ((bool) $id) {
            $updateObject = PromotionCriteria::find($id);
            $updateObject->fill($inputs);
            $updateObject->update();
        }

        array_push($detailIds, $id);
        DB::table("pms_promotioncriteria")->where('Id', $id)->whereNotIn("Id", $detailIds)->delete();
        return redirect('promotioncriteriaindex')->with('successmessage', "Promotion criteria has been " . ($save ? "submitted" : "updated") . " .");
    }

    public function getDelete($id)
    {
        try {
            $this->saveAuditTrail('pms_promotioncriteria', $id, 1);
            PromotionCriteria::where('Id', $id)->delete();
        } catch (\Exception $e) {
            $this->saveError($e, false);
            return back()->with('errormessage', "Promotion criteria could not be deleted.");
        }

        return redirect('promotioncriteriaindex')->with('successmessage', "Promotion criteria has been deleted.");
    }

}
