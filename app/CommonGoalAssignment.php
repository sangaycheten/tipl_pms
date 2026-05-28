<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CommonGoalAssignment extends Model
{
    protected $table      = 'pms_common_goal_assignments';
    protected $primaryKey = 'Id';
    public    $timestamps = false;
    public    $incrementing = true;

    protected $fillable = [
        'CommonGoalId', 'EmployeeId', 'DepartmentId', 'SectionId', 'created_at',
    ];

    public function commonGoal()
    {
        return $this->belongsTo(CommonGoal::class, 'CommonGoalId', 'Id');
    }

    public function employee()
    {
        return $this->belongsTo(\App\MasEmployee::class, 'EmployeeId', 'Id');
    }
}
