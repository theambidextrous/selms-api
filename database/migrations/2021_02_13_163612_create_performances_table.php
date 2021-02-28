<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePerformancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('performances', function (Blueprint $table) {
            $table->id();
            $table->string('student', 4);
            $table->string('subject', 4);
            $table->string('group', 4);
            $table->string('mark', 3);
            $table->string('grade', 2);
            $table->string('remark', 25);
            $table->string('term', 4);
            $table->unique(['student','subject', 'group', 'term']);
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
        Schema::dropIfExists('performances');
    }
}
