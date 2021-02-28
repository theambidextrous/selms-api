<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTermsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('terms', function (Blueprint $table) {
            $table->id();
            $table->string('year', 4);
            $table->string('label', 30);
            $table->string('start', 15);
            $table->string('end', 15);
            $table->boolean('is_current')->default(false);
            $table->string('f1_fee', 5);
            $table->string('f2_fee', 5);
            $table->string('f3_fee', 5);
            $table->string('f4_fee', 5);
            $table->unique(['year', 'label']);
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
        Schema::dropIfExists('terms');
    }
}
