<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCgParticipantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cg_participants', function (Blueprint $table) {
            $table->increments('id');
            $table->string('organization')->nullable();
            $table->string('male')->nullable();
            $table->string('female')->nullable();
            $table->string('pwd-male')->nullable();
            $table->string('pwd-female')->nullable();
            $table->unsignedInteger('cg_id')->nullable();
            $table->foreign('cg_id')->references('id')->on('career_guidances')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cg_participants');
    }
}
