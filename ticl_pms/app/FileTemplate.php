<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2019-01-01
 * Time: 2:45 PM
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

class FileTemplate extends Model
{
    protected $table = 'pms_goals_file_template';
    protected $primaryKey = "Id";
    public $timestamps = false;
    public $incrementing = false;
    protected $fillable = ["Id","FileName","FilePath","SectionId","DepartmentId","CreatedBy","created_at","EditedBy","updated_at"];
}
