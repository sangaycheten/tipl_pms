<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddCommonGoalIdToEmployeegoaldetail extends Migration
{
    public function up()
    {
        // Add CommonGoalId to track which common goal set each detail belongs to
        Schema::table('pms_employeegoaldetail', function (Blueprint $table) {
            $table->unsignedInteger('CommonGoalId')->nullable()->after('GoalType');
        });

        // Migrate existing GoalType=3 (old constant) → GoalType=2 (correct per business rule)
        // and stamp their CommonGoalId from the parent pms_employeegoal record
        DB::statement("
            UPDATE pms_employeegoaldetail d
            JOIN pms_employeegoal eg ON eg.Id = d.EmployeeGoalId
            SET d.GoalType = 2,
                d.CommonGoalId = eg.CommonGoalId
            WHERE d.GoalType = 3
              AND eg.CommonGoalId IS NOT NULL
        ");
    }

    public function down()
    {
        Schema::table('pms_employeegoaldetail', function (Blueprint $table) {
            $table->dropColumn('CommonGoalId');
        });
    }
}
