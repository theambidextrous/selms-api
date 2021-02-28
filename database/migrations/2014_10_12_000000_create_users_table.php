<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('fname', 55);
            $table->string('lname', 55);
            $table->string('address', 55)->nullable();
            $table->string('city', 55)->nullable();
            $table->string('county', 55)->nullable();
            $table->string('zip', 8)->nullable();
            $table->string('email', 55)->unique();
            $table->string('phone', 14)->unique()->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->boolean('is_super')->default(false);
            $table->boolean('is_admin')->default(false);
            $table->boolean('is_lib')->default(false);
            $table->boolean('is_fin')->default(false);
            $table->boolean('is_teacher')->default(false);
            $table->boolean('is_parent')->default(true);
            $table->boolean('is_active')->default(true);
            $table->string('pic')->default('default.png');
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
