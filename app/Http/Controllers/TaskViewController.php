<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\Space;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TaskViewController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Показать страницу просмотра/редактирования задачи
     */
    public function show(Task $task)
    {
        // Проверяем права доступа к пространству задачи
        $space = $task->space;
        if (!$space->members()->where('user_id', Auth::id())->exists()) {
            abort(403, 'У вас нет доступа к этой задаче');
        }

        // Загружаем связанные данные
        $task->load(['space', 'column', 'creator', 'assignedUser']);
        
        // Загружаем активных участников пространства для выбора исполнителя
        $space->load('activeMembers');

        // Парсим контент для отображения файлов
        $task->parsed_content = $this->parseFileContent($task->content);

        return view('task.show', compact('task', 'space'));
    }

    /**
     * Парсит контент задачи и преобразует ссылки на файлы в HTML
     */
    private function parseFileContent($content)
    {
        if (!$content) {
            return '';
        }

        // Парсим файлы в формате [FILE:url|filename|size|type]
        $content = preg_replace_callback(
            '/\[FILE:(.*?)\|(.*?)\|(.*?)\|(.*?)\]/',
            function ($matches) {
                $url = $matches[1];
                $filename = $matches[2];
                $size = $matches[3];
                $type = $matches[4];

                switch ($type) {
                    case 'image':
                        return '<div class="file-block image-block" style="margin: 15px 0;">
                                    <img src="' . htmlspecialchars($url) . '" 
                                         alt="' . htmlspecialchars($filename) . '" 
                                         class="img-fluid" 
                                         style="max-width: 100%; max-height: 400px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                                    <div class="file-info text-muted small mt-2">
                                        <i class="fas fa-image"></i> ' . htmlspecialchars($filename) . ' (' . htmlspecialchars($size) . ')
                                    </div>
                                </div>';

                    case 'video':
                        return '<div class="file-block video-block" style="margin: 15px 0;">
                                    <video controls style="max-width: 100%; max-height: 400px; border-radius: 8px;">
                                        <source src="' . htmlspecialchars($url) . '" type="video/mp4">
                                        Ваш браузер не поддерживает воспроизведение видео.
                                    </video>
                                    <div class="file-info text-muted small mt-2">
                                        <i class="fas fa-video"></i> ' . htmlspecialchars($filename) . ' (' . htmlspecialchars($size) . ')
                                    </div>
                                </div>';

                    default:
                        return '<div class="file-block document-block" style="margin: 15px 0;">
                                    <div class="card" style="max-width: 300px;">
                                        <div class="card-body d-flex align-items-center">
                                            <i class="fas fa-file fa-2x text-primary me-3"></i>
                                            <div>
                                                <a href="' . htmlspecialchars($url) . '" target="_blank" class="text-decoration-none">
                                                    <strong>' . htmlspecialchars($filename) . '</strong>
                                                </a>
                                                <div class="text-muted small">' . htmlspecialchars($size) . '</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>';
                }
            },
            $content
        );

        return nl2br(htmlspecialchars($content, ENT_NOQUOTES, 'UTF-8', false));
    }

    /**
     * Обновить задачу
     */
    public function update(Request $request, Task $task)
    {
        // Проверяем права доступа к пространству задачи
        $space = $task->space;
        if (!$space->members()->where('user_id', Auth::id())->exists()) {
            abort(403, 'У вас нет доступа к этой задаче');
        }

        $request->validate([
            'title' => 'string|max:255',
            'description' => 'nullable|string',
            'assignee' => 'nullable|string|max:255',
            'estimated_time' => 'nullable|string|max:50',
            'priority' => 'nullable|in:low,medium,high,urgent,critical,blocked',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $updateData = [];
        
        // Обновляем только те поля, которые пришли в запросе
        foreach (['title', 'description', 'assignee', 'estimated_time', 'priority', 'start_date', 'due_date', 'assigned_to'] as $field) {
            if ($request->has($field)) {
                $updateData[$field] = $request->input($field);
            }
        }

        $task->update($updateData);

        // Для AJAX запросов возвращаем JSON
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Задача успешно обновлена',
                'task' => $task->fresh()->load(['assignedUser'])
            ]);
        }

        return redirect()->route('tasks.show', $task)->with('success', 'Задача успешно обновлена!');
    }

    /**
     * Обновить контент задачи
     */
    public function updateContent(Request $request, Task $task)
    {
        // Проверяем права доступа к пространству задачи
        $space = $task->space;
        if (!$space->members()->where('user_id', Auth::id())->exists()) {
            abort(403, 'У вас нет доступа к этой задаче');
        }

        $request->validate([
            'content' => 'required|string',
        ]);

        $task->update([
            'content' => $request->input('content')
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Контент сохранен'
        ]);
    }

    /**
     * Загрузка файла к задаче (упрощенная версия)
     */
    public function uploadFile(Request $request, Task $task)
    {
        try {
            // Проверяем права доступа к задаче через пространство
            $space = $task->space;
            if (!$space->members()->where('user_id', Auth::id())->exists()) {
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
            
            // Определяем тип файла
            $isImage = in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif']);
            $isVideo = in_array(strtolower($extension), ['mp4', 'avi', 'mkv', 'mov', 'wmv', 'flv', 'webm']);
            
            // Форматируем размер файла
            $formattedSize = $this->formatFileSize($size);
            
            // Просто добавляем ссылку на файл в content - проще и легче
            $currentContent = $task->content ?? '';
            $fileLink = "\n\n[FILE:{$fileUrl}|{$originalName}|{$formattedSize}|" . ($isImage ? 'image' : ($isVideo ? 'video' : 'file')) . "]\n\n";
            $newContent = $currentContent . $fileLink;
            
            $task->update(['content' => $newContent]);

            return response()->json([
                'success' => true,
                'message' => 'Файл успешно загружен',
                'file_url' => $fileUrl,
                'file_name' => $originalName,
                'file_size' => $formattedSize,
                'file_type' => $isImage ? 'image' : ($isVideo ? 'video' : 'file')
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
     * Удалить задачу
     */
    public function destroy(Task $task)
    {
        // Проверяем права доступа к пространству задачи
        $space = $task->space;
        if (!$space->members()->where('user_id', Auth::id())->exists()) {
            abort(403, 'У вас нет доступа к этой задаче');
        }

        $spaceSlug = $task->space->slug;
        $organizationSlug = $task->space->organization->slug;
        
        $task->delete();

        return redirect()->route('spaces.show', [$organizationSlug, $spaceSlug])->with('success', 'Задача успешно удалена!');
    }
}
