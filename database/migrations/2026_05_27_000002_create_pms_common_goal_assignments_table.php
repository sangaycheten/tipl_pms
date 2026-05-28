<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePmsCommonGoalAssignmentsTable extends Migration
{
    public function up()
    {
        Schema::create('pms_common_goal_assignments', function (Blueprint $table) {
            $table->increments('Id');
            $table->unsignedInteger('CommonGoalId');
            $table->unsignedInteger('EmployeeId');
            $table->unsignedInteger('DepartmentId');
            $table->unsignedInteger('SectionId')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->foreign('CommonGoalId')
                  ->references('Id')->on('pms_common_goals')
                  ->onDelete('cascade');

            $table->unique(['CommonGoalId', 'EmployeeId'], 'uniq_cg_emp');
        });
    }

    public function down()
    {
        Schema::dropIfExists('pms_common_goal_assignments');
    }
}
