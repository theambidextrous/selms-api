<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLibrarybooksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('librarybooks', function (Blueprint $table) {
            $table->id();
            $table->string('number', 30)->unique();
            $table->string('catalogue', 5);
            $table->string('status', 10)->default('In');
            $table->string('lent_to', 5)->nullable();
            $table->string('lent_from', 15)->nullable();
            $table->string('lent_until', 15)->nullable();
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
        Schema::dropIfExists('librarybooks');
    }
}
