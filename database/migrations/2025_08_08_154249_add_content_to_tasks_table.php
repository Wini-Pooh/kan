<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // Проверяем, существуют ли колонки перед добавлением
            if (!Schema::hasColumn('tasks', 'start_date')) {
                $table->date('start_date')->nullable()->after('assignee');
            }
            if (!Schema::hasColumn('tasks', 'due_date')) {
                $table->date('due_date')->nullable()->after('start_date');
            }
            if (!Schema::hasColumn('tasks', 'assigned_to')) {
                $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null')->after('due_date');
            }
        });

        // Обновляем enum для priority
        DB::statement("ALTER TABLE tasks MODIFY COLUMN priority ENUM('low', 'medium', 'high', 'urgent', 'critical', 'blocked') DEFAULT 'medium'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            if (Schema::hasColumn('tasks', 'start_date')) {
                $table->dropColumn('start_date');
            }
            if (Schema::hasColumn('tasks', 'due_date')) {
                $table->dropColumn('due_date');
            }
            if (Schema::hasColumn('tasks', 'assigned_to')) {
                $table->dropForeign(['assigned_to']);
                $table->dropColumn('assigned_to');
            }
        });

        // Возвращаем enum для priority
        DB::statement("ALTER TABLE tasks MODIFY COLUMN priority ENUM('low', 'medium', 'high') DEFAULT 'medium'");
    }
};
