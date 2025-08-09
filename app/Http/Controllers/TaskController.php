<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\Space;
use App\Models\Column;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Создание новой задачи
     */
    public function store(Request $request, Space $space)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'assignee' => 'nullable|string|max:100',
            'estimated_time' => 'nullable|string|max:50',
            'priority' => 'required|in:low,medium,high',
            'status' => 'required|in:todo,progress,done',
            'column_id' => 'nullable|integer|exists:columns,id'
        ]);

        // Проверяем права доступа к пространству
        if (!$space->members()->where('user_id', Auth::id())->exists()) {
            abort(403, 'У вас нет доступа к этому пространству');
        }

        // Если указана колонка, проверяем что она принадлежит этому пространству
        if ($request->column_id) {
            $column = Column::find($request->column_id);
            if (!$column || $column->space_id !== $space->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Указанная колонка не принадлежит этому пространству'
                ], 400);
            }
        }

        $task = Task::create([
            'title' => $request->title,
            'description' => $request->description,
            'assignee' => $request->assignee,
            'assignee_id' => $request->assignee_id,
            'due_date' => $request->due_date,
            'estimated_time' => $request->estimated_time,
            'priority' => $request->priority,
            'status' => $request->status,
            'space_id' => $space->id,
            'column_id' => $request->column_id,
            'created_by' => Auth::id(),
            'position' => $this->getNextPosition($space, $request->status)
        ]);

        return response()->json([
            'success' => true,
            'task' => $task,
            'message' => 'Задача успешно создана'
        ]);
    }

    /**
     * Обновление статуса задачи (для drag & drop)
     */
    public function updateStatus(Request $request, Space $space, Task $task)
    {
        $request->validate([
            'status' => 'nullable|in:todo,progress,done',
            'column_id' => 'nullable|integer|exists:columns,id',
            'position' => 'nullable|integer'
        ]);

        // Проверяем права доступа
        if (!$space->members()->where('user_id', Auth::id())->exists()) {
            abort(403, 'У вас нет доступа к этому пространству');
        }

        if ($task->space_id !== $space->id) {
            abort(404, 'Задача не найдена в данном пространстве');
        }

        $oldStatus = $task->status;
        $updateData = [];

        // Если указана колонка, используем её
        if ($request->column_id) {
            $column = Column::find($request->column_id);
            if ($column && $column->space_id === $space->id) {
                $updateData['column_id'] = $column->id;
                $mappedStatus = $this->mapColumnToStatus($column->slug);
                
                // Логирование для отладки
                Log::info('Column mapping', [
                    'column_id' => $column->id,
                    'column_name' => $column->name,
                    'column_slug' => $column->slug,
                    'mapped_status' => $mappedStatus
                ]);
                
                $updateData['status'] = $mappedStatus;
            }
        } elseif ($request->status) {
            $updateData['status'] = $request->status;
        }

        if ($request->position) {
            $updateData['position'] = $request->position;
        } else {
            // Автоматически устанавливаем позицию в конец
            $newStatus = $updateData['status'] ?? $task->status;
            $updateData['position'] = $this->getNextPosition($space, $newStatus);
        }

        // Дополнительная проверка валидности статуса
        if (isset($updateData['status']) && !in_array($updateData['status'], ['todo', 'progress', 'done'])) {
            return response()->json([
                'success' => false,
                'message' => 'Недопустимый статус задачи: ' . $updateData['status']
            ], 400);
        }

        $task->update($updateData);
        $newStatus = $task->status;

        // Если задача перемещена в "Выполнено", устанавливаем дату завершения
        if ($newStatus === 'done' && $oldStatus !== 'done') {
            $task->update(['completed_at' => now()]);
        } elseif ($newStatus !== 'done' && $oldStatus === 'done') {
            $task->update(['completed_at' => null]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Статус задачи обновлен'
        ]);
    }

    /**
     * Обновление задачи
     */
    public function update(Request $request, Space $space, Task $task)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'assignee' => 'nullable|string|max:100',
            'assignee_id' => 'nullable|integer|exists:users,id',
            'due_date' => 'nullable|date',
            'estimated_time' => 'nullable|string|max:50',
            'priority' => 'nullable|in:low,medium,high,urgent,critical,blocked'
        ]);

        // Проверяем права доступа
        if (!$space->members()->where('user_id', Auth::id())->exists()) {
            abort(403, 'У вас нет доступа к этому пространству');
        }

        if ($task->space_id !== $space->id) {
            abort(404, 'Задача не найдена в данном пространстве');
        }

        // Если назначается исполнитель, проверяем что он является участником пространства
        if ($request->has('assignee_id') && $request->assignee_id) {
            $assigneeInSpace = $space->members()->where('user_id', $request->assignee_id)->exists();
            if (!$assigneeInSpace) {
                return response()->json([
                    'success' => false,
                    'message' => 'Указанный пользователь не является участником пространства'
                ], 400);
            }
        }

        // Обновляем только те поля, которые были переданы
        $updateData = array_filter($request->only([
            'title', 'description', 'assignee', 'assignee_id', 'due_date', 'estimated_time', 'priority'
        ]), function($value) {
            return $value !== null;
        });

        if (!empty($updateData)) {
            $task->update($updateData);
        }

        return response()->json([
            'success' => true,
            'task' => $task,
            'message' => 'Задача успешно обновлена'
        ]);
    }

    /**
     * Удаление задачи
     */
    public function destroy(Space $space, Task $task)
    {
        // Проверяем права доступа
        if (!$space->members()->where('user_id', Auth::id())->exists()) {
            abort(403, 'У вас нет доступа к этому пространству');
        }

        if ($task->space_id !== $space->id) {
            abort(404, 'Задача не найдена в данном пространстве');
        }

        $task->delete();

        return response()->json([
            'success' => true,
            'message' => 'Задача успешно удалена'
        ]);
    }

    /**
     * Получение всех задач пространства
     */
    public function index(Space $space)
    {
        // Проверяем права доступа
        if (!$space->members()->where('user_id', Auth::id())->exists()) {
            abort(403, 'У вас нет доступа к этому пространству');
        }

        $tasks = $space->tasks()
            ->orderBy('position')
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('status');

        return response()->json([
            'success' => true,
            'tasks' => $tasks
        ]);
    }

    /**
     * Получение следующей позиции для задачи в колонке
     */
    private function getNextPosition(Space $space, $status)
    {
        $maxPosition = $space->tasks()
            ->where('status', $status)
            ->max('position');

        return ($maxPosition ?? 0) + 1;
    }

    /**
     * Массовое обновление позиций задач
     */
    public function updatePositions(Request $request, Space $space)
    {
        $request->validate([
            'tasks' => 'required|array',
            'tasks.*.id' => 'required|integer|exists:tasks,id',
            'tasks.*.status' => 'required|in:todo,progress,done',
            'tasks.*.position' => 'required|integer'
        ]);

        // Проверяем права доступа
        if (!$space->members()->where('user_id', Auth::id())->exists()) {
            abort(403, 'У вас нет доступа к этому пространству');
        }

        foreach ($request->tasks as $taskData) {
            $task = Task::find($taskData['id']);
            
            if ($task && $task->space_id === $space->id) {
                $task->update([
                    'status' => $taskData['status'],
                    'position' => $taskData['position']
                ]);

                // Обновляем дату завершения если нужно
                if ($taskData['status'] === 'done' && !$task->completed_at) {
                    $task->update(['completed_at' => now()]);
                } elseif ($taskData['status'] !== 'done' && $task->completed_at) {
                    $task->update(['completed_at' => null]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Позиции задач обновлены'
        ]);
    }

    /**
     * API обновление задачи (без привязки к пространству)
     */
    public function updateApi(Request $request, Task $task)
    {
        try {
            Log::info('TaskController@updateApi called', [
                'task_id' => $task->id,
                'user_id' => Auth::id(),
                'request_data' => $request->all()
            ]);

            $request->validate([
                'title' => 'nullable|string|max:255',
                'description' => 'nullable|string|max:1000',
                'assignee_id' => 'nullable|integer|exists:users,id',
                'start_date' => 'nullable|date',
                'due_date' => 'nullable|date',
                'estimated_time' => 'nullable|string|max:50',
                'priority' => 'nullable|in:low,medium,high,urgent,critical,blocked'
            ]);

            // Проверяем права доступа к пространству задачи
            $space = $task->space;
            if (!$space) {
                Log::error('Task has no associated space', ['task_id' => $task->id]);
                return response()->json([
                    'success' => false,
                    'message' => 'Задача не привязана к пространству'
                ], 404);
            }

            if (!$space->members()->where('user_id', Auth::id())->exists()) {
                Log::warning('User has no access to space', [
                    'user_id' => Auth::id(),
                    'space_id' => $space->id,
                    'task_id' => $task->id
                ]);
                abort(403, 'У вас нет доступа к этому пространству');
            }

        // Если назначается исполнитель, проверяем что он является участником пространства
        if ($request->has('assignee_id') && $request->assignee_id) {
            $assigneeInSpace = $space->members()->where('user_id', $request->assignee_id)->exists();
            if (!$assigneeInSpace) {
                return response()->json([
                    'success' => false,
                    'message' => 'Указанный пользователь не является участником пространства'
                ], 400);
            }
        }

        // Обновляем только те поля, которые были переданы
        $updateData = [];
        
        if ($request->has('title')) {
            $updateData['title'] = $request->title;
        }
        
        if ($request->has('description')) {
            $updateData['description'] = $request->description;
        }
        
        if ($request->has('assignee_id')) {
            $updateData['assignee_id'] = $request->assignee_id;
        }
        
        if ($request->has('start_date')) {
            $updateData['start_date'] = $request->start_date;
        }
        
        if ($request->has('due_date')) {
            $updateData['due_date'] = $request->due_date;
        }
        
        if ($request->has('estimated_time')) {
            $updateData['estimated_time'] = $request->estimated_time;
        }
        
        if ($request->has('priority')) {
            $updateData['priority'] = $request->priority;
        }

        if (!empty($updateData)) {
            $task->update($updateData);
            Log::info('Task updated successfully', [
                'task_id' => $task->id,
                'updated_fields' => array_keys($updateData)
            ]);
        }

        // Загружаем связанные данные
        $task = $task->fresh(['assignee']);

        return response()->json([
            'success' => true,
            'task' => $task,
            'message' => 'Задача успешно обновлена'
        ]);

        } catch (\Exception $e) {
            Log::error('Error in TaskController@updateApi', [
                'task_id' => $task->id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Произошла ошибка при обновлении задачи',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Обновление контента задачи
     */
    public function updateContent(Request $request, Task $task)
    {
        try {
            // Проверяем права доступа к задаче через пространство
            $space = $task->space;
            if (!$space || !$space->members()->where('user_id', Auth::id())->exists()) {
                abort(403, 'У вас нет доступа к этой задаче');
            }

            $request->validate([
                'content' => 'nullable|string'
            ]);

            // Попытка обновления с обработкой ошибок соединения
            $task->update([
                'content' => $request->input('content')
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Контент задачи сохранен'
            ]);

        } catch (\Illuminate\Database\QueryException $e) {
            // Обработка ошибок базы данных
            Log::error('Database error in updateContent: ' . $e->getMessage(), [
                'task_id' => $task->id,
                'user_id' => Auth::id(),
                'error_code' => $e->getCode()
            ]);

            // Если это ошибка потери соединения, пытаемся переподключиться
            if (strpos($e->getMessage(), 'server has gone away') !== false || 
                strpos($e->getMessage(), 'Lost connection') !== false) {
                
                try {
                    // Переподключение к БД
                    DB::reconnect();
                    
                    // Повторная попытка обновления
                    $task->refresh();
                    $task->update([
                        'content' => $request->input('content')
                    ]);

                    return response()->json([
                        'success' => true,
                        'message' => 'Контент задачи сохранен (после переподключения)'
                    ]);

                } catch (\Exception $retryException) {
                    Log::error('Retry failed in updateContent: ' . $retryException->getMessage());
                    
                    return response()->json([
                        'success' => false,
                        'message' => 'Ошибка соединения с базой данных',
                        'error' => 'database_connection_lost'
                    ], 500);
                }
            }

            return response()->json([
                'success' => false,
                'message' => 'Ошибка при сохранении контента',
                'error' => 'database_error'
            ], 500);

        } catch (\Exception $e) {
            Log::error('General error in updateContent: ' . $e->getMessage(), [
                'task_id' => $task->id,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Произошла ошибка при сохранении',
                'error' => 'general_error'
            ], 500);
        }
    }

    /**
     * Загрузка файла к задаче
     */
    public function uploadFile(Request $request, Task $task)
    {
        try {
            // Проверяем права доступа к задаче через пространство
            $space = $task->space;
            if (!$space || !$space->members()->where('user_id', Auth::id())->exists()) {
                abort(403, 'У вас нет доступа к этой задаче');
            }

            $request->validate([
                'file' => 'required|file|max:50000|mimes:jpg,jpeg,png,gif,pdf,doc,docx,mp4,avi,mkv,mov,wmv,flv,webm,txt,zip,rar'
            ]);

            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $size = $file->getSize();
            
            // Создаем уникальное имя файла
            $fileName = time() . '_' . preg_replace('/[^a-zA-Zа-яА-Я0-9\.\-_]/', '', $originalName);
            
            // Создаем папку для задачи если её нет
            $taskFolder = "tasks/{$task->id}";
            
            // Сохраняем файл
            $filePath = $file->storeAs($taskFolder, $fileName, 'public');
            
            // Генерируем URL для доступа к файлу
            $fileUrl = asset('storage/' . $filePath);
            
            // Определяем тип файла для отображения
            $isImage = in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif']);
            $isVideo = in_array(strtolower($extension), ['mp4', 'avi', 'mkv', 'mov', 'wmv', 'flv', 'webm']);
            
            // Форматируем размер файла
            $formattedSize = $this->formatFileSize($size);
            
            // Создаем HTML для вставки в контент
            if ($isImage) {
                $fileHtml = "\n\n<div class=\"file-attachment\" data-file-path=\"{$filePath}\">\n";
                $fileHtml .= "<img src=\"{$fileUrl}\" alt=\"{$originalName}\" class=\"img-fluid\" style=\"max-width: 100%; max-height: 400px; margin: 10px 0; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);\">\n";
                $fileHtml .= "<div class=\"file-info text-muted small\">{$originalName} ({$formattedSize})</div>\n";
                $fileHtml .= "</div>\n\n";
            } elseif ($isVideo) {
                $fileHtml = "\n\n<div class=\"file-attachment\" data-file-path=\"{$filePath}\">\n";
                $fileHtml .= "<video controls style=\"max-width: 100%; max-height: 400px; margin: 10px 0; border-radius: 5px;\">\n";
                $fileHtml .= "<source src=\"{$fileUrl}\" type=\"video/{$extension}\">\n";
                $fileHtml .= "Ваш браузер не поддерживает воспроизведение видео.\n";
                $fileHtml .= "</video>\n";
                $fileHtml .= "<div class=\"file-info text-muted small\">{$originalName} ({$formattedSize})</div>\n";
                $fileHtml .= "</div>\n\n";
            } else {
                $fileHtml = "\n\n<div class=\"file-attachment\" data-file-path=\"{$filePath}\">\n";
                $fileHtml .= "<a href=\"{$fileUrl}\" target=\"_blank\" class=\"btn btn-outline-primary btn-sm\">\n";
                $fileHtml .= "<i class=\"fas fa-download\"></i> {$originalName} ({$formattedSize})\n";
                $fileHtml .= "</a>\n";
                $fileHtml .= "</div>\n\n";
            }
            
            // Добавляем файл к существующему контенту
            $currentContent = $task->content ?? '';
            $newContent = $currentContent . $fileHtml;
            
            $task->update(['content' => $newContent]);

            return response()->json([
                'success' => true,
                'message' => 'Файл успешно загружен',
                'file_html' => $fileHtml,
                'file_url' => $fileUrl,
                'file_name' => $originalName
            ]);

        } catch (\Exception $e) {
            Log::error('Error uploading file: ' . $e->getMessage(), [
                'task_id' => $task->id,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Ошибка при загрузке файла: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Форматирование размера файла
     */
    private function formatFileSize($bytes)
    {
        if ($bytes >= 1073741824) {
            $bytes = number_format($bytes / 1073741824, 2) . ' ГБ';
        } elseif ($bytes >= 1048576) {
            $bytes = number_format($bytes / 1048576, 2) . ' МБ';
        } elseif ($bytes >= 1024) {
            $bytes = number_format($bytes / 1024, 2) . ' КБ';
        } elseif ($bytes > 1) {
            $bytes = $bytes . ' байт';
        } elseif ($bytes == 1) {
            $bytes = $bytes . ' байт';
        } else {
            $bytes = '0 байт';
        }

        return $bytes;
    }

    /**
     * Маппинг slug колонки на допустимые статусы задач
     */
    private function mapColumnToStatus($columnSlug)
    {
        // Стандартный маппинг slug на статусы
        $mapping = [
            'todo' => 'todo',
            'to-do' => 'todo',
            'backlog' => 'todo',
            'new' => 'todo',
            'открытые' => 'todo',
            'к-выполнению' => 'todo',
            
            'progress' => 'progress',
            'in-progress' => 'progress',
            'doing' => 'progress',
            'work' => 'progress',
            'в-работе' => 'progress',
            'в-процессе' => 'progress',
            
            'done' => 'done',
            'completed' => 'done',
            'finished' => 'done',
            'closed' => 'done',
            'выполнено' => 'done',
            'готово' => 'done',
        ];

        // Приводим к нижнему регистру для сравнения
        $lowerSlug = strtolower($columnSlug);
        
        // Если есть точное соответствие, возвращаем его
        if (isset($mapping[$lowerSlug])) {
            return $mapping[$lowerSlug];
        }
        
        // Если slug содержит ключевые слова, пробуем найти частичное соответствие
        if (str_contains($lowerSlug, 'progress') || str_contains($lowerSlug, 'work') || str_contains($lowerSlug, 'процесс')) {
            return 'progress';
        }
        
        if (str_contains($lowerSlug, 'done') || str_contains($lowerSlug, 'complete') || str_contains($lowerSlug, 'finish') || str_contains($lowerSlug, 'выполн')) {
            return 'done';
        }
        
        // По умолчанию возвращаем 'todo'
        return 'todo';
    }
}
