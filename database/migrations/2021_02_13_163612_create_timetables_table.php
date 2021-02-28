<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTimetablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('timetables', function (Blueprint $table) {
            $table->id();
            $table->string('current_term', 4);
            $table->string('day', 30);
            $table->string('date', 30);
            $table->string('time', 15);
            $table->string('stream', 15);
            $table->string('subject', 5);
            $table->string('teacher', 5);
            $table->string('datetime', 15);
            $table->unique(['teacher', 'datetime']);
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
        Schema::dropIfExists('timetables');
    }
}
