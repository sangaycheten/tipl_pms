<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PromotionCriteria extends Model
{
    protected $table = 'pms_promotioncriteria';
    protected $primaryKey = "Id";
    public $timestamps = false;
    protected $fillable = ["Id","FromGradeStepId","ToGradeStepId","OutstandingCount","OutstandingAndGoodCount","RegularPromotionCount"];
}
