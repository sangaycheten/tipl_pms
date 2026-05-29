<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PMSEmployeeGoalDetail extends Model
{
    protected $primaryKey = "Id";
    public $timestamps = false;
    public $incrementing = false;
    protected $table = "pms_employeegoaldetail";
    protected $fillable = [
        "Id","EmployeeGoalId","Type","GoalType","CommonGoalId","DisplayOrder","Description","Weightage","Target","InH1","InH2","IsReadyForEmployee","Year","Achievement","SelfScore","Level1Score","CreatedBy","created_at","EditedBy","updated_at",
        "SelfRemarks","Level1Remarks","Level2Remarks"
    ];

    public function targets()
    {
        return $this->hasMany(PMSEmployeeGoalTarget::class, 'GoalDetailId', 'Id');
    }
}
