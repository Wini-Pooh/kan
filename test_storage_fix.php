<?php

/**
 * Скрипт для тестирования исправлений системы учета памяти
 */

require_once 'vendor/autoload.php';

use App\Models\User;
use App\Models\Organization;
use App\Models\Space;
use App\Models\Task;
use App\Services\StorageService;
use Illuminate\Support\Facades\DB;

// Симуляция загрузки Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Тест системы учета памяти ===\n\n";

// Находим тестового пользователя
$user = User::first();
if (!$user) {
    echo "Ошибка: Пользователь не найден\n";
    exit(1);
}

echo "Пользователь: {$user->name} (ID: {$user->id})\n";
echo "Память до тестов: {$user->storage_used_mb} МБ из {$user->storage_limit_mb} МБ\n\n";

// Показываем последние операции с памятью
echo "=== Последние 10 операций с памятью ===\n";
$logs = $user->storageUsageLogs()->orderBy('created_at', 'desc')->take(10)->get();

foreach ($logs as $log) {
    echo "{$log->created_at->format('Y-m-d H:i:s')} | ";
    echo "{$log->action} | ";
    echo "{$log->entity_type}:{$log->entity_id} | ";
    echo "MB: {$log->storage_used_mb} | ";
    echo "Total: {$log->storage_after_mb} МБ | ";
    echo "{$log->description}\n";
}

echo "\n=== Статистика использования памяти ===\n";

// Статистика по типам операций
$actionStats = DB::table('storage_usage_logs')
    ->select('action', DB::raw('COUNT(*) as count'), DB::raw('SUM(storage_used_mb) as total_mb'))
    ->where('user_id', $user->id)
    ->groupBy('action')
    ->get();

foreach ($actionStats as $stat) {
    echo "{$stat->action}: {$stat->count} операций, {$stat->total_mb} МБ\n";
}

echo "\n=== Рекомендации ===\n";
echo "1. Проверьте, что при загрузке файла в задачу память увеличивается\n";
echo "2. Проверьте, что при редактировании контента задачи память пересчитывается\n";
echo "3. Убедитесь, что все операции логируются в storage_usage_logs\n";
echo "\nТест завершен!\n";
