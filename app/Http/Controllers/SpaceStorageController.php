<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Space;
use App\Models\User;
use App\Services\StorageService;
use Illuminate\Support\Facades\Auth;

class SpaceStorageController extends Controller
{
    protected $storageService;

    public function __construct(StorageService $storageService)
    {
        $this->middleware('auth');
        $this->storageService = $storageService;
    }

    /**
     * Получить статистику памяти для пространства
     */
    public function getSpaceStorageStats(Space $space)
    {
        // Проверяем права доступа
        if (!$this->canAccessSpace($space)) {
            return response()->json(['error' => 'Нет доступа к пространству'], 403);
        }

        $spaceUsage = $this->storageService->calculateSpaceStorageUsage($space);
        $spaceOwner = $space->creator;

        return response()->json([
            'space' => [
                'id' => $space->id,
                'name' => $space->name,
                'creator' => [
                    'id' => $spaceOwner->id,
                    'name' => $spaceOwner->name,
                    'phone' => $spaceOwner->phone,
                ]
            ],
            'usage' => $spaceUsage,
            'owner_profile' => [
                'total_used_mb' => $spaceOwner->storage_used_mb,
                'total_limit_mb' => $spaceOwner->total_storage_limit,
                'usage_percent' => $spaceOwner->storage_usage_percent,
                'plan_type' => $spaceOwner->plan_type_display,
            ]
        ]);
    }

    /**
     * Получить детальную статистику всех пространств пользователя
     */
    public function getUserSpacesStats()
    {
        $user = Auth::user();
        $stats = $this->storageService->getUserStorageStats($user);
        
        return response()->json($stats);
    }

    /**
     * Синхронизировать реальное использование памяти пользователя
     */
    public function syncUserStorage()
    {
        $user = Auth::user();
        
        try {
            $this->storageService->syncUserStorageUsage($user);
            
            $user = User::find($user->id); // Перезагружаем пользователя
            
            return response()->json([
                'success' => true,
                'message' => 'Синхронизация памяти выполнена успешно',
                'new_usage' => $user->storage_used_mb
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка синхронизации: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Получить рекомендации по оптимизации памяти
     */
    public function getOptimizationTips()
    {
        $user = Auth::user();
        $tips = $this->storageService->getStorageOptimizationTips($user);
        
        return response()->json([
            'tips' => $tips,
            'current_usage' => $user->storage_usage_percent
        ]);
    }

    /**
     * Анализ использования памяти по задачам в пространстве
     */
    public function analyzeSpaceTasks(Space $space)
    {
        if (!$this->canAccessSpace($space)) {
            return response()->json(['error' => 'Нет доступа к пространству'], 403);
        }

        $tasks = $space->tasks()->get();
        $tasksAnalysis = [];
        $totalContentSize = 0;
        $totalFilesSize = 0;

        foreach ($tasks as $task) {
            $contentSize = 0;
            $filesSize = 0;
            $filesCount = 0;

            if ($task->content) {
                $contentSize = $this->storageService->calculateContentSizeMB($task->content);
                $filesSize = $this->storageService->calculateFilesInContent($task->content);
                
                // Подсчет количества файлов
                try {
                    $blocks = json_decode($task->content, true);
                    if (is_array($blocks)) {
                        foreach ($blocks as $block) {
                            if (isset($block['type']) && $block['type'] === 'file') {
                                $filesCount++;
                            }
                        }
                    }
                } catch (\Exception $e) {
                    // Игнорируем ошибки парсинга
                }
            }

            $totalSize = StorageService::TASK_CREATION_SIZE_MB + $contentSize + $filesSize;
            $totalContentSize += $contentSize;
            $totalFilesSize += $filesSize;

            $tasksAnalysis[] = [
                'id' => $task->id,
                'title' => $task->title,
                'status' => $task->status,
                'is_archived' => $task->is_archived,
                'created_at' => $task->created_at,
                'size_breakdown' => [
                    'base_size_mb' => StorageService::TASK_CREATION_SIZE_MB,
                    'content_size_mb' => round($contentSize, 3),
                    'files_size_mb' => round($filesSize, 3),
                    'total_size_mb' => round($totalSize, 3),
                ],
                'files_count' => $filesCount,
            ];
        }

        // Сортируем по размеру (самые большие сначала)
        usort($tasksAnalysis, function($a, $b) {
            return $b['size_breakdown']['total_size_mb'] <=> $a['size_breakdown']['total_size_mb'];
        });

        return response()->json([
            'space' => [
                'id' => $space->id,
                'name' => $space->name,
                'tasks_count' => $tasks->count(),
            ],
            'summary' => [
                'total_content_size_mb' => round($totalContentSize, 3),
                'total_files_size_mb' => round($totalFilesSize, 3),
                'average_task_size_mb' => $tasks->count() > 0 ? round(($totalContentSize + $totalFilesSize) / $tasks->count(), 3) : 0,
            ],
            'tasks' => $tasksAnalysis,
            'largest_tasks' => array_slice($tasksAnalysis, 0, 10), // Топ 10 самых больших задач
        ]);
    }

    /**
     * Очистка архивированных задач старше определенного периода
     */
    public function cleanupArchivedTasks(Request $request)
    {
        $request->validate([
            'days' => 'required|integer|min:1|max:365',
            'space_id' => 'nullable|exists:spaces,id'
        ]);

        $user = Auth::user();
        $days = $request->input('days', 30);
        $spaceId = $request->input('space_id');

        // Получаем пространства пользователя через модель Space
        $spacesQuery = \App\Models\Space::whereHas('members', function($query) use ($user) {
            $query->where('user_id', $user->id);
        });
        
        if ($spaceId) {
            $spacesQuery->where('id', $spaceId);
        }
        
        $spaces = $spacesQuery->get();

        $deletedCount = 0;
        $freedMemory = 0;

        foreach ($spaces as $space) {
            // Получаем архивированные задачи через модель Task
            $oldTasks = \App\Models\Task::where('space_id', $space->id)
                ->where('is_archived', true)
                ->where('archived_at', '<', now()->subDays($days))
                ->get();

            foreach ($oldTasks as $task) {
                // Рассчитываем размер задачи перед удалением
                $taskSize = StorageService::TASK_CREATION_SIZE_MB;
                if ($task->content) {
                    $taskSize += $this->storageService->calculateContentSizeMB($task->content);
                    $taskSize += $this->storageService->calculateFilesInContent($task->content);
                }
                
                $freedMemory += $taskSize;
                $task->delete();
                $deletedCount++;
            }
        }

        // Обновляем использование памяти пользователя
        if ($freedMemory > 0) {
            $newUsage = max(0, (float)$user->storage_used_mb - $freedMemory);
            
            // Обновляем пользователя напрямую через модель
            User::where('id', $user->id)->update(['storage_used_mb' => $newUsage]);
            
            // Логируем операцию
            \App\Models\StorageUsageLog::create([
                'user_id' => $user->id,
                'action' => 'cleanup_archived_tasks',
                'entity_type' => 'user',
                'entity_id' => $user->id,
                'storage_used_mb' => -$freedMemory,
                'storage_before_mb' => (float)$user->storage_used_mb,
                'storage_after_mb' => $newUsage,
                'description' => "Очистка {$deletedCount} архивированных задач старше {$days} дней",
                'metadata' => [
                    'deleted_tasks_count' => $deletedCount,
                    'freed_memory_mb' => $freedMemory,
                    'days_threshold' => $days,
                ]
            ]);
        }

        $updatedUser = User::find($user->id); // Перезагружаем

        return response()->json([
            'success' => true,
            'message' => "Удалено {$deletedCount} архивированных задач",
            'deleted_tasks_count' => $deletedCount,
            'freed_memory_mb' => round($freedMemory, 3),
            'new_usage_mb' => $updatedUser->storage_used_mb
        ]);
    }

    /**
     * Проверить доступ к пространству
     */
    private function canAccessSpace(Space $space): bool
    {
        $user = Auth::user();
        
        // Проверяем, является ли пользователь участником пространства
        return $space->members()->where('user_id', $user->id)->exists() ||
               $space->created_by === $user->id;
    }
}
