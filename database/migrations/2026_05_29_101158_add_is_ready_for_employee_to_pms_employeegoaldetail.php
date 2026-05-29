<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pms_employeegoaldetail', function (Blueprint $table) {
            // 0 = awaiting supervisor to set weightages; 1 = supervisor approved, visible to employee
            $table->tinyInteger('IsReadyForEmployee')->default(0)->after('InH2');
        });

        // Existing common goal rows (GoalType=2) that already have a Weightage > 0
        // were already processed by a supervisor — mark them as ready.
        DB::statement("
            UPDATE pms_employeegoaldetail
            SET IsReadyForEmployee = 1
            WHERE GoalType = 2 AND Weightage > 0
        ");

        // All non-common-goal rows (section goals, ONM, etc.) are always visible.
        DB::statement("
            UPDATE pms_employeegoaldetail
            SET IsReadyForEmployee = 1
            WHERE GoalType IS NULL OR GoalType != 2
        ");
    }

    public function down(): void
    {
        Schema::table('pms_employeegoaldetail', function (Blueprint $table) {
            $table->dropColumn('IsReadyForEmployee');
        });
    }
};
