<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\Space;
use App\Services\StorageService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class TaskViewController extends Controller
{
    protected $storageService;

    public function __construct(StorageService $storageService)
    {
        $this->middleware('auth');
        $this->storageService = $storageService;
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

        // Проверяем, есть ли уже структура A4
        $hasA4Structure = strpos($content, 'a4-page') !== false;
        
        // Сначала извлекаем все файловые ссылки
        $fileBlocks = [];
        
        // Обрабатываем SWIPER блоки
        $content = preg_replace_callback(
            '/\[SWIPER:(.*?)\]/',
            function ($matches) use (&$fileBlocks) {
                $filesData = $matches[1];
                $files = explode(';', $filesData);
                
                $blockHtml = $this->generateSwiperBlockHtml($files);
                $fileBlocks[] = $blockHtml;
                
                return '';
            },
            $content
        );
        
        // Обрабатываем FILEGRID блоки
        $content = preg_replace_callback(
            '/\[FILEGRID:(.*?)\]/',
            function ($matches) use (&$fileBlocks) {
                $filesData = $matches[1];
                $files = explode(';', $filesData);
                
                $blockHtml = $this->generateFileGridBlockHtml($files);
                $fileBlocks[] = $blockHtml;
                
                return '';
            },
            $content
        );
        
        // Обрабатываем обычные FILE блоки
        $content = preg_replace_callback(
            '/\[FILE:(.*?)\|(.*?)\|(.*?)\|(.*?)\]/',
            function ($matches) use (&$fileBlocks) {
                $url = $matches[1];
                $filename = $matches[2];
                $size = $matches[3];
                $type = $matches[4];

                $blockHtml = $this->generateFileBlockHtml($url, $filename, $size, $type);
                $fileBlocks[] = $blockHtml;
                
                return '';
            },
            $content
        );

        // Если есть структура A4 и есть файловые блоки для добавления
        if ($hasA4Structure && !empty($fileBlocks)) {
            // Находим последнюю страницу и добавляем блоки в неё
            $content = $this->insertFileBlocksIntoLastPage($content, $fileBlocks);
        } elseif (!$hasA4Structure && !empty($fileBlocks)) {
            // Если нет структуры A4, создаём её и добавляем блоки
            $content = $this->createA4StructureWithBlocks($content, $fileBlocks);
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
     * Генерирует HTML блок для файла
     */
    private function generateFileBlockHtml($url, $filename, $size, $type)
    {
        $blockId = 'block_' . time() . rand(1000, 9999);
        
        switch ($type) {
            case 'image':
                $filesData = json_encode([['url' => $url, 'name' => $filename, 'type' => 'image']]);
                return '<div class="content-block image-block" id="' . $blockId . '" data-files="' . htmlspecialchars($filesData, ENT_QUOTES, 'UTF-8') . '" draggable="false">
                            <div class="block-toolbar">
                                <button class="block-btn drag-handle" title="Перетащить блок" data-block-id="' . $blockId . '">
                                    <i class="fas fa-grip-vertical"></i>
                                </button>
                                <button class="block-btn add-more" onclick="convertToSwiperAndAddMore(\'' . $blockId . '\')" title="Добавить еще изображения">
                                    <i class="fas fa-plus"></i>
                                </button>
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
                        
                        </div>';

            case 'video':
                return '<div class="content-block video-block" id="' . $blockId . '" draggable="false">
                            <div class="block-toolbar">
                                <button class="block-btn drag-handle" title="Перетащить блок" data-block-id="' . $blockId . '">
                                    <i class="fas fa-grip-vertical"></i>
                                </button>
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
                return '<div class="content-block file-block" id="' . $blockId . '" draggable="false">
                            <div class="block-toolbar">
                                <button class="block-btn drag-handle" title="Перетащить блок" data-block-id="' . $blockId . '">
                                    <i class="fas fa-grip-vertical"></i>
                                </button>
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
    }

    /**
     * Генерирует HTML блок для документа
     */
    private function generateDocumentBlockHtml($url, $filename, $size, $extension)
    {
        $blockId = 'block_' . time() . rand(1000, 9999);
        
        // Определяем иконку по типу файла
        $iconClass = $this->getDocumentIcon($extension);
        
        return '<div class="content-block document-block" id="' . $blockId . '" draggable="false">
                    <div class="block-toolbar">
                        <button class="block-btn drag-handle" title="Перетащить блок" data-block-id="' . $blockId . '">
                            <i class="fas fa-grip-vertical"></i>
                        </button>
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
                    <div class="document-info">
                        <div class="document-icon">
                            <i class="' . $iconClass . ' fa-3x"></i>
                        </div>
                        <div class="document-details">
                            <h4 class="document-title">' . htmlspecialchars($filename) . '</h4>
                            <p class="document-size">' . htmlspecialchars($size) . '</p>
                            <div class="document-actions">
                                <a href="' . htmlspecialchars($url) . '" target="_blank" class="btn btn-primary btn-sm">
                                    <i class="fas fa-download"></i> Скачать
                                </a>
                                <a href="' . htmlspecialchars($url) . '" target="_blank" class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-eye"></i> Просмотр
                                </a>
                            </div>
                        </div>
                    </div>
                   
                </div>';
    }

    /**
     * Определяет иконку для типа документа
     */
    private function getDocumentIcon($extension)
    {
        $extension = strtolower($extension);
        
        switch ($extension) {
            case 'pdf':
                return 'fas fa-file-pdf text-danger';
            case 'doc':
            case 'docx':
                return 'fas fa-file-word text-primary';
            case 'xls':
            case 'xlsx':
                return 'fas fa-file-excel text-success';
            case 'ppt':
            case 'pptx':
                return 'fas fa-file-powerpoint text-warning';
            case 'txt':
                return 'fas fa-file-alt text-secondary';
            case 'zip':
            case 'rar':
                return 'fas fa-file-archive text-info';
            default:
                return 'fas fa-file text-muted';
        }
    }

    /**
     * Вставляет блок документа в контент
     */
    private function insertDocumentBlockIntoContent($content, $blockHtml)
    {
        // Если контент пустой, создаем базовую структуру с блоком
        if (empty($content)) {
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
                            ' . $blockHtml . '
                        </div>
                    </div>';
        }
        
        // Находим последнюю страницу и добавляем блок туда
        $lastPageContentPos = strrpos($content, '</div>');
        if ($lastPageContentPos !== false) {
            // Ищем закрывающий тег page-content
            $searchPos = $lastPageContentPos;
            while ($searchPos > 0) {
                $checkPos = strrpos($content, '<div class="page-content"', $searchPos - 1);
                if ($checkPos !== false) {
                    // Находим соответствующий закрывающий </div>
                    $openTags = 0;
                    $contentStart = strpos($content, '>', $checkPos) + 1;
                    
                    for ($i = $contentStart; $i < strlen($content); $i++) {
                        if (substr($content, $i, 4) === '<div') {
                            $openTags++;
                        } elseif (substr($content, $i, 6) === '</div>') {
                            if ($openTags === 0) {
                                // Это закрывающий тег для page-content
                                return substr($content, 0, $i) . '            ' . $blockHtml . "\n        " . substr($content, $i);
                            }
                            $openTags--;
                        }
                    }
                    break;
                }
                $searchPos = $checkPos;
            }
        }
        
        // Если не получилось найти нужное место, просто добавляем в конец
        return $content . "\n\n" . $blockHtml;
    }

    /**
     * Вставляет файловые блоки в последнюю страницу A4
     */
    private function insertFileBlocksIntoLastPage($content, $fileBlocks)
    {
        // Простой подход: находим последний </div> перед закрытием page-content
        // и вставляем блоки перед ним
        
        // Находим все page-content блоки
        $lastPageContentPos = strrpos($content, '<div class="page-content"');
        if ($lastPageContentPos !== false) {
            // Находим соответствующий закрывающий </div> для page-content
            $searchStart = $lastPageContentPos;
            $openTags = 0;
            $pageContentStart = strpos($content, '>', $lastPageContentPos) + 1;
            
            for ($i = $pageContentStart; $i < strlen($content); $i++) {
                if (substr($content, $i, 4) === '<div') {
                    $openTags++;
                } elseif (substr($content, $i, 6) === '</div>') {
                    if ($openTags === 0) {
                        // Это закрывающий тег для page-content
                        $insertPosition = $i;
                        
                        // Удаляем empty-page если есть
                        $beforeInsert = substr($content, $pageContentStart, $insertPosition - $pageContentStart);
                        $beforeInsert = preg_replace('/<div class="empty-page">.*?<\/div>/s', '', $beforeInsert);
                        
                        // Вставляем блоки
                        $newContent = substr($content, 0, $pageContentStart) . 
                                     trim($beforeInsert) . 
                                     implode('', $fileBlocks) . 
                                     substr($content, $insertPosition);
                        
                        return $newContent;
                    } else {
                        $openTags--;
                    }
                }
            }
        }
        
        return $content;
    }

    /**
     * Создаёт структуру A4 с блоками
     */
    private function createA4StructureWithBlocks($content, $fileBlocks)
    {
        $textBlockId = 'block_' . time() . rand(1000, 9999);
        
        // Если есть текстовый контент, создаём текстовый блок
        $textBlock = '';
        if (!empty(trim($content))) {
            $textBlock = '<div class="content-block text-block" id="' . $textBlockId . '" draggable="false">
                            <div class="block-toolbar">
                                <button class="block-btn drag-handle" title="Перетащить блок" data-block-id="' . $textBlockId . '">
                                    <i class="fas fa-grip-vertical"></i>
                                </button>
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
                            <div class="text-content" contenteditable="true">' . htmlspecialchars($content) . '</div>
                        </div>';
        }
        
        return '<div class="a4-page" data-page="1" draggable="false">
                    <div class="page-header">
                        <span class="page-number">Лист 1</span>
                        <div class="page-actions">
                            <button class="page-btn page-drag-handle" data-page-number="1" title="Перетащить лист">
                                <i class="fas fa-grip-vertical"></i>
                            </button>
                            <button class="page-btn" onclick="deletePage(this)" title="Удалить лист">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="page-content" onclick="addContentBlock(event)">
                        ' . $textBlock . implode('', $fileBlocks) . '
                    </div>
                </div>';
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
        try {
            // Проверяем права доступа к пространству задачи
            $space = $task->space;
            if (!$space->members()->where('user_id', Auth::id())->exists()) {
                abort(403, 'У вас нет доступа к этой задаче');
            }

            $request->validate([
                'content' => 'required|string',
            ]);

            $newContent = $request->input('content');
            
            // Проверяем размер контента (максимум 16MB в текстовом виде)
            if (strlen($newContent) > 16 * 1024 * 1024) {
                return response()->json([
                    'success' => false,
                    'message' => 'Контент слишком большой. Максимальный размер: 16MB'
                ], 413, [], JSON_UNESCAPED_UNICODE);
            }

            // Получаем старый контент для учета изменения памяти
            $oldContent = $task->content ?? '';

            // Обновляем задачу
            $task->update([
                'content' => $newContent
            ]);

            // Учитываем изменение размера контента (только если система учета памяти инициализирована)
            try {
                $user = Auth::user();
                if ($user->storage_limit_mb !== null) {
                    $this->storageService->trackTaskContentUpdate($user, $task, $oldContent, $newContent);
                }
            } catch (\Exception $storageError) {
                Log::warning('Storage tracking failed: ' . $storageError->getMessage(), [
                    'task_id' => $task->id,
                    'user_id' => Auth::id()
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Контент сохранен'
            ], 200, [], JSON_UNESCAPED_UNICODE);
            
        } catch (\Exception $e) {
            Log::error('Error updating task content: ' . $e->getMessage(), [
                'task_id' => $task->id,
                'user_id' => Auth::id(),
                'content_length' => strlen($request->input('content', '')),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Ошибка при сохранении контента: ' . $e->getMessage()
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
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
            
            // Создаем безопасное имя файла (только ASCII символы)
            $safeName = preg_replace('/[^a-zA-Z0-9\.\-_]/', '_', $originalName);
            $fileName = time() . '_' . $safeName;
            
            // Создаем папку для задачи если её нет
            $taskFolder = "tasks/{$task->id}";
            
            // Сохраняем файл в storage/app/public
            $filePath = $file->storeAs($taskFolder, $fileName, 'public');
            
            // Генерируем URL для доступа к файлу
            $fileUrl = asset('storage/' . $filePath);
            
            // Определяем тип файла
            $isImage = in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif']);
            $isVideo = in_array(strtolower($extension), ['mp4', 'avi', 'mkv', 'mov', 'wmv', 'flv', 'webm']);
            
            // Форматируем размер файла
            $formattedSize = $this->formatFileSize($size);
            
            // Получаем текущий контент
            $currentContent = $task->content ?? '';
            
            // Добавляем файл к контенту простым способом
            $currentContent = $task->content ?? '';
            
            // Добавляем ссылку на файл в формате [FILE:url|name|size|type]
            $fileLink = "\n\n[FILE:{$fileUrl}|{$originalName}|{$formattedSize}|" . ($isImage ? 'image' : ($isVideo ? 'video' : 'file')) . "]\n\n";
            $newContent = $currentContent . $fileLink;
            
            $task->update(['content' => $newContent]);

            // Учитываем память за загрузку файла (только если система учета памяти инициализирована)
            try {
                $user = Auth::user();
                if ($user->storage_limit_mb !== null) {
                    $this->storageService->trackFileUpload($user, $file, 'task', $task->id);
                }
            } catch (\Exception $storageError) {
                Log::warning('Storage tracking failed: ' . $storageError->getMessage(), [
                    'task_id' => $task->id,
                    'user_id' => Auth::id()
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Файл успешно загружен',
                'file_url' => $fileUrl,
                'file_name' => $originalName,
                'file_size' => $formattedSize,
                'file_type' => $isImage ? 'image' : ($isVideo ? 'video' : 'file')
            ], 200, [], JSON_UNESCAPED_UNICODE);

        } catch (\Exception $e) {
            Log::error('Error uploading file: ' . $e->getMessage(), [
                'task_id' => $task->id,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Ошибка при загрузке файла: ' . $e->getMessage()
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Загрузка документа к задаче (создает отдельный блок)
     */
    public function uploadDocument(Request $request, Task $task)
    {
        try {
            // Проверяем права доступа к задаче через пространство
            $space = $task->space;
            if (!$space->members()->where('user_id', Auth::id())->exists()) {
                abort(403, 'У вас нет доступа к этой задаче');
            }

            $request->validate([
                'file' => 'required|file|max:50000|mimes:pdf,doc,docx,txt,zip,rar,xls,xlsx,ppt,pptx'
            ]);

            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $size = $file->getSize();
            
            // Создаем безопасное имя файла (только ASCII символы)
            $safeName = preg_replace('/[^a-zA-Z0-9\.\-_]/', '_', $originalName);
            $fileName = time() . '_' . $safeName;
            
            // Создаем папку для задачи если её нет
            $taskFolder = "tasks/{$task->id}";
            
            // Сохраняем файл в storage/app/public
            $filePath = $file->storeAs($taskFolder, $fileName, 'public');
            
            // Генерируем URL для доступа к файлу
            $fileUrl = asset('storage/' . $filePath);
            
            // Форматируем размер файла
            $formattedSize = $this->formatFileSize($size);
            
            // Генерируем HTML блок для документа
            $blockHtml = $this->generateDocumentBlockHtml($fileUrl, $originalName, $formattedSize, $extension);
            
            // Добавляем блок к контенту задачи
            $currentContent = $task->content ?? '';
            $newContent = $this->insertDocumentBlockIntoContent($currentContent, $blockHtml);
            
            $task->update(['content' => $newContent]);

            // Учитываем память за загрузку файла
            try {
                $user = Auth::user();
                if ($user->storage_limit_mb !== null) {
                    $this->storageService->trackFileUpload($user, $file, 'task', $task->id);
                }
            } catch (\Exception $storageError) {
                Log::warning('Storage tracking failed: ' . $storageError->getMessage(), [
                    'task_id' => $task->id,
                    'user_id' => Auth::id()
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Документ успешно загружен',
                'block_html' => $blockHtml,
                'file_url' => $fileUrl,
                'file_name' => $originalName,
                'file_size' => $formattedSize
            ], 200, [], JSON_UNESCAPED_UNICODE);

        } catch (\Exception $e) {
            Log::error('Error uploading document: ' . $e->getMessage(), [
                'task_id' => $task->id,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Ошибка при загрузке документа: ' . $e->getMessage()
            ], 500, [], JSON_UNESCAPED_UNICODE);
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

        // Получаем старое и новое название для учета изменения памяти
        $oldTitle = $task->title ?? '';
        $newTitle = $request->input('title');

        // Обновляем задачу
        $task->update([
            'title' => $newTitle
        ]);

        // Учитываем изменение размера названия
        $this->storageService->trackTaskTitleUpdate(Auth::user(), $task, $oldTitle, $newTitle);

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
        
        // Добавляем ссылку на задачу для PDF
        $taskUrl = url('/tasks/' . $task->id);

        // Создаем PDF с использованием DomPDF
        $pdf = Pdf::loadView('task.pdf', compact('task', 'space', 'taskUrl'));
        
        // Настройки PDF
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isPhpEnabled' => true,
            'defaultFont' => 'DejaVu Sans',
            'enable_remote' => true,
            'isRemoteEnabled' => true,
            'enable_css_float' => true,
            'enable_html5_parser' => true,
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
            'enable_javascript' => false,
            'dpi' => 96,
            'defaultPaperSize' => 'a4',
            'defaultPaperOrientation' => 'portrait',
        ]);
        
        // Создаем имя файла с добавлением ссылки на задачу в название
        $taskUrl = url('/tasks/' . $task->id);
        $baseFilename = 'task-' . $task->id . '-' . \Illuminate\Support\Str::slug($task->title ?? 'без-названия');
        $filename = $baseFilename . '-' . urlencode($taskUrl) . '.pdf';
        
        // Если название слишком длинное, сокращаем его
        if (strlen($filename) > 200) {
            $filename = $baseFilename . '-link-' . $task->id . '.pdf';
        }
        
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

        // Сначала обрабатываем блоки множественных файлов
        $content = $this->processMultipleFilesForPDF($content);

        // Сначала обрабатываем изображения, чтобы они не потерялись при strip_tags
        $content = $this->processImagesForPDF($content);
        
        // Обрабатываем документные блоки, чтобы сохранить ссылки для PDF
        $content = $this->processDocumentBlocksForPDF($content);
        
        // Обрабатываем любые ссылки, делая их абсолютными
        $content = preg_replace_callback(
            '/<a[^>]*href="([^"]*)"([^>]*)>([^<]*)<\/a>/i',
            function ($matches) {
                $href = $matches[1];
                $attributes = $matches[2];
                $text = $matches[3];
                
                // Делаем ссылку абсолютной если она относительная
                if (!filter_var($href, FILTER_VALIDATE_URL) && !preg_match('/^https?:\/\//', $href)) {
                    $href = url($href);
                }
                
                return '<a href="' . htmlspecialchars($href) . '"' . $attributes . ' style="color: #007bff; text-decoration: underline; font-weight: bold;">' . $text . '</a>';
            },
            $content
        );
        
        // Убираем интерактивные элементы и оставляем только безопасные теги для PDF, включая ссылки
        $content = strip_tags($content, '<p><br><strong><b><em><i><u><h1><h2><h3><h4><h5><h6><ul><ol><li><div><span><table><tr><td><th><thead><tbody><img><a>');
        
        // Убираем большинство атрибутов, но оставляем важные для PDF
        $content = preg_replace('/\s(?!src=|alt=|width=|height=|href=|target=|style=)(class|id|onclick|draggable|contenteditable|data-[^=]*|title)="[^"]*"/i', '', $content);
        
        // Заменяем div на p для лучшего отображения в PDF, но не трогаем блоки с изображениями и документами
        $content = preg_replace('/<div(?![^>]*(?:image-block|document-block|file-block|style=))([^>]*)>/', '<p$1>', $content);
        $content = str_replace('</div>', '</p>', $content);
        
        // Добавляем стили для разрывов страниц для больших блоков
        $content = preg_replace('/<div([^>]*style="[^"]*border[^"]*")([^>]*)>/', '<div$1$2 style="page-break-inside: avoid;">', $content);
        
        return $content;
    }
    
    /**
     * Обрабатывает множественные файлы (SWIPER и FILEGRID) для PDF
     */
    private function processMultipleFilesForPDF($content)
    {
        // Обрабатываем SWIPER блоки 
        $content = preg_replace_callback(
            '/\[SWIPER:(.*?)\]/',
            function ($matches) {
                $filesData = $matches[1];
                $files = explode(';', $filesData);
                
                return $this->generateMultipleFilesForPDF($files, 'Слайдер изображений');
            },
            $content
        );
        
        // Обрабатываем FILEGRID блоки
        $content = preg_replace_callback(
            '/\[FILEGRID:(.*?)\]/',
            function ($matches) {
                $filesData = $matches[1];
                $files = explode(';', $filesData);
                
                return $this->generateMultipleFilesForPDF($files, 'Сетка файлов');
            },
            $content
        );
        
        // Обрабатываем обычные FILE блоки в формате [FILE:url|name|size|type]
        $content = preg_replace_callback(
            '/\[FILE:(.*?)\|(.*?)\|(.*?)\|(.*?)\]/',
            function ($matches) {
                $url = $matches[1];
                $filename = $matches[2];
                $size = $matches[3];
                $type = $matches[4];
                
                return $this->generateSingleFileForPDF($url, $filename, $size, $type);
            },
            $content
        );
        
        return $content;
    }
    
    /**
     * Генерирует блок множественных файлов для PDF
     */
    private function generateMultipleFilesForPDF($files, $blockTitle)
    {
        $result = '<div style="border: 1px solid #ddd; border-radius: 5px; padding: 15px; margin: 15px 0; background: #f8f9fa; page-break-inside: avoid;">';
        $result .= '<h4 style="margin: 0 0 15px 0; color: #495057; font-size: 14pt;">' . htmlspecialchars($blockTitle) . '</h4>';
        
        foreach ($files as $fileData) {
            $fileData = trim($fileData);
            if (empty($fileData)) continue;
            
            // Парсим данные файла (формат: url|name|size|type)
            $fileParts = explode('|', $fileData);
            if (count($fileParts) >= 4) {
                $url = $fileParts[0];
                $filename = $fileParts[1];
                $size = $fileParts[2];
                $type = $fileParts[3];
                
                // Делаем ссылку абсолютной
                if (!filter_var($url, FILTER_VALIDATE_URL) && !preg_match('/^https?:\/\//', $url)) {
                    $url = url($url);
                }
                
                if ($type === 'image') {
                    // Для изображений показываем миниатюру и ссылку с полной информацией
                    $result .= '<div style="margin: 10px 0; border: 1px solid #eee; padding: 10px; border-radius: 3px;">';
                    $result .= '<img src="' . htmlspecialchars($url) . '" alt="' . htmlspecialchars($filename) . '" style="max-width: 150px; max-height: 100px; border: 1px solid #ddd; border-radius: 3px; float: left; margin-right: 10px;">';
                    $result .= '<div style="overflow: hidden;">';
                    $result .= '<p style="margin: 0 0 5px 0; font-weight: bold;"><a href="' . htmlspecialchars($url) . '" target="_blank" style="color: #007bff; text-decoration: underline;">' . htmlspecialchars($filename) . '</a></p>';
                    $result .= '<p style="margin: 0 0 3px 0; font-size: 10pt; color: #666;">Размер: ' . htmlspecialchars($size) . '</p>';
                    $result .= '<p style="margin: 0; font-size: 8pt; color: #999; word-break: break-all;">Ссылка: ' . htmlspecialchars($url) . '</p>';
                    $result .= '</div>';
                    $result .= '<div style="clear: both;"></div>';
                    $result .= '</div>';
                } else {
                    // Для других файлов показываем иконку и ссылку с подробной информацией
                    $extension = pathinfo($filename, PATHINFO_EXTENSION);
                    $iconText = $this->getDocumentIconForPDF($extension);
                    
                    $result .= '<div style="margin: 5px 0; padding: 8px; border: 1px solid #eee; border-radius: 3px;">';
                    $result .= '<div style="margin-bottom: 5px;">';
                    $result .= '<span style="display: inline-block; width: 25px; text-align: center; background: #007bff; color: white; border-radius: 3px; padding: 2px 4px; font-size: 9pt; margin-right: 8px;">' . $iconText . '</span>';
                    $result .= '<a href="' . htmlspecialchars($url) . '" target="_blank" style="color: #007bff; text-decoration: underline; font-weight: bold;">';
                    $result .= htmlspecialchars($filename) . ' (' . htmlspecialchars($size) . ')';
                    $result .= '</a>';
                    $result .= '</div>';
                    $result .= '<p style="margin: 0; font-size: 8pt; color: #999; word-break: break-all;">Прямая ссылка: ' . htmlspecialchars($url) . '</p>';
                    $result .= '</div>';
                }
            }
        }
        
        $result .= '</div>';
        return $result;
    }
    
    /**
     * Генерирует блок одиночного файла для PDF
     */
    private function generateSingleFileForPDF($url, $filename, $size, $type)
    {
        // Делаем ссылку абсолютной
        if (!filter_var($url, FILTER_VALIDATE_URL) && !preg_match('/^https?:\/\//', $url)) {
            $url = url($url);
        }
        
        if ($type === 'image') {
            // Для изображений показываем само изображение с ссылкой для скачивания
            $result = '<div style="text-align: center; margin: 15px 0; page-break-inside: avoid;">';
            $result .= '<img src="' . htmlspecialchars($url) . '" alt="' . htmlspecialchars($filename) . '" style="max-width: 100%; height: auto; max-height: 400px; border: 1px solid #ddd; border-radius: 4px; padding: 4px;">';
            $result .= '<p style="font-size: 10pt; color: #666; margin: 5px 0 0 0; font-style: italic;">' . htmlspecialchars($filename) . ' (' . htmlspecialchars($size) . ')</p>';
            $result .= '<p style="font-size: 9pt; color: #007bff; margin: 5px 0 0 0;">';
            $result .= '<a href="' . htmlspecialchars($url) . '" target="_blank" style="color: #007bff; text-decoration: underline;">Скачать изображение</a>';
            $result .= '</p>';
            $result .= '<p style="font-size: 8pt; color: #999; margin: 2px 0 0 0; word-break: break-all;">' . htmlspecialchars($url) . '</p>';
            $result .= '</div>';
            return $result;
        } elseif ($type === 'video') {
            // Для видео показываем ссылку с иконкой и полной ссылкой
            $result = '<div style="border: 1px solid #ddd; border-radius: 5px; padding: 15px; margin: 15px 0; background: #f8f9fa; page-break-inside: avoid;">';
            $result .= '<p style="margin: 0; font-weight: bold;">';
            $result .= '<span style="display: inline-block; width: 30px; text-align: center; background: #dc3545; color: white; border-radius: 3px; padding: 2px 4px; font-size: 10pt; margin-right: 8px;">VID</span>';
            $result .= '<a href="' . htmlspecialchars($url) . '" target="_blank" style="color: #007bff; text-decoration: underline; font-weight: bold;">';
            $result .= htmlspecialchars($filename) . ' (' . htmlspecialchars($size) . ')';
            $result .= '</a>';
            $result .= '</p>';
            $result .= '<p style="margin: 5px 0 0 0; font-size: 10pt; color: #666;">Видеофайл - ссылка для просмотра и скачивания</p>';
            $result .= '<p style="margin: 5px 0 0 0; font-size: 8pt; color: #999; word-break: break-all;">Ссылка: ' . htmlspecialchars($url) . '</p>';
            $result .= '</div>';
            return $result;
        } else {
            // Для других файлов показываем как документ с подробной информацией
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
            $iconText = $this->getDocumentIconForPDF($extension);
            
            $result = '<div style="border: 1px solid #ddd; border-radius: 5px; padding: 15px; margin: 15px 0; background: #f8f9fa; page-break-inside: avoid;">';
            $result .= '<p style="margin: 0; font-weight: bold;">';
            $result .= '<span style="display: inline-block; width: 30px; text-align: center; background: #007bff; color: white; border-radius: 3px; padding: 2px 4px; font-size: 10pt; margin-right: 8px;">' . $iconText . '</span>';
            $result .= '<a href="' . htmlspecialchars($url) . '" target="_blank" style="color: #007bff; text-decoration: underline; font-weight: bold;">';
            $result .= htmlspecialchars($filename) . ' (' . htmlspecialchars($size) . ')';
            $result .= '</a>';
            $result .= '</p>';
            $result .= '<p style="margin: 5px 0 0 0; font-size: 10pt; color: #666;">Файл для скачивания - кликните по ссылке выше</p>';
            $result .= '<p style="margin: 5px 0 0 0; font-size: 8pt; color: #999; word-break: break-all;">Прямая ссылка: ' . htmlspecialchars($url) . '</p>';
            $result .= '</div>';
            return $result;
        }
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
                    
                    // Делаем ссылку абсолютной если она относительная
                    if (!filter_var($src, FILTER_VALIDATE_URL) && !preg_match('/^https?:\/\//', $src)) {
                        $src = url($src);
                    }
                    
                    // Извлекаем подпись если есть
                    $caption = '';
                    if (preg_match('/<div[^>]*class="[^"]*image-caption[^"]*"[^>]*>(.*?)<\/div>/s', $blockContent, $captionMatches)) {
                        $caption = strip_tags(trim($captionMatches[1]));
                    } elseif (preg_match('/<p[^>]*style="[^"]*italic[^"]*"[^>]*>(.*?)<\/p>/s', $blockContent, $captionMatches)) {
                        $caption = strip_tags(trim($captionMatches[1]));
                    }
                    
                    $result = '<div style="text-align: center; margin: 15px 0; page-break-inside: avoid;">';
                    $result .= '<img src="' . htmlspecialchars($src) . '" alt="' . htmlspecialchars($alt) . '" style="max-width: 100%; height: auto; max-height: 400px; border: 1px solid #ddd; border-radius: 4px; padding: 4px;">';
                    
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
        
        // Обрабатываем также одиночные изображения вне блоков
        $content = preg_replace_callback(
            '/<img[^>]*src="([^"]*)"[^>]*alt="([^"]*)"[^>]*>/i',
            function ($matches) {
                $src = $matches[1];
                $alt = $matches[2];
                
                // Делаем ссылку абсолютной если она относительная
                if (!filter_var($src, FILTER_VALIDATE_URL) && !preg_match('/^https?:\/\//', $src)) {
                    $src = url($src);
                }
                
                return '<img src="' . htmlspecialchars($src) . '" alt="' . htmlspecialchars($alt) . '" style="max-width: 100%; height: auto; max-height: 400px; border: 1px solid #ddd; border-radius: 4px; padding: 4px; display: block; margin: 10px auto;">';
            },
            $content
        );
        
        return $content;
    }
    
    /**
     * Обработка документных блоков для PDF
     */
    private function processDocumentBlocksForPDF($content)
    {
        // Заменяем блоки документов на простые ссылки с иконками для PDF
        $content = preg_replace_callback(
            '/<div[^>]*class="[^"]*document-block[^"]*"[^>]*>(.*?)<\/div>/s',
            function ($matches) {
                $blockContent = $matches[1];
                
                // Извлекаем любые ссылки для скачивания - более универсальный подход
                $downloadUrl = '';
                $filename = 'Документ';
                $fileSize = '';
                
                // Ищем основную ссылку на скачивание
                if (preg_match('/<a[^>]*href="([^"]*)"[^>]*(?:class="[^"]*btn[^"]*btn-primary[^"]*"|title="[^"]*"[^>]*><i[^>]*fa-download[^>]*)|>.*?(?:Скачать|Download)/is', $blockContent, $linkMatches)) {
                    $downloadUrl = $linkMatches[1];
                } elseif (preg_match('/<a[^>]*href="([^"]*)"[^>]*target="_blank"[^>]*>.*?<i[^>]*fa-download[^>]*>/is', $blockContent, $linkMatches)) {
                    $downloadUrl = $linkMatches[1];
                } elseif (preg_match('/<a[^>]*href="([^"]*)"[^>]*>/is', $blockContent, $linkMatches)) {
                    // Если не нашли специфичную кнопку скачивания, берем первую ссылку
                    $downloadUrl = $linkMatches[1];
                }
                
                // Извлекаем название файла из h4 или из ссылки
                if (preg_match('/<h4[^>]*class="[^"]*document-title[^"]*"[^>]*>(.*?)<\/h4>/s', $blockContent, $titleMatches)) {
                    $filename = strip_tags(trim($titleMatches[1]));
                } elseif (preg_match('/<a[^>]*>([^<]*)<\/a>/s', $blockContent, $titleMatches)) {
                    $filename = strip_tags(trim($titleMatches[1]));
                }
                
                // Извлекаем размер файла
                if (preg_match('/<p[^>]*class="[^"]*document-size[^"]*"[^>]*>(.*?)<\/p>/s', $blockContent, $sizeMatches)) {
                    $fileSize = ' (' . strip_tags(trim($sizeMatches[1])) . ')';
                } elseif (preg_match('/\(([^)]*(?:KB|MB|GB|байт|Кб|Мб|Гб)[^)]*)\)/i', $blockContent, $sizeMatches)) {
                    $fileSize = ' (' . $sizeMatches[1] . ')';
                }
                
                // Определяем иконку файла
                $extension = pathinfo($filename, PATHINFO_EXTENSION);
                $iconText = $this->getDocumentIconForPDF($extension);
                
                // Убеждаемся, что у нас есть абсолютная ссылка для PDF
                if (!empty($downloadUrl)) {
                    // Если ссылка относительная, делаем её абсолютной
                    if (!filter_var($downloadUrl, FILTER_VALIDATE_URL)) {
                        $downloadUrl = url($downloadUrl);
                    }
                    
                    // Создаем блок для PDF с кликабельной ссылкой
                    $result = '<div style="border: 1px solid #ddd; border-radius: 5px; padding: 15px; margin: 15px 0; background: #f8f9fa; page-break-inside: avoid;">';
                    $result .= '<p style="margin: 0; font-weight: bold;">';
                    $result .= '<span style="display: inline-block; width: 30px; text-align: center; background: #007bff; color: white; border-radius: 3px; padding: 2px 4px; font-size: 10pt; margin-right: 8px;">' . $iconText . '</span>';
                    $result .= '<a href="' . htmlspecialchars($downloadUrl) . '" target="_blank" style="color: #007bff; text-decoration: underline; font-weight: bold;">';
                    $result .= htmlspecialchars($filename) . htmlspecialchars($fileSize);
                    $result .= '</a>';
                    $result .= '</p>';
                    $result .= '<p style="margin: 5px 0 0 0; font-size: 10pt; color: #666;">Ссылка для скачивания: ' . htmlspecialchars($downloadUrl) . '</p>';
                    $result .= '</div>';
                    
                    return $result;
                }
                
                return $matches[0]; // Возвращаем исходный контент если не удалось извлечь ссылку
            },
            $content
        );
        
        // Также обрабатываем простые файловые блоки
        $content = preg_replace_callback(
            '/<div[^>]*class="[^"]*file-block[^"]*"[^>]*>(.*?)<\/div>/s',
            function ($matches) {
                $blockContent = $matches[1];
                
                // Извлекаем ссылку и информацию о файле
                if (preg_match('/<a[^>]*href="([^"]*)"[^>]*[^>]*>([^<]*)<\/a>/s', $blockContent, $linkMatches)) {
                    $downloadUrl = $linkMatches[1];
                    $filename = strip_tags(trim($linkMatches[2]));
                    
                    // Извлекаем размер файла
                    $fileSize = '';
                    if (preg_match('/<p[^>]*>([^<]*(?:KB|MB|GB|байт|Кб|Мб|Гб)[^<]*)<\/p>/i', $blockContent, $sizeMatches)) {
                        $fileSize = ' (' . $sizeMatches[1] . ')';
                    }
                    
                    // Определяем иконку файла
                    $extension = pathinfo($filename, PATHINFO_EXTENSION);
                    $iconText = $this->getDocumentIconForPDF($extension);
                    
                    // Убеждаемся, что у нас есть абсолютная ссылка для PDF
                    if (!filter_var($downloadUrl, FILTER_VALIDATE_URL)) {
                        $downloadUrl = url($downloadUrl);
                    }
                    
                    // Создаем блок для PDF
                    $result = '<div style="border: 1px solid #ddd; border-radius: 5px; padding: 15px; margin: 15px 0; background: #f8f9fa; page-break-inside: avoid;">';
                    $result .= '<p style="margin: 0; font-weight: bold;">';
                    $result .= '<span style="display: inline-block; width: 30px; text-align: center; background: #007bff; color: white; border-radius: 3px; padding: 2px 4px; font-size: 10pt; margin-right: 8px;">' . $iconText . '</span>';
                    $result .= '<a href="' . htmlspecialchars($downloadUrl) . '" target="_blank" style="color: #007bff; text-decoration: underline; font-weight: bold;">';
                    $result .= htmlspecialchars($filename) . htmlspecialchars($fileSize);
                    $result .= '</a>';
                    $result .= '</p>';
                    $result .= '<p style="margin: 5px 0 0 0; font-size: 10pt; color: #666;">Ссылка для скачивания: ' . htmlspecialchars($downloadUrl) . '</p>';
                    $result .= '</div>';
                    
                    return $result;
                }
                
                return $matches[0];
            },
            $content
        );
        
        return $content;
    }
    
    /**
     * Получает текстовую иконку для документа в PDF
     */
    private function getDocumentIconForPDF($extension)
    {
        $extension = strtolower($extension);
        
        switch ($extension) {
            case 'pdf':
                return 'PDF';
            case 'doc':
            case 'docx':
                return 'DOC';
            case 'xls':
            case 'xlsx':
                return 'XLS';
            case 'ppt':
            case 'pptx':
                return 'PPT';
            case 'txt':
                return 'TXT';
            case 'zip':
            case 'rar':
                return 'ZIP';
            default:
                return 'FILE';
        }
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

    /**
     * Загрузка множественных файлов
     */
    public function uploadMultiple(Request $request, Task $task)
    {
        try {
            // Проверяем права доступа к задаче через пространство
            $space = $task->space;
            if (!$space->members()->where('user_id', Auth::id())->exists()) {
                abort(403, 'У вас нет доступа к этой задаче');
            }

            $request->validate([
                'files.*' => 'required|file|max:50000|mimes:jpg,jpeg,png,gif,pdf,doc,docx,mp4,avi,mkv,mov,wmv,flv,webm,txt,zip,rar,xls,xlsx,ppt,pptx',
                'type' => 'required|in:image,document'
            ]);

            $files = $request->file('files');
            $type = $request->input('type');
            $uploadedFiles = [];

            if (!$files || !is_array($files)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Не выбраны файлы для загрузки'
                ], 400);
            }

            // Создаем папку для задачи если её нет
            $taskFolder = "tasks/{$task->id}";

            foreach ($files as $file) {
                $originalName = $file->getClientOriginalName();
                $extension = $file->getClientOriginalExtension();
                $size = $file->getSize();
                
                // Создаем безопасное имя файла
                $safeName = preg_replace('/[^a-zA-Z0-9\.\-_]/', '_', $originalName);
                $fileName = time() . '_' . uniqid() . '_' . $safeName;
                
                // Сохраняем файл
                $filePath = $file->storeAs($taskFolder, $fileName, 'public');
                $fileUrl = asset('storage/' . $filePath);
                
                // Определяем тип файла
                $isImage = in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif']);
                $isVideo = in_array(strtolower($extension), ['mp4', 'avi', 'mkv', 'mov', 'wmv', 'flv', 'webm']);
                
                $uploadedFiles[] = [
                    'url' => $fileUrl,
                    'name' => $originalName,
                    'size' => $size,
                    'type' => $isImage ? 'image' : ($isVideo ? 'video' : 'document'),
                    'extension' => $extension
                ];

                // Учитываем память за загрузку файла
                try {
                    $user = Auth::user();
                    if ($user->storage_limit_mb !== null) {
                        $this->storageService->trackFileUpload($user, $file, 'task', $task->id);
                    }
                } catch (\Exception $storageError) {
                    Log::warning('Storage tracking failed: ' . $storageError->getMessage());
                }
            }

            // Добавляем файлы к контенту задачи
            $currentContent = $task->content ?? '';
            
            if (count($uploadedFiles) === 1) {
                // Один файл - добавляем как обычную ссылку
                $file = $uploadedFiles[0];
                $formattedSize = $this->formatFileSize($file['size']);
                $fileLink = "\n\n[FILE:{$file['url']}|{$file['name']}|{$formattedSize}|{$file['type']}]\n\n";
                $newContent = $currentContent . $fileLink;
            } else {
                // Множественные файлы - добавляем как блок
                if ($type === 'image') {
                    $fileLinks = array_map(function($file) {
                        return "{$file['url']}|{$file['name']}|{$file['type']}";
                    }, $uploadedFiles);
                    $blockContent = "\n\n[SWIPER:" . implode(';', $fileLinks) . "]\n\n";
                } else {
                    $fileLinks = array_map(function($file) {
                        $formattedSize = $this->formatFileSize($file['size']);
                        return "{$file['url']}|{$file['name']}|{$formattedSize}|{$file['type']}";
                    }, $uploadedFiles);
                    $blockContent = "\n\n[FILEGRID:" . implode(';', $fileLinks) . "]\n\n";
                }
                $newContent = $currentContent . $blockContent;
            }
            
            $task->update(['content' => $newContent]);

            return response()->json([
                'success' => true,
                'message' => 'Файлы успешно загружены',
                'files' => $uploadedFiles,
                'count' => count($uploadedFiles)
            ], 200, [], JSON_UNESCAPED_UNICODE);

        } catch (\Exception $e) {
            Log::error('Error uploading multiple files: ' . $e->getMessage(), [
                'task_id' => $task->id,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Ошибка при загрузке файлов: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Добавление файлов к существующему блоку
     */
    public function addToBlock(Request $request, Task $task)
    {
        try {
            // Проверяем права доступа к задаче через пространство
            $space = $task->space;
            if (!$space->members()->where('user_id', Auth::id())->exists()) {
                abort(403, 'У вас нет доступа к этой задаче');
            }

            $request->validate([
                'files.*' => 'required|file|max:50000|mimes:jpg,jpeg,png,gif,pdf,doc,docx,mp4,avi,mkv,mov,wmv,flv,webm,txt,zip,rar,xls,xlsx,ppt,pptx',
                'type' => 'required|in:image,document',
                'block_id' => 'required|string'
            ]);

            $files = $request->file('files');
            $type = $request->input('type');
            $blockId = $request->input('block_id');
            $uploadedFiles = [];

            if (!$files || !is_array($files)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Не выбраны файлы для загрузки'
                ], 400);
            }

            // Создаем папку для задачи если её нет
            $taskFolder = "tasks/{$task->id}";

            foreach ($files as $file) {
                $originalName = $file->getClientOriginalName();
                $extension = $file->getClientOriginalExtension();
                $size = $file->getSize();
                
                // Создаем безопасное имя файла
                $safeName = preg_replace('/[^a-zA-Z0-9\.\-_]/', '_', $originalName);
                $fileName = time() . '_' . uniqid() . '_' . $safeName;
                
                // Сохраняем файл
                $filePath = $file->storeAs($taskFolder, $fileName, 'public');
                $fileUrl = asset('storage/' . $filePath);
                
                // Определяем тип файла
                $isImage = in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif']);
                $isVideo = in_array(strtolower($extension), ['mp4', 'avi', 'mkv', 'mov', 'wmv', 'flv', 'webm']);
                
                $uploadedFiles[] = [
                    'url' => $fileUrl,
                    'name' => $originalName,
                    'size' => $size,
                    'type' => $isImage ? 'image' : ($isVideo ? 'video' : 'document'),
                    'extension' => $extension
                ];

                // Учитываем память за загрузку файла
                try {
                    $user = Auth::user();
                    if ($user->storage_limit_mb !== null) {
                        $this->storageService->trackFileUpload($user, $file, 'task', $task->id);
                    }
                } catch (\Exception $storageError) {
                    Log::warning('Storage tracking failed: ' . $storageError->getMessage());
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Файлы успешно добавлены',
                'files' => $uploadedFiles,
                'block_id' => $blockId
            ], 200, [], JSON_UNESCAPED_UNICODE);

        } catch (\Exception $e) {
            Log::error('Error adding files to block: ' . $e->getMessage(), [
                'task_id' => $task->id,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Ошибка при добавлении файлов: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Генерирует HTML для swiper блока с множественными изображениями
     */
    private function generateSwiperBlockHtml($files)
    {
        $blockId = 'swiper_' . time() . rand(1000, 9999);
        $filesArray = [];
        
        foreach ($files as $fileData) {
            $parts = explode('|', $fileData);
            if (count($parts) >= 3) {
                $filesArray[] = [
                    'url' => $parts[0],
                    'name' => $parts[1],
                    'type' => $parts[2] ?? 'image'
                ];
            }
        }
        
        if (empty($filesArray)) {
            return '';
        }
        
        $slidesHtml = '';
        foreach ($filesArray as $index => $file) {
            if ($file['type'] === 'video') {
                $slidesHtml .= '<div class="swiper-slide" data-index="' . $index . '">
                                    <video src="' . htmlspecialchars($file['url']) . '" controls></video>
                                </div>';
            } else {
                $slidesHtml .= '<div class="swiper-slide" data-index="' . $index . '">
                                    <img src="' . htmlspecialchars($file['url']) . '" 
                                         alt="' . htmlspecialchars($file['name']) . '" 
                                         onclick="showImageModal(\'' . htmlspecialchars($file['url']) . '\', \'' . htmlspecialchars($file['name']) . '\')">
                                </div>';
            }
        }
        
        $navigationHtml = '';
        if (count($filesArray) > 1) {
            $paginationBullets = '';
            foreach ($filesArray as $index => $file) {
                $activeClass = $index === 0 ? ' active' : '';
                $paginationBullets .= '<span class="swiper-pagination-bullet' . $activeClass . '" onclick="swiperGoTo(\'' . $blockId . '\', ' . $index . ')"></span>';
            }
            
            $navigationHtml = '
                <button class="swiper-navigation swiper-prev" onclick="swiperPrev(\'' . $blockId . '\')">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button class="swiper-navigation swiper-next" onclick="swiperNext(\'' . $blockId . '\')">
                    <i class="fas fa-chevron-right"></i>
                </button>
                <div class="swiper-pagination">' . $paginationBullets . '</div>
                <div class="swiper-counter">1 / ' . count($filesArray) . '</div>';
        }
        
        $filesJson = htmlspecialchars(json_encode($filesArray), ENT_QUOTES, 'UTF-8');
        
        return '<div class="content-block swiper-block" id="' . $blockId . '" data-files="' . $filesJson . '" draggable="false">
                    <div class="block-toolbar">
                        <button class="block-btn drag-handle" title="Перетащить блок" data-block-id="' . $blockId . '">
                            <i class="fas fa-grip-vertical"></i>
                        </button>
                        <button class="block-btn add-more" onclick="addMoreImages(\'' . $blockId . '\')" title="Добавить еще изображения">
                            <i class="fas fa-plus"></i>
                        </button>
                        <button class="block-btn" onclick="moveBlockUp(\'' . $blockId . '\')" title="Переместить вверх">
                            <i class="fas fa-arrow-up"></i>
                        </button>
                        <button class="block-btn" onclick="moveBlockDown(\'' . $blockId . '\')" title="Переместить вниз">
                            <i class="fas fa-arrow-down"></i>
                        </button>
                        <button class="block-btn delete-btn" onclick="deleteBlock(\'' . $blockId . '\')" title="Удалить блок">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                    <div class="swiper-container" data-current-slide="0">
                        <div class="swiper-wrapper" style="transform: translateX(0%);">
                            ' . $slidesHtml . '
                        </div>
                        ' . $navigationHtml . '
                    </div>
                  
                </div>';
    }

    /**
     * Генерирует HTML для file grid блока с множественными документами
     */
    private function generateFileGridBlockHtml($files)
    {
        $blockId = 'file_grid_' . time() . rand(1000, 9999);
        $filesArray = [];
        
        foreach ($files as $fileData) {
            $parts = explode('|', $fileData);
            if (count($parts) >= 4) {
                $filesArray[] = [
                    'url' => $parts[0],
                    'name' => $parts[1],
                    'size' => $parts[2],
                    'type' => $parts[3] ?? 'document'
                ];
            }
        }
        
        if (empty($filesArray)) {
            return '';
        }
        
        $filesHtml = '';
        foreach ($filesArray as $index => $file) {
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $iconClass = $this->getFileGridIconClass($extension);
            $iconName = $this->getFileGridIcon($extension);
            
            $filesHtml .= '<div class="file-grid-item" data-index="' . $index . '">
                               <div class="file-grid-icon ' . $iconClass . '">
                                   <i class="fas ' . $iconName . '"></i>
                               </div>
                               <div class="file-grid-name">' . htmlspecialchars($file['name']) . '</div>
                               <div class="file-grid-size">' . htmlspecialchars($file['size']) . '</div>
                               <div class="file-grid-actions">
                                   <a href="' . htmlspecialchars($file['url']) . '" download="' . htmlspecialchars($file['name']) . '" class="file-grid-btn download" title="Скачать">
                                       <i class="fas fa-download"></i>
                                   </a>
                                   <button class="file-grid-btn delete" onclick="deleteDocumentFromGrid(\'' . $blockId . '\', ' . $index . ')" title="Удалить">
                                       <i class="fas fa-trash"></i>
                                   </button>
                               </div>
                           </div>';
        }
        
        $filesJson = htmlspecialchars(json_encode($filesArray), ENT_QUOTES, 'UTF-8');
        
        return '<div class="content-block file-grid-block" id="' . $blockId . '" data-files="' . $filesJson . '" draggable="false">
                    <div class="block-toolbar">
                        <button class="block-btn drag-handle" title="Перетащить блок" data-block-id="' . $blockId . '">
                            <i class="fas fa-grip-vertical"></i>
                        </button>
                        <button class="block-btn add-more" onclick="addMoreDocuments(\'' . $blockId . '\')" title="Добавить еще документы">
                            <i class="fas fa-plus"></i>
                        </button>
                        <button class="block-btn" onclick="moveBlockUp(\'' . $blockId . '\')" title="Переместить вверх">
                            <i class="fas fa-arrow-up"></i>
                        </button>
                        <button class="block-btn" onclick="moveBlockDown(\'' . $blockId . '\')" title="Переместить вниз">
                            <i class="fas fa-arrow-down"></i>
                        </button>
                        <button class="block-btn delete-btn" onclick="deleteBlock(\'' . $blockId . '\')" title="Удалить блок">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                    <div class="file-grid-header">
                        <i class="fas fa-folder"></i>
                        <h5 class="file-grid-title">Документы</h5>
                        <span class="file-grid-count">' . count($filesArray) . '</span>
                    </div>
                    <div class="file-grid">
                        ' . $filesHtml . '
                    </div>
                </div>';
    }

    /**
     * Определяет CSS класс для иконки файла в grid
     */
    private function getFileGridIconClass($extension)
    {
        $extension = strtolower($extension);
        $iconMap = [
            'pdf' => 'pdf',
            'doc' => 'doc', 'docx' => 'doc',
            'xls' => 'xls', 'xlsx' => 'xls',
            'ppt' => 'ppt', 'pptx' => 'ppt',
            'zip' => 'zip', 'rar' => 'zip',
            'txt' => 'txt'
        ];
        return $iconMap[$extension] ?? 'default';
    }

    /**
     * Определяет иконку Font Awesome для файла в grid
     */
    private function getFileGridIcon($extension)
    {
        $extension = strtolower($extension);
        $iconMap = [
            'pdf' => 'fa-file-pdf',
            'doc' => 'fa-file-word', 'docx' => 'fa-file-word',
            'xls' => 'fa-file-excel', 'xlsx' => 'fa-file-excel',
            'ppt' => 'fa-file-powerpoint', 'pptx' => 'fa-file-powerpoint',
            'zip' => 'fa-file-archive', 'rar' => 'fa-file-archive',
            'txt' => 'fa-file-text'
        ];
        return $iconMap[$extension] ?? 'fa-file';
    }
}
