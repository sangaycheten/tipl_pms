<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2019-01-01
 * Time: 2:45 PM
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

class TaskTargetDetails extends Model
{
    protected $table = 'pms_task_targetdetails';
    protected $primaryKey = "Id";
    public $timestamps = false;
    public $incrementing = false;
    protected $fillable = ["Id","TaskId","GoalId","EmployeeId","DisplayOrder","TaskDescription","Weightage","Target","AchievementId","GoalStatusId",
        "SelfScore","SelfRemarks","SupervisorScore","SupervisorRemarks","StatusId","FileName","FilePath",
        "CreatedBy","created_at","EditedBy","updated_at"
    ];
}
