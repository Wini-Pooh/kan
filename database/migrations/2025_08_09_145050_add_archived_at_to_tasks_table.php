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
        Schema::table('tasks', function (Blueprint $table) {
            $table->timestamp('archived_at')->nullable()->after('completed_at');
            $table->foreignId('archived_by')->nullable()->constrained('users')->onDelete('set null')->after('archived_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['archived_by']);
            $table->dropColumn(['archived_at', 'archived_by']);
        });
    }
};
