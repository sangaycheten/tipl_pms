<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PMSEmployeeGoalTarget extends Model
{
    protected $primaryKey = "Id";
    public $timestamps = false;
    public $incrementing = false;
    protected $table = "pms_employeegoaltargetdetail";
    protected $fillable = [
        "Id","GoalDetailId","Description","Weightage","Target","Achievement","SelfScore","Level1Appraiser",
        "Level1Score","Level2Appraiser","Level2Score","SelfRemarks","Level1Remarks","Level2Remarks","CreatedBy","created_at","EditedBy","updated_at"
    ];

    public function goalDetail()
    {
        return $this->belongsTo(PMSEmployeeGoalDetail::class, 'GoalDetailId', 'Id');
    }

    /**
     * When MultipleLevel1Appraiser = 1, scores/remarks per appraiser are stored here.
     * When MultipleLevel1Appraiser = 0, Level1Appraiser/Level1Score on this record itself is used.
     */
    public function multipleAppraisers()
    {
        return $this->hasMany(PMSEmployeeGoalTargetMultipleAppraiser::class, 'TargetId', 'Id');
    }

}
