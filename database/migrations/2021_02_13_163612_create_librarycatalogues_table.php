<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLibrarycataloguesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('librarycatalogues', function (Blueprint $table) {
            $table->id();
            $table->string('title', 55);
            $table->string('author', 55);
            $table->string('publisher', 55);
            $table->string('available', 3);
            $table->string('lent', 3)->default(0);
            $table->string('lost', 3)->default(0);
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
        Schema::dropIfExists('librarycatalogues');
    }
}
