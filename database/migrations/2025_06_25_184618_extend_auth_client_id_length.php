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
    
         Schema::table('oauth_clients', function (Blueprint $table) {
            $table->string('id', 255)->change();
        });

         Schema::table('oauth_personal_access_clients', function (Blueprint $table) {
            $table->string('id', 255)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
