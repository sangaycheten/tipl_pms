<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PMSEmployeeGoalTargetMultipleAppraiser extends Model
{
    protected $primaryKey = "Id";
    public $timestamps = false;
    public $incrementing = false;
    protected $table = "pms_employeetargetmultipleappraiser";
    protected $fillable = [
        "Id","TargetId","Achievement","Level1Appraiser","Level1Score","Level1Remarks","CreatedBy","created_at","EditedBy","updated_at"
    ];

    public function target()
    {
        return $this->belongsTo(PMSEmployeeGoalTarget::class, 'TargetId', 'Id');
    }
}
