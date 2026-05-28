<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CorrectMergerScores extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'correct-merger-scores';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Correct Merger Employees' Scores";

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $mergerCriteria = DB::table("mas_pmsregions_criteria as T1")
            ->join("mas_employee as T2", "T2.Id", '=', 'T1.EmployeeId')
            ->get(["T2.EmpId", 'EmployeeId', 'Level1ANDWeightage', 'Level1ANDAppraiserId', 'Level1MarketingWeightage', 'Level1MarketingAppraiserId', 'Level2Weightage']);
        foreach ($mergerCriteria as $mergerSingle) {
            $employeeId = $mergerSingle->EmployeeId;
            $empId = $mergerSingle->EmpId;
            $submissionId = DB::table("pms_submission")->whereRaw("DATE(SubmissionTime) >= '2023-07-01' and EmployeeId = ?", [$employeeId])->pluck("Id");
            $submissionId = $submissionId[0];
            $criteria = DB::table("pms_submissiondetail as T1")
                ->whereRaw("T1.SubmissionId = ?", [$submissionId])
                ->get(["T1.Weightage", "T1.Id"]);
            $level1MarketingWeightage = (float)$mergerSingle->Level1MarketingWeightage;
            $level1ANDWeightage = (float)$mergerSingle->Level1ANDWeightage;
            $level1ANDAppraiserId = $mergerSingle->Level1ANDAppraiserId;
            $level1MarketingAppraiserId = $mergerSingle->Level1MarketingAppraiserId;
            foreach ($criteria as $singleCriteria):
                $criteriaId = $singleCriteria->Id;
                $criteriaWeightage = (float)$singleCriteria->Weightage;
                $appraiserScoreAND = DB::table("pms_submissionmultipledetail as A")
                    ->join("pms_submissionmultiple as B", "B.Id", "=", "A.SubmissionMultipleId")
                    ->whereRaw("B.AppraisedByEmployeeId = ? and A.SubmissionDetailId = ?", [$level1ANDAppraiserId, $criteriaId])->pluck("A.Score");
                $appraiserScoreAND = (float)$appraiserScoreAND[0];
                $weightedScoreAND = $appraiserScoreAND / $criteriaWeightage * ($level1ANDWeightage / ($level1ANDWeightage + $level1MarketingWeightage) * $criteriaWeightage);
                $appraiserScoreMarketing = DB::table("pms_submissionmultipledetail as A")
                    ->join("pms_submissionmultiple as B", "B.Id", "=", "A.SubmissionMultipleId")
                    ->whereRaw("B.AppraisedByEmployeeId = ? and A.SubmissionDetailId = ?", [$level1MarketingAppraiserId, $criteriaId])->pluck("A.Score");
                $appraiserScoreMarketing = (float)$appraiserScoreMarketing[0];
                $weightedScoreMarketing = $appraiserScoreMarketing / $criteriaWeightage * ($level1MarketingWeightage / ($level1ANDWeightage + $level1MarketingWeightage) * $criteriaWeightage);
                $weightedScoreMarketing = (float)$weightedScoreMarketing;
                $finalWeightedScore = $weightedScoreAND + $weightedScoreMarketing;
                DB::update("UPDATE pms_submissiondetail T1 set T1.Level1Rating = ? where T1.Id = ?", [round($finalWeightedScore, 2), $criteriaId]);
            endforeach;
            $currentPMSSubmissionDate = "2023-07-02";
            DB::update("UPDATE pms_submission T1 set T1.PMSOutcomeId = 1 where T1.Id = ?", [$submissionId]);
            $pmsNumber = DB::select("select T1.Id,T1.PMSNumber, T1.EvaluationMeetingDate from sys_pmsnumber T1 where T1.StartDate <= ? order by StartDate DESC limit 1", [$currentPMSSubmissionDate]);
            $pmsId = $pmsNumber[0]->Id;

            $finalScore = $this->getFinalScore($submissionId);

            DB::delete("DELETE FROM pms_historical where PMSNumberId = ? and EmpId = ?", [$pmsId, $empId]);
            DB::table('pms_submission')->where('Id', $submissionId)->update(['PMSOutcomeId' => CONST_PMSOUTCOME_NOACTION]);
            DB::insert("INSERT INTO pms_historical (CIDNo,EmpId,PMSNumberId,PMSSubmissionId,PMSSCore,PMSResult,PMSRemarks) SELECT T2.CIDNo,T2.EmpId, ?,?, ?, D.Name, T1.FinalRemarks from pms_submission T1 join mas_employee T2 on T2.Id = T1.EmployeeId left join mas_pmsoutcome D on D.Id = T1.PMSOutcomeId where T1.Id = ?", [$pmsId, $submissionId, $finalScore, $submissionId]);
        }
    }
    public function getFinalScore($id)
    {
         $application = DB::select("select T1.Id,T1.NewPayScale,T1.FilePath,T1.File2Path,T1.File3Path,T1.File4Path,T1.NewDesignationId,T1.NewGradeId,T1.NewLocation,T1.NewBasicPay,T1.NewGradeStepId,T1.NewSupervisorId,coalesce(T1.PMSOutcomeId,T1.SavedPMSOutcomeId) as PMSOutcomeId, T5.HasBasicPayChange, T5.HasDesignationAndLocationChange, T5.HasPayChange, T5.HasPositionChange, T1.FinalRemarks, T1.OutcomeDateTime,T1.EmployeeId,T1.WeightageForLevel1, T1.Level2CriteriaType,T1.WeightageForLevel2, A.Name as Level1Employee, B.Name as Level2Employee from viewpmssubmissionwithlaststatus T1 join (mas_hierarchy T2 join (mas_employee T3 join mas_position
A on A.Id = T3.PositionId) on T2.ReportingLevel1EmployeeId = T3.Id left join (mas_employee T4 join mas_position B on B.Id = T4.PositionId) on T4.Id = T2.ReportingLevel2EmployeeId) on T2.EmployeeId = T1.EmployeeId left join mas_pmsoutcome T5 on T5.Id = coalesce(T1.PMSOutcomeId,T1.SavedPMSOutcomeId) where T1.Id = ? and T1.LastStatusId = ?", [$id, CONST_PMSSTATUS_APPROVED]);
         if (count($application) == 0) {
             abort(404);
         }
         $applicationDetails = DB::select("select T2.AssessmentArea, T2.ApplicableToLevel2,T2.Weightage, T2.SelfRating, T2.Level1Rating, T2.Level2Rating from viewpmssubmissionwithlaststatus T1 join pms_submissiondetail T2 on T2.SubmissionId = T1.Id where T1.Id = ?", [$id]);
         $finalScore = DB::table('pms_submissionfinalscore')->where('SubmissionId', $id)->pluck('FinalScore');
         if (!(empty($finalScore))):
             $finalScore = $finalScore[0];
         else:
             $finalScore = '';
         endif;

         $appraisalType = '';
         if ((bool)$application[0]->WeightageForLevel2 && $application[0]->WeightageForLevel2 > 0):
             if ($application[0]->Level2CriteriaType == 2):
                 $type = 1;
             else:
                 $type = 2;
             endif;
         else:
             $type = 3;
         endif;

         $finalAdjustmentPercentDetails = $this->fetchCurrentPMSAdjustmentDetails($application[0]->Id);
         $count = 1;
         $level1WeightedTotal = $level2WeightedTotal = $selfRatingTotal = $level1QualitativeTotal = $level1QuantitativeTotal = $level2QualitativeTotal =
         $level2QuantitativeTotal = $level1RatingTotal = $level2RatingTotal = $qualitativeWeightageTotal = $quantitativeWeightageTotal = 0;
         foreach ($applicationDetails as $assessmentArea):
             $selfRatingTotal += $assessmentArea->SelfRating;
             if ($assessmentArea->ApplicableToLevel2 == 0):
                 $quantitativeWeightageTotal += $assessmentArea->Weightage;
                 $level1QuantitativeTotal += $assessmentArea->Level1Rating;
             else:
                 $qualitativeWeightageTotal += $assessmentArea->Weightage;
                 $level1QualitativeTotal += $assessmentArea->Level1Rating;
             endif;

             if ((bool)$application[0]->WeightageForLevel2 && $application[0]->WeightageForLevel2 > 0):
                 if ($assessmentArea->ApplicableToLevel2 == 0):
                     $level2QuantitativeTotal += $assessmentArea->Level2Rating;
                 else:
                     $level2QualitativeTotal += $assessmentArea->Level2Rating;
                 endif;
             endif;
             $count++;
         endforeach;
         $level1RatingTotal = $level1QualitativeTotal + $level1QuantitativeTotal;
         if ((bool)$application[0]->WeightageForLevel2 && $application[0]->WeightageForLevel2 > 0):
             $level2RatingTotal = $level2QualitativeTotal + $level2QuantitativeTotal;
         endif;
         if ($type == 1):
             if ((bool)$finalAdjustmentPercentDetails):
                 $adjustedLevel1Score = ($level1QuantitativeTotal / $quantitativeWeightageTotal * ($quantitativeWeightageTotal - $finalAdjustmentPercentDetails['Adjustment'])) + $finalAdjustmentPercentDetails['ScoreToInject'] + $level1QualitativeTotal;
             endif;
             $level1WeightedTotal = $level1RatingTotal / 100 * $application[0]->WeightageForLevel1;
             if ((bool)$finalAdjustmentPercentDetails):
                 $level1AdjustedTotal = round($adjustedLevel1Score, 2) / 100 * $application[0]->WeightageForLevel1;
             endif;
             if ((bool)$finalAdjustmentPercentDetails):
                 $adjustedLevel2Score = ($level2QuantitativeTotal / $quantitativeWeightageTotal * ($quantitativeWeightageTotal - $finalAdjustmentPercentDetails['Adjustment'])) + $finalAdjustmentPercentDetails['ScoreToInject'] + $level2QualitativeTotal;
             endif;
             $level2WeightedTotal = $level2RatingTotal / 100 * $application[0]->WeightageForLevel2;
             if ((bool)$finalAdjustmentPercentDetails):
                 $level2AdjustedTotal = round($adjustedLevel2Score, 2) / 100 * $application[0]->WeightageForLevel2;
             endif;
             $finalScore = (bool)$finalAdjustmentPercentDetails ? (round($level1AdjustedTotal, 2) + round($level2AdjustedTotal, 2)) : (round($level1WeightedTotal, 2) + round($level2WeightedTotal, 2));
         elseif ($type == 2):
             if ((bool)$finalAdjustmentPercentDetails):
                 $adjustedLevel1Score = ($level1QuantitativeTotal / $quantitativeWeightageTotal * ($quantitativeWeightageTotal - $finalAdjustmentPercentDetails['Adjustment'])) + $finalAdjustmentPercentDetails['ScoreToInject'] + $level1QualitativeTotal;
             endif;
             $level1WeightedTotal = $level1RatingTotal / 100 * $application[0]->WeightageForLevel1;
             if ((bool)$finalAdjustmentPercentDetails):
                 $level1AdjustedTotal = round($adjustedLevel1Score, 2) / 100 * $application[0]->WeightageForLevel1;
             endif;
             $level2WeightedTotal = $level2RatingTotal / $qualitativeWeightageTotal * $application[0]->WeightageForLevel2;
             $finalScore = round((bool)$finalAdjustmentPercentDetails ? (round($level1AdjustedTotal, 2) + round($level2WeightedTotal, 2)) : (round($level1WeightedTotal, 2) + round($level2WeightedTotal, 2)), 2);
         else:
             if ((bool)$finalAdjustmentPercentDetails):
                 $adjustedLevel1Score = ($level1QuantitativeTotal / $quantitativeWeightageTotal * ($quantitativeWeightageTotal - $finalAdjustmentPercentDetails['Adjustment'])) + $finalAdjustmentPercentDetails['ScoreToInject'] + $level1QualitativeTotal;
             endif;
             $level1WeightedTotal = $level1RatingTotal / 100 * $application[0]->WeightageForLevel1;
             if ((bool)$finalAdjustmentPercentDetails):
                 $level1AdjustedTotal = round($adjustedLevel1Score, 2) / 100 * $application[0]->WeightageForLevel1;
             endif;

             $finalScore = round((bool)$finalAdjustmentPercentDetails ? (round($level1AdjustedTotal, 2)) : (round($level1WeightedTotal, 2)), 2);
         endif;

         return round($finalScore, 2);
     }
    public function fetchCurrentPMSAdjustmentDetails($id){
        $applicationSubmissionDetails = DB::table('pms_submission')->where('Id',$id)->selectRaw("DATE_FORMAT(SubmissionTime,'%Y-%m-%d') as SubmittedAt")->pluck('SubmittedAt');
        $submissionTime = $applicationSubmissionDetails[0];
        $currentPMSQuery = DB::table('sys_pmsnumber')->where('StartDate','<=',$submissionTime)->orderBy('StartDate','DESC')->get(['TargetRevenue','AchievedRevenue']);
        $targetRevenue = $currentPMSQuery[0]->TargetRevenue;
        $achievedRevenue = $currentPMSQuery[0]->AchievedRevenue;
        $adjustmentPercentage = DB::table('mas_pmssettings')->orderBy('created_at','DESC')->pluck('FinalAdjustmentPercent');
        $adjustmentPercentage = isset($adjustmentPercentage[0])?$adjustmentPercentage[0]:false;

        if(!(bool)$targetRevenue || !(bool)$achievedRevenue || !(bool)$adjustmentPercentage){
            return false;
        }else{
            return ['Adjustment'=>$adjustmentPercentage,'ScoreToInject'=>round(($achievedRevenue/$targetRevenue * $adjustmentPercentage),2)];
        }
    }
}
