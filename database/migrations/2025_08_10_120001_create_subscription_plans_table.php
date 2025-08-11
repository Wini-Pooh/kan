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
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // 'test', 'custom', etc.
            $table->string('display_name'); // Отображаемое название
            $table->text('description')->nullable();
            $table->integer('storage_mb'); // Количество памяти в МБ
            $table->decimal('price', 10, 2); // Цена плана
            $table->string('currency', 3)->default('RUB');
            $table->integer('duration_days')->default(30); // Длительность в днях
            $table->boolean('is_active')->default(true);
            $table->boolean('is_recurring')->default(false); // Автопродление
            $table->json('features')->nullable(); // Дополнительные функции
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
