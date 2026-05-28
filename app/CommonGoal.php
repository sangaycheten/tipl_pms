<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CommonGoal extends Model
{
    protected $table      = 'pms_common_goals';
    protected $primaryKey = 'Id';
    public    $timestamps = false;
    public    $incrementing = true;

    protected $fillable = [
        'Year', 'Title', 'Status',
        'CreatedBy', 'EditedBy', 'created_at', 'updated_at',
    ];

    /**
     * Employee assignment records.
     */
    public function assignments()
    {
        return $this->hasMany(CommonGoalAssignment::class, 'CommonGoalId', 'Id');
    }
}
