<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Services\StorageService;

class CalculateSpaceStorage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'storage:calculate-spaces {--user-id= : ID пользователя} {--sync : Синхронизировать с реальным использованием}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Рассчитать использование памяти пространствами пользователей';

    protected $storageService;

    /**
     * Create a new command instance.
     */
    public function __construct(StorageService $storageService)
    {
        parent::__construct();
        $this->storageService = $storageService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->option('user-id');
        $shouldSync = $this->option('sync');

        if ($userId) {
            $users = User::where('id', $userId)->get();
            if ($users->isEmpty()) {
                $this->error("Пользователь с ID {$userId} не найден");
                return 1;
            }
        } else {
            $users = User::all();
        }

        $this->info("Анализ использования памяти для " . $users->count() . " пользователей...");
        $this->line('');

        foreach ($users as $user) {
            $this->info("=== Пользователь: {$user->name} (ID: {$user->id}) ===");
            
            try {
                $stats = $this->storageService->getUserStorageStats($user);
                
                $this->line("Текущее использование в БД: {$stats['used_mb']} МБ");
                $this->line("Лимит: {$stats['limit_mb']} МБ");
                $this->line("Использовано: {$stats['usage_percent']}%");
                $this->line("Пространств: {$stats['spaces_count']}");
                
                if (!empty($stats['spaces_details'])) {
                    $this->line('');
                    $this->line('Детали по пространствам:');
                    
                    $totalCalculated = 0;
                    foreach ($stats['spaces_details'] as $spaceData) {
                        $space = $spaceData['space'];
                        $usage = $spaceData['usage'];
                        
                        $this->line("  • {$space->name}: {$usage['total_size_mb']} МБ");
                        $this->line("    - Базовая память: {$usage['base_space_size_mb']} МБ");
                        $this->line("    - Задачи ({$usage['tasks_count']} шт.): {$usage['tasks_base_size_mb']} МБ");
                        $this->line("    - Контент: {$usage['content_size_mb']} МБ");
                        $this->line("    - Файлы: {$usage['files_size_mb']} МБ");
                        $this->line("    - Колонки ({$usage['columns_count']} шт.): {$usage['columns_size_mb']} МБ");
                        
                        $totalCalculated += $usage['total_size_mb'];
                    }
                    
                    $this->line('');
                    $this->line("Общий рассчитанный размер: {$totalCalculated} МБ");
                    $this->line("Разница с БД: " . round($totalCalculated - $stats['used_mb'], 3) . " МБ");
                    
                    if ($shouldSync && abs($totalCalculated - $stats['used_mb']) > 0.01) {
                        $this->info("Синхронизация...");
                        $this->storageService->syncUserStorageUsage($user);
                        $this->info("✓ Память синхронизирована");
                    }
                } else {
                    $this->line("У пользователя нет пространств");
                }
                
                $this->line('');
                
            } catch (\Exception $e) {
                $this->error("Ошибка для пользователя {$user->name}: " . $e->getMessage());
            }
        }

        $this->info("Анализ завершен!");
        
        if (!$shouldSync) {
            $this->line('');
            $this->comment('Добавьте флаг --sync для синхронизации реального использования с рассчитанным');
        }
        
        return 0;
    }
}
