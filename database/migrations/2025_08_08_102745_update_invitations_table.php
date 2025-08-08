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
        Schema::table('invitations', function (Blueprint $table) {
            $table->enum('type', ['space', 'organization'])->default('space')->after('token');
            $table->foreignId('organization_id')->nullable()->constrained('organizations')->onDelete('cascade')->after('space_id');
            $table->foreignId('accepted_by')->nullable()->constrained('users')->onDelete('set null')->after('status');
            $table->timestamp('accepted_at')->nullable()->after('accepted_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            $table->dropColumn(['type', 'organization_id', 'accepted_by', 'accepted_at']);
        });
    }
};
