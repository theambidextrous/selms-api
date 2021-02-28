<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('admission', 20);
            $table->string('date_of_admission', 15);
            $table->string('fname', 55);
            $table->string('lname', 55);
            $table->string('address', 55)->nullable();
            $table->string('city', 55)->nullable();
            $table->string('county', 55)->nullable();
            $table->string('zip', 8)->nullable();
            $table->string('parent', 10)->nullable();
            $table->string('form', 10);
            $table->string('stream', 10);
            $table->string('current_term', 4);
            $table->string('expected_grad');
            $table->string('gender', 10);
            $table->string('dob', 16);
            $table->string('birth_cert', 20);
            $table->string('nemis_no', 32)->nullable();
            $table->string('huduma_no', 32)->nullable();
            $table->string('is_active')->default(true);
            $table->string('pic')->nullable()->default('default.png');
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
        Schema::dropIfExists('students');
    }
}
