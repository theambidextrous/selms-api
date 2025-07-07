<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('app_messages', function (Blueprint $table) {
            $table->id();
            $table->string('group', 50); // ATTENDANCE, FINANCE, LIB, ACADEMIC ETC
            $table->string('initiator', 5);
            $table->text('message');
            $table->string('subject', 50);
            $table->string('recipient_phone', 15);
            $table->boolean('approved')->default(false);
            $table->boolean('send')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_messages');
    }
};
