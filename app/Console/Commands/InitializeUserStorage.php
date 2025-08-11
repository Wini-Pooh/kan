<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Space;
use App\Models\Task;
use App\Services\StorageService;

class InitializeUserStorage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'storage:initialize {--user-id= : ID конкретного пользователя}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Инициализация системы учета памяти для существующих пользователей';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->option('user-id');
        $storageService = new StorageService();
        
        if ($userId) {
            $users = User::where('id', $userId)->get();
            if ($users->isEmpty()) {
                $this->error("Пользователь с ID {$userId} не найден");
                return 1;
            }
        } else {
            $users = User::all();
        }
        
        $this->info("Инициализация системы памяти для " . $users->count() . " пользователей...");
        
        foreach ($users as $user) {
            $this->line("Обрабатывается пользователь: {$user->name} (ID: {$user->id})");
            
            // Сбрасываем использование памяти
            $user->update(['storage_used_mb' => 0]);
            
            // Подсчитываем память для пространств
            $userSpaces = $user->createdSpaces;
            $spacesMemory = $userSpaces->count() * StorageService::SPACE_CREATION_SIZE_MB;
            
            if ($spacesMemory > 0) {
                $this->line("  - Пространств: {$userSpaces->count()} ({$spacesMemory} МБ)");
                
                foreach ($userSpaces as $space) {
                    $storageService->trackSpaceCreation($user, $space);
                }
            }
            
            // Подсчитываем память для задач
            $userTasks = Task::whereIn('space_id', $user->createdSpaces->pluck('id'))
                           ->where('created_by', $user->id)
                           ->get();
            
            if ($userTasks->count() > 0) {
                $this->line("  - Задач: {$userTasks->count()}");
                
                foreach ($userTasks as $task) {
                    $storageService->trackTaskCreation($user, $task);
                }
            }
            
            $user->refresh();
            $this->line("  - Итого использовано: {$user->formatted_storage_usage}");
            $this->line("  - Процент использования: {$user->storage_usage_percent}%");
            $this->line("");
        }
        
        $this->info("Инициализация завершена!");
        return 0;
    }
}
