<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class RemoveCommongoalidFromEmployeegoal extends Migration
{
    public function up()
    {
        // Clean up any GoalSetBy='commongoal' template rows — they're no longer needed.
        // Their detail rows already have CommonGoalId stamped on pms_employeegoaldetail,
        // so the template pms_employeegoal row itself is redundant.
        $templateIds = DB::table('pms_employeegoal')
            ->where('GoalSetBy', 'commongoal')
            ->pluck('Id');

        if ($templateIds->isNotEmpty()) {
            $detailIds = DB::table('pms_employeegoaldetail')
                ->whereIn('EmployeeGoalId', $templateIds)
                ->pluck('Id');
            if ($detailIds->isNotEmpty()) {
                DB::table('pms_employeegoaltargetdetail')
                    ->whereIn('GoalDetailId', $detailIds)->delete();
                DB::table('pms_employeegoaldetail')
                    ->whereIn('EmployeeGoalId', $templateIds)->delete();
            }
            DB::table('pms_employeegoal')
                ->whereIn('Id', $templateIds)->delete();
        }

        // Drop the CommonGoalId column — no longer needed on pms_employeegoal
        Schema::table('pms_employeegoal', function (Blueprint $table) {
            $table->dropColumn('CommonGoalId');
        });
    }

    public function down()
    {
        Schema::table('pms_employeegoal', function (Blueprint $table) {
            $table->unsignedInteger('CommonGoalId')->nullable()->after('Id');
        });
    }
}
