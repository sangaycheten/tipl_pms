<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePmsCommonGoalsTables extends Migration
{
    public function up()
    {
        Schema::create('pms_common_goals', function (Blueprint $table) {
            $table->increments('Id');
            $table->unsignedInteger('Year');
            $table->string('Title', 255)->nullable();
            $table->string('Status', 30)->default('draft'); // draft | published
            $table->unsignedInteger('CreatedBy')->nullable();
            $table->unsignedInteger('EditedBy')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });

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

    public function down()
    {
        Schema::dropIfExists('pms_common_goal_tasks');
        Schema::dropIfExists('pms_common_goal_details');
        Schema::dropIfExists('pms_common_goals');
    }
}
