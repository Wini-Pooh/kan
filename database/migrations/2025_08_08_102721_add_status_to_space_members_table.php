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
        Schema::table('space_members', function (Blueprint $table) {
            $table->enum('status', ['active', 'pending', 'banned'])->default('active')->after('access_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('space_members', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
