<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PMSEmployeeGoalL1Submission extends Model
{
    protected $primaryKey = 'Id';
    public $incrementing  = false;
    public $timestamps    = false;
    protected $table      = 'pms_employeegoal_l1submission';
    protected $fillable   = [
        'Id', 'EmployeeGoalId', 'AppraiserEmployeeId',
        'Cycle', 'SubmittedAt', 'CreatedBy', 'created_at', 'updated_at',
    ];
}
