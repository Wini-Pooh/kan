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
        Schema::table('users', function (Blueprint $table) {
            // Общий лимит памяти в МБ (базовый = 50 МБ)
            $table->integer('storage_limit_mb')->default(50)->after('password');
            
            // Использованная память в МБ
            $table->decimal('storage_used_mb', 10, 2)->default(0)->after('storage_limit_mb');
            
            // Текущий тарифный план
            $table->enum('plan_type', ['free', 'test', 'custom'])->default('free')->after('storage_used_mb');
            
            // Дата окончания действия тарифного плана
            $table->timestamp('plan_expires_at')->nullable()->after('plan_type');
            
            // Дополнительная купленная память в МБ
            $table->integer('additional_storage_mb')->default(0)->after('plan_expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'storage_limit_mb',
                'storage_used_mb', 
                'plan_type',
                'plan_expires_at',
                'additional_storage_mb'
            ]);
        });
    }
};
