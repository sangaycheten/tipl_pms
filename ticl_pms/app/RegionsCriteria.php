<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2024-01-29
 * Time: 12:08 PM
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

class RegionsCriteria extends Model
{
    protected $table = 'mas_pmsregions_criteria';
    protected $primaryKey = "Id";
    public $timestamps = false;
    protected $fillable = ["Id","EmpId","EmployeeId",
        "Level1ANDWeightage","Level1ANDAppraiserId","Level1MarketingWeightage","Level1MarketingAppraiserId",
	"Level2Weightage", "DisplayOrder", "EmployeeStatus"];

    protected $casts = [
        'Id' => 'string', // Ensure 'Id' is always treated as a string
    ];
}
