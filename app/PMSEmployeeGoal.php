<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PMSEmployeeGoal extends Model
{
    //
    protected $primaryKey = "Id";
    public $timestamps = false;
    public $incrementing = false;
    protected $table = "pms_employeegoal";
    protected $fillable = [
        "Id","SysPmsNumberId","EmployeeId","DepartmentId","MultipleLevel1Appraiser",
        "MultipleLevel2Appraiser","Status","H1Status","H2Status","GoalSetBy","ApprovalStatus",
        "ApprovalRemark","CreatedBy","created_at","EditedBy","updated_at"
    ];
}
