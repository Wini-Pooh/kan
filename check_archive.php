<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Task;
use App\Models\Space;

echo "Проверка архивированных задач:\n";
echo "=====================================\n";

$space = Space::find(2);
if ($space) {
    echo "Пространство: {$space->name} (ID: {$space->id})\n";
    
    $allTasks = $space->tasks()->count();
    $activeTasks = $space->activeTasks()->count();
    $archivedTasks = $space->archivedTasks()->count();
    
    echo "Всего задач в пространстве: {$allTasks}\n";
    echo "Активных задач: {$activeTasks}\n";
    echo "Архивированных задач: {$archivedTasks}\n";
    
    // Проверим все задачи с archived_at
    $tasksWithArchivedAt = Task::where('space_id', 2)->whereNotNull('archived_at')->get();
    echo "Задач с archived_at != NULL: {$tasksWithArchivedAt->count()}\n";
    
    if ($tasksWithArchivedAt->count() > 0) {
        echo "\nАрхивированные задачи:\n";
        foreach ($tasksWithArchivedAt as $task) {
            echo "- ID: {$task->id}, Название: {$task->title}, Архивирована: {$task->archived_at}, Архивировал: {$task->archived_by}\n";
        }
    }
    
    // Проверим все задачи в пространстве
    echo "\nВсе задачи в пространстве:\n";
    $allSpaceTasks = Task::where('space_id', 2)->get();
    foreach ($allSpaceTasks as $task) {
        $archivedStatus = $task->archived_at ? "АРХИВИРОВАНА ({$task->archived_at})" : "АКТИВНАЯ";
        echo "- ID: {$task->id}, Название: {$task->title}, Статус: {$archivedStatus}\n";
    }
} else {
    echo "Пространство с ID 2 не найдено\n";
}
