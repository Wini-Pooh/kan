<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\Space;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

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
        $task->load(['space', 'column', 'creator', 'assignee']);
        
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
            return '<div class="a4-page" data-page="1" draggable="true">
                        <div class="page-header">
                            <span class="page-number">Лист 1</span>
                            <div class="page-actions">
                                <button class="page-btn page-drag-handle" title="Перетащить лист">
                                    <i class="fas fa-grip-vertical"></i>
                                </button>
                                <button class="page-btn" onclick="deletePage(this)" title="Удалить лист">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        <div class="page-content" onclick="addContentBlock(event)">
                            <div class="empty-page">
                                <i class="fas fa-plus-circle"></i>
                                <p>Нажмите здесь, чтобы добавить контент</p>
                            </div>
                        </div>
                    </div>';
        }

        // Если контент уже содержит HTML-структуру листов A4, обновляем поддержку drag & drop
        if (strpos($content, 'a4-page') !== false) {
            // Добавляем draggable="true" к листам A4, если его нет
            $content = preg_replace(
                '/<div class="a4-page"(?![^>]*draggable)/',
                '<div class="a4-page" draggable="true"',
                $content
            );
            
            // Добавляем drag handle к блокам контента, если его нет
            $content = preg_replace_callback(
                '/<div class="content-block[^"]*"[^>]*id="([^"]*)"[^>]*>.*?<div class="block-toolbar">((?:(?!<\/div>).)*)<\/div>/s',
                function($matches) {
                    $blockId = $matches[1];
                    $toolbarContent = $matches[2];
                    
                    // Если drag handle уже есть, не добавляем
                    if (strpos($toolbarContent, 'drag-handle') !== false) {
                        return $matches[0];
                    }
                    
                    // Добавляем drag handle в начало toolbar
                    $newToolbarContent = '<button class="block-btn drag-handle" title="Перетащить блок">
                                            <i class="fas fa-grip-vertical"></i>
                                          </button>' . $toolbarContent;
                    
                    return str_replace($toolbarContent, $newToolbarContent, $matches[0]);
                },
                $content
            );
            
            // Добавляем draggable="true" к блокам контента, если его нет
            $content = preg_replace(
                '/<div class="content-block[^"]*"(?![^>]*draggable)([^>]*id="[^"]*")/',
                '<div class="content-block" draggable="true" $1',
                $content
            );
            
            // Добавляем page drag handle к листам, если его нет
            $content = preg_replace_callback(
                '/<div class="page-actions">((?:(?!<\/div>).)*)<\/div>/s',
                function($matches) {
                    $actionsContent = $matches[1];
                    
                    // Если page drag handle уже есть, не добавляем
                    if (strpos($actionsContent, 'page-drag-handle') !== false) {
                        return $matches[0];
                    }
                    
                    // Добавляем page drag handle в начало actions
                    $newActionsContent = '<button class="page-btn page-drag-handle" title="Перетащить лист">
                                            <i class="fas fa-grip-vertical"></i>
                                          </button>' . $actionsContent;
                    
                    return str_replace($actionsContent, $newActionsContent, $matches[0]);
                },
                $content
            );
            
            return $content;
        }

        // Парсим файлы в формате [FILE:url|filename|size|type] для старого формата
        $content = preg_replace_callback(
            '/\[FILE:(.*?)\|(.*?)\|(.*?)\|(.*?)\]/',
            function ($matches) {
                $url = $matches[1];
                $filename = $matches[2];
                $size = $matches[3];
                $type = $matches[4];

                switch ($type) {
                    case 'image':
                        return '<div class="content-block image-block" id="block_' . time() . rand(1000, 9999) . '">
                                    <div class="block-toolbar">
                                        <button class="block-btn" onclick="moveBlockUp(this.closest(\'.content-block\').id)" title="Переместить вверх">
                                            <i class="fas fa-arrow-up"></i>
                                        </button>
                                        <button class="block-btn" onclick="moveBlockDown(this.closest(\'.content-block\').id)" title="Переместить вниз">
                                            <i class="fas fa-arrow-down"></i>
                                        </button>
                                        <button class="block-btn delete-btn" onclick="deleteBlock(this.closest(\'.content-block\').id)" title="Удалить блок">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                    <img src="' . htmlspecialchars($url) . '" 
                                         alt="' . htmlspecialchars($filename) . '" 
                                         onclick="showImageModal(\'' . htmlspecialchars($url) . '\', \'' . htmlspecialchars($filename) . '\')">
                                    <div class="image-caption" contenteditable="true" data-placeholder="Добавить подпись к изображению...">' . htmlspecialchars($filename) . '</div>
                                </div>';

                    case 'video':
                        return '<div class="content-block video-block" id="block_' . time() . rand(1000, 9999) . '">
                                    <div class="block-toolbar">
                                        <button class="block-btn" onclick="moveBlockUp(this.closest(\'.content-block\').id)" title="Переместить вверх">
                                            <i class="fas fa-arrow-up"></i>
                                        </button>
                                        <button class="block-btn" onclick="moveBlockDown(this.closest(\'.content-block\').id)" title="Переместить вниз">
                                            <i class="fas fa-arrow-down"></i>
                                        </button>
                                        <button class="block-btn delete-btn" onclick="deleteBlock(this.closest(\'.content-block\').id)" title="Удалить блок">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                    <video controls>
                                        <source src="' . htmlspecialchars($url) . '" type="video/mp4">
                                        Ваш браузер не поддерживает воспроизведение видео.
                                    </video>
                                </div>';

                    default:
                        return '<div class="content-block file-block" id="block_' . time() . rand(1000, 9999) . '">
                                    <div class="block-toolbar">
                                        <button class="block-btn" onclick="moveBlockUp(this.closest(\'.content-block\').id)" title="Переместить вверх">
                                            <i class="fas fa-arrow-up"></i>
                                        </button>
                                        <button class="block-btn" onclick="moveBlockDown(this.closest(\'.content-block\').id)" title="Переместить вниз">
                                            <i class="fas fa-arrow-down"></i>
                                        </button>
                                        <button class="block-btn delete-btn" onclick="deleteBlock(this.closest(\'.content-block\').id)" title="Удалить блок">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                    <div class="file-info">
                                        <div class="file-icon">
                                            <i class="fas fa-file fa-2x"></i>
                                        </div>
                                        <div class="file-details">
                                            <h4><a href="' . htmlspecialchars($url) . '" target="_blank">' . htmlspecialchars($filename) . '</a></h4>
                                            <p>' . htmlspecialchars($size) . '</p>
                                        </div>
                                    </div>
                                </div>';
                }
            },
            $content
        );

        // Если это старый текстовый контент, оборачиваем в лист A4
        if (!empty($content) && strpos($content, 'a4-page') === false) {
            $textBlockId = 'block_' . time() . rand(1000, 9999);
            $content = '<div class="a4-page" data-page="1">
                            <div class="page-header">
                                <span class="page-number">Лист 1</span>
                                <div class="page-actions">
                                    <button class="page-btn" onclick="deletePage(this)" title="Удалить лист">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="page-content">
                                <div class="content-block text-block" id="' . $textBlockId . '">
                                    <div class="block-toolbar">
                                        <button class="block-btn" onclick="moveBlockUp(\'' . $textBlockId . '\')" title="Переместить вверх">
                                            <i class="fas fa-arrow-up"></i>
                                        </button>
                                        <button class="block-btn" onclick="moveBlockDown(\'' . $textBlockId . '\')" title="Переместить вниз">
                                            <i class="fas fa-arrow-down"></i>
                                        </button>
                                        <button class="block-btn delete-btn" onclick="deleteBlock(\'' . $textBlockId . '\')" title="Удалить блок">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                    <div class="text-content" contenteditable="true">' . $content . '</div>
                                </div>
                            </div>
                        </div>';
        }

        return $content;
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
     * Обновить название задачи
     */
    public function updateTitle(Request $request, Task $task)
    {
        // Проверяем права доступа к пространству задачи
        $space = $task->space;
        if (!$space->members()->where('user_id', Auth::id())->exists()) {
            abort(403, 'У вас нет доступа к этой задаче');
        }

        $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $task->update([
            'title' => $request->input('title')
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Название задачи обновлено',
            'title' => $task->title
        ]);
    }

    /**
     * Обновить приоритет задачи
     */
    public function updatePriority(Request $request, Task $task)
    {
        // Проверяем права доступа к пространству задачи
        $space = $task->space;
        if (!$space->members()->where('user_id', Auth::id())->exists()) {
            abort(403, 'У вас нет доступа к этой задаче');
        }

        $request->validate([
            'priority' => 'required|in:low,medium,high,urgent,critical,blocked',
        ]);

        $task->update([
            'priority' => $request->input('priority')
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Приоритет задачи обновлен',
            'priority' => $task->priority
        ]);
    }

    /**
     * Обновить дату начала задачи
     */
    public function updateStartDate(Request $request, Task $task)
    {
        // Проверяем права доступа к пространству задачи
        $space = $task->space;
        if (!$space->members()->where('user_id', Auth::id())->exists()) {
            abort(403, 'У вас нет доступа к этой задаче');
        }

        $request->validate([
            'start_date' => 'nullable|date',
        ]);

        $task->update([
            'start_date' => $request->input('start_date')
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Дата начала обновлена',
            'start_date' => $task->start_date
        ]);
    }

    /**
     * Обновить дату окончания задачи
     */
    public function updateDueDate(Request $request, Task $task)
    {
        // Проверяем права доступа к пространству задачи
        $space = $task->space;
        if (!$space->members()->where('user_id', Auth::id())->exists()) {
            abort(403, 'У вас нет доступа к этой задаче');
        }

        $request->validate([
            'due_date' => 'nullable|date',
        ]);

        $task->update([
            'due_date' => $request->input('due_date')
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Дата окончания обновлена',
            'due_date' => $task->due_date
        ]);
    }

    /**
     * Обновить исполнителя задачи
     */
    public function updateAssignee(Request $request, Task $task)
    {
        // Проверяем права доступа к пространству задачи
        $space = $task->space;
        if (!$space->members()->where('user_id', Auth::id())->exists()) {
            abort(403, 'У вас нет доступа к этой задаче');
        }

        $request->validate([
            'assignee_id' => 'nullable|exists:users,id',
        ]);

        // Если назначается исполнитель, проверяем что он является участником пространства
        $assigneeId = $request->input('assignee_id');
        if ($assigneeId) {
            $isMember = $space->members()->where('user_id', $assigneeId)->exists();
            if (!$isMember) {
                return response()->json([
                    'success' => false,
                    'message' => 'Пользователь не является участником пространства'
                ], 422);
            }
        }

        $task->update([
            'assignee_id' => $assigneeId
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Исполнитель задачи обновлен',
            'assignee_id' => $task->assignee_id,
            'assignee' => $task->assignee ? [
                'id' => $task->assignee->id,
                'name' => $task->assignee->name,
                'avatar' => $task->assignee->avatar
            ] : null
        ]);
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

    /**
     * Скачать задачу в PDF формате
     */
    public function downloadPDF(Task $task)
    {
        // Проверяем права доступа к пространству задачи
        $space = $task->space;
        if (!$space->members()->where('user_id', Auth::id())->exists()) {
            abort(403, 'Нет доступа к этому пространству');
        }

        // Загружаем связанные данные
        $task->load(['space', 'column', 'creator', 'assignee']);
        
        // Парсим контент для PDF
        $task->parsed_content = $this->parseContentForPDF($task->content);

        // Создаем PDF с использованием DomPDF
        $pdf = Pdf::loadView('task.pdf', compact('task', 'space'));
        
        // Настройки PDF
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isPhpEnabled' => true,
            'defaultFont' => 'DejaVu Sans',
            'enable_remote' => true,
            'isRemoteEnabled' => true,
            'chroot' => public_path(),
            'tempDir' => storage_path('app/temp'),
            'fontDir' => storage_path('fonts/'),
            'fontCache' => storage_path('fonts/'),
            'logOutputFile' => storage_path('logs/dompdf.log'),
            'debugKeepTemp' => false,
            'debugCss' => false,
            'debugLayout' => false,
            'debugLayoutLines' => false,
            'debugLayoutBlocks' => false,
            'debugLayoutInline' => false,
            'debugLayoutPaddingBox' => false,
        ]);
        
        // Создаем имя файла
        $filename = 'task-' . $task->id . '-' . \Illuminate\Support\Str::slug($task->title ?? 'без-названия') . '.pdf';
        
        // Возвращаем PDF для скачивания
        return $pdf->download($filename);
    }

    /**
     * Парсит контент задачи для PDF (упрощенная версия)
     */
    private function parseContentForPDF($content)
    {
        if (!$content) {
            return '';
        }

        // Сначала обрабатываем изображения, чтобы они не потерялись при strip_tags
        $content = $this->processImagesForPDF($content);
        
        // Убираем интерактивные элементы и оставляем только безопасные теги для PDF
        $content = strip_tags($content, '<p><br><strong><b><em><i><u><h1><h2><h3><h4><h5><h6><ul><ol><li><div><span><table><tr><td><th><thead><tbody><img>');
        
        // Убираем большинство атрибутов, но оставляем src, alt, width, height для изображений
        $content = preg_replace('/\s(?!src=|alt=|width=|height=)(class|style|id|onclick|draggable|contenteditable|data-[^=]*|title)="[^"]*"/i', '', $content);
        
        // Заменяем div на p для лучшего отображения в PDF, но не трогаем блоки с изображениями
        $content = preg_replace('/<div(?![^>]*image-block)([^>]*)>/', '<p$1>', $content);
        $content = str_replace('</div>', '</p>', $content);
        
        return $content;
    }
    
    /**
     * Обработка изображений для PDF
     */
    private function processImagesForPDF($content)
    {
        // Заменяем блоки изображений на простые img теги для PDF
        $content = preg_replace_callback(
            '/<div[^>]*class="[^"]*image-block[^"]*"[^>]*>(.*?)<\/div>/s',
            function ($matches) {
                $blockContent = $matches[1];
                
                // Извлекаем изображение
                if (preg_match('/<img[^>]*src="([^"]*)"[^>]*alt="([^"]*)"[^>]*>/i', $blockContent, $imgMatches)) {
                    $src = $imgMatches[1];
                    $alt = $imgMatches[2];
                    
                    // Извлекаем подпись если есть
                    $caption = '';
                    if (preg_match('/<div[^>]*class="[^"]*image-caption[^"]*"[^>]*>(.*?)<\/div>/s', $blockContent, $captionMatches)) {
                        $caption = strip_tags(trim($captionMatches[1]));
                    }
                    
                    $result = '<div style="text-align: center; margin: 15px 0;">';
                    $result .= '<img src="' . htmlspecialchars($src) . '" alt="' . htmlspecialchars($alt) . '" style="max-width: 100%; height: auto; border: 1px solid #ddd; border-radius: 4px; padding: 4px;">';
                    
                    if (!empty($caption)) {
                        $result .= '<p style="font-size: 10pt; color: #666; margin: 5px 0 0 0; font-style: italic;">' . htmlspecialchars($caption) . '</p>';
                    }
                    
                    $result .= '</div>';
                    return $result;
                }
                
                return $matches[0]; // Возвращаем исходный контент если не удалось извлечь изображение
            },
            $content
        );
        
        return $content;
    }

    /**
     * Архивировать задачу
     */
    public function archive(Task $task)
    {
        // Проверяем права доступа к пространству задачи
        $space = $task->space;
        if (!$space->members()->where('user_id', Auth::id())->exists()) {
            abort(403, 'У вас нет доступа к этой задаче');
        }

        // Проверяем, что пользователь является создателем пространства или создателем задачи
        if ($space->created_by !== Auth::id() && $task->created_by !== Auth::id()) {
            abort(403, 'Вы можете архивировать только свои задачи или задачи в созданных вами пространствах');
        }

        // Архивируем задачу
        $task->update([
            'archived_at' => now(),
            'archived_by' => Auth::id()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Задача успешно архивирована'
        ]);
    }

    /**
     * Разархивировать задачу
     */
    public function unarchive(Task $task)
    {
        // Проверяем права доступа к пространству задачи
        $space = $task->space;
        if (!$space->members()->where('user_id', Auth::id())->exists()) {
            abort(403, 'У вас нет доступа к этой задаче');
        }

        // Проверяем, что пользователь является создателем пространства или создателем задачи
        if ($space->created_by !== Auth::id() && $task->created_by !== Auth::id()) {
            abort(403, 'Вы можете разархивировать только свои задачи или задачи в созданных вами пространствах');
        }

        // Разархивируем задачу
        
        // Если колонка задачи была удалена, переместим её в первую доступную колонку
        if ($task->column === null) {
            // Находим первую доступную колонку в пространстве
            $defaultColumn = $space->columns()->orderBy('position')->first();
            
            if ($defaultColumn) {
                $task->update([
                    'archived_at' => null,
                    'archived_by' => null,
                    'column_id' => $defaultColumn->id
                ]);
                
                Log::info('Unarchived task assigned to new column after original column was deleted', [
                    'task_id' => $task->id,
                    'original_column_id' => $task->column_id,
                    'new_column_id' => $defaultColumn->id
                ]);
            } else {
                // Если нет доступных колонок, выдаем ошибку
                return response()->json([
                    'success' => false,
                    'message' => 'Невозможно разархивировать задачу, так как нет доступных колонок в пространстве. Создайте сначала хотя бы одну колонку.'
                ], 400);
            }
        } else {
            // Колонка существует, просто разархивируем задачу
            $task->update([
                'archived_at' => null,
                'archived_by' => null
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Задача успешно разархивирована'
        ]);
    }
}
