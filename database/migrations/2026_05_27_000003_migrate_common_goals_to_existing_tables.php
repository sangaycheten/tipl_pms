<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MigrateCommonGoalsToExistingTables extends Migration
{
    public function up()
    {
        // 1. Drop the standalone template detail/task tables (replaced by pms_employeegoaldetail)
        Schema::dropIfExists('pms_common_goal_tasks');    // FK child first
        Schema::dropIfExists('pms_common_goal_details');  // then parent

        // 2. Add CommonGoalId to pms_employeegoal so every master record
        //    (supervisor template + employee copies) can link back to pms_common_goals.
        Schema::table('pms_employeegoal', function (Blueprint $table) {
            $table->unsignedInteger('CommonGoalId')->nullable()->after('Id');
        });
    }

    public function down()
    {
        Schema::table('pms_employeegoal', function (Blueprint $table) {
            $table->dropColumn('CommonGoalId');
        });

        // Recreate minimal shells (no data restoration needed for a feature rollback)
        Schema::create('pms_common_goal_details', function (Blueprint $table) {
            $table->increments('Id');
            $table->unsignedInteger('CommonGoalId');
            $table->unsignedSmallInteger('DisplayOrder')->default(1);
            $table->string('Description', 500);
            $table->decimal('TotalScore', 8, 2)->default(0);
            $table->tinyInteger('InH1')->default(0);
            $table->tinyInteger('InH2')->default(0);
            $table->unsignedInteger('CreatedBy')->nullable();
            $table->unsignedInteger('EditedBy')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->foreign('CommonGoalId')->references('Id')->on('pms_common_goals')->onDelete('cascade');
        });

        Schema::create('pms_common_goal_tasks', function (Blueprint $table) {
            $table->increments('Id');
            $table->unsignedInteger('CommonGoalDetailId');
            $table->string('TaskNumber', 20)->nullable();
            $table->string('Description', 500);
            $table->decimal('Weightage', 8, 2)->default(0);
            $table->string('Target', 100)->nullable();
            $table->unsignedInteger('CreatedBy')->nullable();
            $table->unsignedInteger('EditedBy')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->foreign('CommonGoalDetailId')->references('Id')->on('pms_common_goal_details')->onDelete('cascade');
        });
    }
}
