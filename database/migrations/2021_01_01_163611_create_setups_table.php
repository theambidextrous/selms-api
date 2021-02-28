<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSetupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('setups', function (Blueprint $table) {
            $table->id();
            $table->string('school', 255);
            $table->string('address', 55);
            $table->string('city', 30);
            $table->string('county', 35);
            $table->string('zip', 5);
            $table->string('email', 55);
            $table->string('phone', 16);
            $table->string('website');
            $table->string('motto');
            $table->string('logo');
            $table->boolean('is_active')->default(true);
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
        Schema::dropIfExists('setups');
    }
}
