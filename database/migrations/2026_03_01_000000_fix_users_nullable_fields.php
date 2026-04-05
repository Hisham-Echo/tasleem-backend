<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Make optional profile fields nullable so registration works
            // without requiring them upfront
            $table->string('address')->nullable()->change();
            $table->string('city')->nullable()->change();
            $table->string('phone')->nullable()->change();
            $table->string('user_photo')->nullable()->change();
            $table->string('post_code')->nullable()->change();
            $table->string('status')->default('active')->change();
        });
    }

    public function down(): void
    {
        // Nothing to reverse — safer to leave nullable
    }
};
