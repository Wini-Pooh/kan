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
        Schema::create('storage_usage_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('action'); // 'create_space', 'create_task', 'upload_file', etc.
            $table->string('entity_type'); // 'space', 'task', 'file', etc.
            $table->unsignedBigInteger('entity_id')->nullable(); // ID сущности
            $table->decimal('storage_used_mb', 10, 2); // Использованная память
            $table->decimal('storage_before_mb', 10, 2); // Память до операции
            $table->decimal('storage_after_mb', 10, 2); // Память после операции
            $table->text('description')->nullable(); // Описание операции
            $table->json('metadata')->nullable(); // Дополнительные данные
            $table->timestamps();
            
            $table->index(['user_id', 'created_at']);
            $table->index(['entity_type', 'entity_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('storage_usage_logs');
    }
};
