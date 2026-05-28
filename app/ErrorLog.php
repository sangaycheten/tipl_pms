<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2019-01-04
 * Time: 11:56 AM
 */

namespace App;


use Illuminate\Database\Eloquent\Model;

class ErrorLog extends Model
{
    protected $table = "dev_errorlog";
    public $timestamps = false;
    protected $fillable = ["Id","Description","Date","Resolved","Message","Code","File","LineNo","URL"];
}