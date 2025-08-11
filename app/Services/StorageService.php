<?php

namespace App\Services;

use App\Models\User;
use App\Models\Space;
use App\Models\Task;
use App\Models\StorageUsageLog;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class StorageService
{
    // Константы для размеров базовых операций
    const SPACE_CREATION_SIZE_MB = 5;
    const TASK_CREATION_SIZE_MB = 0.1; // 100 КБ
    const COLUMN_CREATION_SIZE_MB = 0.05; // 50 КБ
    const BASE_CONTENT_SIZE_MB = 0.01; // 10 КБ за базовый контент
    
    /**
     * Рассчитать размер файла в МБ
     */
    public function calculateFileSizeMB(UploadedFile $file): float
    {
        return round($file->getSize() / (1024 * 1024), 3);
    }

    /**
     * Рассчитать размер контента в МБ
     */
    public function calculateContentSizeMB(string $content): float
    {
        $sizeBytes = strlen($content);
        return round($sizeBytes / (1024 * 1024), 3);
    }

    /**
     * Учет памяти при создании пространства
     */
    public function trackSpaceCreation(User $user, Space $space): void
    {
        $user->increaseStorageUsage(
            mb: self::SPACE_CREATION_SIZE_MB,
            action: 'create_space',
            entityType: 'space',
            entityId: $space->id,
            description: "Создание пространства '{$space->name}'",
            metadata: [
                'space_name' => $space->name,
                'organization_id' => $space->organization_id,
            ]
        );
    }

    /**
     * Учет памяти при удалении пространства
     */
    public function trackSpaceDeletion(User $user, Space $space): void
    {
        $user->decreaseStorageUsage(
            mb: self::SPACE_CREATION_SIZE_MB,
            action: 'delete_space',
            entityType: 'space',
            entityId: $space->id,
            description: "Удаление пространства '{$space->name}'",
            metadata: [
                'space_name' => $space->name,
                'organization_id' => $space->organization_id,
            ]
        );
    }

    /**
     * Учет памяти при создании задачи
     */
    public function trackTaskCreation(User $user, Task $task): void
    {
        $contentSize = 0;
        if ($task->content) {
            $contentSize = $this->calculateContentSizeMB($task->content);
        }
        
        // Учитываем размер названия
        $titleSize = 0;
        if ($task->title) {
            $titleSize = $this->calculateContentSizeMB($task->title);
        }
        
        // Учитываем размер описания
        $descriptionSize = 0;
        if ($task->description) {
            $descriptionSize = $this->calculateContentSizeMB($task->description);
        }
        
        $totalSize = self::TASK_CREATION_SIZE_MB + $contentSize + $titleSize + $descriptionSize;
        
        $user->increaseStorageUsage(
            mb: $totalSize,
            action: 'create_task',
            entityType: 'task',
            entityId: $task->id,
            description: "Создание задачи '{$task->title}'",
            metadata: [
                'task_title' => $task->title,
                'space_id' => $task->space_id,
                'content_size_mb' => $contentSize,
                'title_size_mb' => $titleSize,
                'description_size_mb' => $descriptionSize,
                'base_size_mb' => self::TASK_CREATION_SIZE_MB,
            ]
        );
    }

    /**
     * Учет памяти при обновлении контента задачи
     */
    public function trackTaskContentUpdate(User $user, Task $task, string $oldContent, string $newContent): void
    {
        $oldSize = $this->calculateContentSizeMB($oldContent);
        $newSize = $this->calculateContentSizeMB($newContent);
        $difference = $newSize - $oldSize;
        
        if ($difference > 0) {
            $user->increaseStorageUsage(
                mb: $difference,
                action: 'update_task_content',
                entityType: 'task',
                entityId: $task->id,
                description: "Обновление контента задачи '{$task->title}'",
                metadata: [
                    'task_title' => $task->title,
                    'old_content_size_mb' => $oldSize,
                    'new_content_size_mb' => $newSize,
                    'size_difference_mb' => $difference,
                ]
            );
        } elseif ($difference < 0) {
            $user->decreaseStorageUsage(
                mb: abs($difference),
                action: 'update_task_content',
                entityType: 'task',
                entityId: $task->id,
                description: "Уменьшение контента задачи '{$task->title}'",
                metadata: [
                    'task_title' => $task->title,
                    'old_content_size_mb' => $oldSize,
                    'new_content_size_mb' => $newSize,
                    'size_difference_mb' => $difference,
                ]
            );
        }
    }

    /**
     * Учет памяти при обновлении названия задачи
     */
    public function trackTaskTitleUpdate(User $user, Task $task, string $oldTitle, string $newTitle): void
    {
        $oldSize = $this->calculateContentSizeMB($oldTitle);
        $newSize = $this->calculateContentSizeMB($newTitle);
        $difference = $newSize - $oldSize;
        
        if ($difference > 0) {
            $user->increaseStorageUsage(
                mb: $difference,
                action: 'update_task_title',
                entityType: 'task',
                entityId: $task->id,
                description: "Обновление названия задачи",
                metadata: [
                    'old_title' => $oldTitle,
                    'new_title' => $newTitle,
                    'old_title_size_mb' => $oldSize,
                    'new_title_size_mb' => $newSize,
                    'size_difference_mb' => $difference,
                ]
            );
        } elseif ($difference < 0) {
            $user->decreaseStorageUsage(
                mb: abs($difference),
                action: 'update_task_title',
                entityType: 'task',
                entityId: $task->id,
                description: "Уменьшение названия задачи",
                metadata: [
                    'old_title' => $oldTitle,
                    'new_title' => $newTitle,
                    'old_title_size_mb' => $oldSize,
                    'new_title_size_mb' => $newSize,
                    'size_difference_mb' => $difference,
                ]
            );
        }
    }

    /**
     * Учет памяти при обновлении описания задачи
     */
    public function trackTaskDescriptionUpdate(User $user, Task $task, string $oldDescription, string $newDescription): void
    {
        $oldSize = $this->calculateContentSizeMB($oldDescription);
        $newSize = $this->calculateContentSizeMB($newDescription);
        $difference = $newSize - $oldSize;
        
        if ($difference > 0) {
            $user->increaseStorageUsage(
                mb: $difference,
                action: 'update_task_description',
                entityType: 'task',
                entityId: $task->id,
                description: "Обновление описания задачи '{$task->title}'",
                metadata: [
                    'task_title' => $task->title,
                    'old_description_size_mb' => $oldSize,
                    'new_description_size_mb' => $newSize,
                    'size_difference_mb' => $difference,
                ]
            );
        } elseif ($difference < 0) {
            $user->decreaseStorageUsage(
                mb: abs($difference),
                action: 'update_task_description',
                entityType: 'task',
                entityId: $task->id,
                description: "Уменьшение описания задачи '{$task->title}'",
                metadata: [
                    'task_title' => $task->title,
                    'old_description_size_mb' => $oldSize,
                    'new_description_size_mb' => $newSize,
                    'size_difference_mb' => $difference,
                ]
            );
        }
    }

    /**
     * Подсчет общей потраченной памяти пространства
     */
    public function calculateSpaceStorageUsage(Space $space): array
    {
        // Базовая память пространства
        $baseSpaceSize = self::SPACE_CREATION_SIZE_MB;
        
        // Получаем все задачи пространства (включая архивные)
        $tasks = $space->tasks()->get();
        
        $tasksSizeMB = 0;
        $filesSize = 0;
        $contentSize = 0;
        $taskCount = $tasks->count();
        
        foreach ($tasks as $task) {
            // Базовая память задачи
            $tasksSizeMB += self::TASK_CREATION_SIZE_MB;
            
            // Размер контента задачи
            if ($task->content) {
                $taskContentSize = $this->calculateContentSizeMB($task->content);
                $contentSize += $taskContentSize;
            }
            
            // Размер файлов в задаче (парсим контент на наличие файлов)
            if ($task->content) {
                $filesSize += $this->calculateFilesInContent($task->content);
            }
        }
        
        // Получаем все колонки пространства
        $columns = $space->columns()->get();
        $columnsSize = $columns->count() * self::COLUMN_CREATION_SIZE_MB;
        
        $totalSize = $baseSpaceSize + $tasksSizeMB + $contentSize + $filesSize + $columnsSize;
        
        return [
            'total_size_mb' => round($totalSize, 3),
            'base_space_size_mb' => $baseSpaceSize,
            'tasks_base_size_mb' => round($tasksSizeMB, 3),
            'content_size_mb' => round($contentSize, 3),
            'files_size_mb' => round($filesSize, 3),
            'columns_size_mb' => round($columnsSize, 3),
            'tasks_count' => $taskCount,
            'columns_count' => $columns->count(),
        ];
    }
    
    /**
     * Подсчет размера файлов в контенте задачи
     */
    public function calculateFilesInContent(string $content): float
    {
        $totalSize = 0;
        
        // Парсим JSON контент для поиска файлов
        try {
            $blocks = json_decode($content, true);
            if (is_array($blocks)) {
                foreach ($blocks as $block) {
                    if (isset($block['type']) && $block['type'] === 'file') {
                        if (isset($block['data']['file']['size'])) {
                            // Размер в байтах, конвертируем в МБ
                            $totalSize += $block['data']['file']['size'] / (1024 * 1024);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            // Если не удалось распарсить, считаем как текст
            return 0;
        }
        
        return round($totalSize, 3);
    }
    
    /**
     * Получить детальную статистику использования памяти пользователем (включая анализ пространств)
     */
    public function getUserStorageStats(User $user): array
    {
        $user->refresh();
        
        $userSpaces = $user->spaces()->get();
        $totalSpacesSize = 0;
        $spacesDetails = [];
        
        foreach ($userSpaces as $space) {
            $spaceUsage = $this->calculateSpaceStorageUsage($space);
            $spacesDetails[] = [
                'space' => $space,
                'usage' => $spaceUsage
            ];
            $totalSpacesSize += $spaceUsage['total_size_mb'];
        }
        
        return [
            'used_mb' => (float) $user->storage_used_mb,
            'limit_mb' => $user->total_storage_limit,
            'available_mb' => $user->available_storage,
            'usage_percent' => $user->storage_usage_percent,
            'formatted_used' => $user->formatted_storage_usage,
            'formatted_limit' => $user->formatted_storage_limit,
            'plan_type' => $user->plan_type,
            'plan_type_display' => $user->plan_type_display,
            'has_active_subscription' => $user->hasActiveSubscription(),
            'spaces_calculated_size_mb' => round($totalSpacesSize, 3),
            'spaces_details' => $spacesDetails,
            'spaces_count' => $userSpaces->count(),
        ];
    }
    
    /**
     * Синхронизация реальной памяти пользователя с рассчитанной
     */
    public function syncUserStorageUsage(User $user): void
    {
        $stats = $this->getUserStorageStats($user);
        $realUsage = $stats['spaces_calculated_size_mb'];
        
        // Обновляем реальное использование памяти
        $user->storage_used_mb = $realUsage;
        $user->save();
        
        // Логируем синхронизацию
        StorageUsageLog::create([
            'user_id' => $user->id,
            'action' => 'sync_storage',
            'entity_type' => 'user',
            'entity_id' => $user->id,
            'storage_used_mb' => 0, // Синхронизация не добавляет память
            'storage_before_mb' => $stats['user_storage_used_mb'],
            'storage_after_mb' => $realUsage,
            'description' => 'Синхронизация реального использования памяти',
            'metadata' => [
                'old_usage' => $stats['user_storage_used_mb'],
                'calculated_usage' => $realUsage,
                'spaces_count' => count($stats['spaces_details']),
            ]
        ]);
    }

    /**
     * Учет памяти при загрузке файла
     */
    public function trackFileUpload(User $user, UploadedFile $file, string $entityType = 'task', int $entityId = null): void
    {
        $fileSizeMB = $this->calculateFileSizeMB($file);
        
        $user->increaseStorageUsage(
            mb: $fileSizeMB,
            action: 'upload_file',
            entityType: $entityType,
            entityId: $entityId,
            description: "Загрузка файла '{$file->getClientOriginalName()}'",
            metadata: [
                'file_name' => $file->getClientOriginalName(),
                'file_size_mb' => $fileSizeMB,
                'file_type' => $file->getMimeType(),
                'file_extension' => $file->getClientOriginalExtension(),
            ]
        );
    }

    /**
     * Учет памяти при удалении файла
     */
    public function trackFileDelete(User $user, string $fileName, float $fileSizeMB, string $entityType = 'task', int $entityId = null): void
    {
        $user->decreaseStorageUsage(
            mb: $fileSizeMB,
            action: 'delete_file',
            entityType: $entityType,
            entityId: $entityId,
            description: "Удаление файла '{$fileName}'",
            metadata: [
                'file_name' => $fileName,
                'file_size_mb' => $fileSizeMB,
            ]
        );
    }

    /**
     * Учет памяти при удалении задачи
     */
    public function trackTaskDeletion(User $user, Task $task): void
    {
        // Рассчитываем общий размер задачи
        $contentSize = 0;
        if ($task->content) {
            $contentSize = $this->calculateContentSizeMB($task->content);
        }
        
        // Учитываем размер названия
        $titleSize = 0;
        if ($task->title) {
            $titleSize = $this->calculateContentSizeMB($task->title);
        }
        
        // Учитываем размер описания
        $descriptionSize = 0;
        if ($task->description) {
            $descriptionSize = $this->calculateContentSizeMB($task->description);
        }
        
        $totalSize = self::TASK_CREATION_SIZE_MB + $contentSize + $titleSize + $descriptionSize;
        
        $user->decreaseStorageUsage(
            mb: $totalSize,
            action: 'delete_task',
            entityType: 'task',
            entityId: $task->id,
            description: "Удаление задачи '{$task->title}'",
            metadata: [
                'task_title' => $task->title,
                'space_id' => $task->space_id,
                'content_size_mb' => $contentSize,
                'title_size_mb' => $titleSize,
                'description_size_mb' => $descriptionSize,
                'base_size_mb' => self::TASK_CREATION_SIZE_MB,
            ]
        );
    }

    /**
     * Получить детальную статистику использования по типам
     */
    public function getDetailedStorageStats(User $user, int $days = 30): array
    {
        $startDate = now()->subDays($days);
        
        $usageByType = StorageUsageLog::getUsageByEntityType($user->id, $startDate);
        $totalUsage = StorageUsageLog::getTotalUsageForUser($user->id, $startDate);
        
        $recentLogs = $user->storageUsageLogs()
            ->where('created_at', '>=', $startDate)
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();
        
        return [
            'usage_by_type' => $usageByType,
            'total_usage_period' => $totalUsage,
            'recent_logs' => $recentLogs,
            'period_days' => $days,
        ];
    }

    /**
     * Проверить лимиты перед операцией
     */
    public function checkStorageLimit(User $user, float $requiredMB): bool
    {
        return $user->hasEnoughStorage($requiredMB);
    }

    /**
     * Получить рекомендации по оптимизации памяти
     */
    public function getStorageOptimizationTips(User $user): array
    {
        $tips = [];
        $usagePercent = $user->storage_usage_percent;
        
        if ($usagePercent > 80) {
            $tips[] = [
                'type' => 'warning',
                'message' => 'Использовано более 80% памяти. Рекомендуем очистить неиспользуемые файлы или обновить тарифный план.',
            ];
        }
        
        if ($usagePercent > 95) {
            $tips[] = [
                'type' => 'danger',
                'message' => 'Критически мало свободного места! Срочно требуется очистка или обновление тарифа.',
            ];
        }
        
        // Проверяем наличие архивированных задач
        $archivedTasksCount = $user->spaces()
            ->with('archivedTasks')
            ->get()
            ->pluck('archivedTasks')
            ->flatten()
            ->count();
            
        if ($archivedTasksCount > 10) {
            $tips[] = [
                'type' => 'info',
                'message' => "У вас {$archivedTasksCount} архивированных задач. Удаление старых архивных задач может освободить место.",
            ];
        }
        
        return $tips;
    }
}
