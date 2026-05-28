<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2019-01-01
 * Time: 2:45 PM
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

class GoalTarget extends Model
{
    protected $table = 'pms_goals_target';
    protected $primaryKey = "Id";
    public $timestamps = false;
    public $incrementing = false;
    protected $fillable = ["Id","GoalDescription","GoalTypeId","SysPmsNumberId","DisplayOrder",
        "CreatedBy","created_at","EditedBy","updated_at"
    ];
}
