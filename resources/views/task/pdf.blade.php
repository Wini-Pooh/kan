<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>{{ $task->title ?? 'Задача без названия' }} - PDF</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 20px;
            background: white;
            font-size: 12pt;
        }
        
        .header {
            border-bottom: 3px solid #007bff;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .task-title {
            font-size: 24pt;
            font-weight: bold;
            color: #007bff;
            margin: 0 0 15px 0;
        }
        
        .task-meta {
            display: block;
            font-size: 11pt;
            color: #666;
            margin-bottom: 10px;
        }
        
        .meta-item {
            display: inline-block;
            margin-right: 20px;
            margin-bottom: 5px;
        }
        
        .meta-label {
            font-weight: bold;
        }
        
        .priority {
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 10pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .priority.low { background: #d4edda; color: #155724; }
        .priority.medium { background: #fff3cd; color: #856404; }
        .priority.high { background: #f8d7da; color: #721c24; }
        .priority.urgent { background: #f5c6cb; color: #721c24; }
        .priority.critical { background: #d1ecf1; color: #0c5460; }
        .priority.blocked { background: #e2e3e5; color: #383d41; }
        
        .content-section {
            margin: 30px 0;
        }
        
        .section-title {
            font-size: 16pt;
            font-weight: bold;
            color: #495057;
            margin-bottom: 15px;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 5px;
        }
        
        .task-content {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin: 15px 0;
        }
        
        .task-content p {
            margin: 8px 0;
        }
        
        .task-content h1, .task-content h2, .task-content h3 {
            color: #495057;
            margin: 12px 0 8px 0;
        }
        
        .task-content ul, .task-content ol {
            margin: 8px 0;
            padding-left: 20px;
        }
        
        .task-content li {
            margin: 3px 0;
        }
        
        .footer {
            margin-top: 40px;
            padding-top: 15px;
            border-top: 1px solid #dee2e6;
            font-size: 10pt;
            color: #6c757d;
            text-align: center;
        }
        
        .no-content {
            text-align: center;
            color: #6c757d;
            font-style: italic;
            padding: 30px 15px;
        }
        
        /* Стили для изображений в PDF */
        .task-content img {
            max-width: 100% !important;
            height: auto !important;
            max-height: 400px !important;
            border: 1px solid #ddd !important;
            border-radius: 4px !important;
            padding: 4px !important;
            margin: 10px auto !important;
            display: block !important;
            page-break-inside: avoid !important;
        }
        
        .task-content div {
            page-break-inside: avoid !important;
            margin: 10px 0 !important;
        }
        
        /* Стили для подписей к изображениям */
        .task-content p[style*="font-style: italic"] {
            text-align: center !important;
            font-size: 10pt !important;
            color: #666 !important;
            margin: 5px 0 !important;
            page-break-before: avoid !important;
            font-style: italic !important;
        }
        
        /* Стили для заголовков блоков */
        .task-content h4[style*="color: #495057"] {
            color: #495057 !important;
            font-size: 14pt !important;
            margin: 0 0 15px 0 !important;
            font-weight: bold !important;
        }
        
        /* Стили для видео блоков */
        .task-content span[style*="background: #dc3545"] {
            background: #dc3545 !important;
            color: white !important;
        }
        
        /* Стили для ссылок в PDF */
        .task-content a {
            color: #007bff !important;
            text-decoration: underline !important;
            font-weight: bold !important;
            word-break: break-all;
        }
        
        .task-content a:hover {
            color: #0056b3 !important;
        }
        
        /* Стили для блоков документов в PDF */
        .task-content div[style*="border: 1px solid #ddd"] {
            page-break-inside: avoid !important;
            margin: 15px 0 !important;
            border: 1px solid #ddd !important;
            border-radius: 5px !important;
            padding: 15px !important;
            background: #f8f9fa !important;
        }
        
        .task-content div[style*="border: 1px solid #ddd"] a {
            color: #007bff !important;
            text-decoration: underline !important;
            font-weight: bold !important;
            word-break: break-all;
        }
        
        .task-content div[style*="border: 1px solid #ddd"] span[style*="background: #007bff"] {
            display: inline-block !important;
            color: white !important;
            font-weight: bold !important;
            text-align: center !important;
            background: #007bff !important;
            border-radius: 3px !important;
            padding: 2px 4px !important;
            font-size: 10pt !important;
            margin-right: 8px !important;
        }
        
        /* Улучшенные стили для изображений */
        .task-content img {
            max-width: 100% !important;
            height: auto !important;
            max-height: 400px !important;
            border: 1px solid #ddd !important;
            border-radius: 4px !important;
            padding: 4px !important;
            margin: 10px auto !important;
            display: block !important;
            page-break-inside: avoid !important;
        }
        
        /* Стили для множественных файлов */
        .task-content div[style*="margin: 10px 0"] {
            page-break-inside: avoid !important;
        }
        
        /* Общие стили для блоков с файлами */
        .task-content div[style*="page-break-inside: avoid"] {
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }
        
        /* Стили для URL ссылок */
        .task-content p[style*="font-size: 10pt"] {
            word-break: break-all !important;
            overflow-wrap: break-word !important;
        }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1 class="task-title">{{ $task->title ?? 'Задача без названия' }}</h1>
        
        <div class="task-meta">
            <div class="meta-item">
                <span class="meta-label">ID:</span>
                <span>#{{ $task->id }}</span>
            </div>
            
            @if($task->priority)
            <div class="meta-item">
                <span class="meta-label">Приоритет:</span>
                <span class="priority {{ $task->priority }}">{{ $task->priority_label }}</span>
            </div>
            @endif
            
            @if($task->assignee)
            <div class="meta-item">
                <span class="meta-label">Исполнитель:</span>
                <span>{{ $task->assignee->name }}</span>
            </div>
            @endif
            
            @if($task->creator)
            <div class="meta-item">
                <span class="meta-label">Создал:</span>
                <span>{{ $task->creator->name }}</span>
            </div>
            @endif
            
            <div class="meta-item">
                <span class="meta-label">Создано:</span>
                <span>{{ $task->created_at->format('d.m.Y H:i') }}</span>
            </div>
            
            @if($task->start_date)
            <div class="meta-item">
                <span class="meta-label">Дата начала:</span>
                <span>{{ $task->start_date->format('d.m.Y') }}</span>
            </div>
            @endif
            
            @if($task->due_date)
            <div class="meta-item">
                <span class="meta-label">Дата окончания:</span>
                <span>{{ $task->due_date->format('d.m.Y') }}</span>
            </div>
            @endif
            
            @if($task->estimated_time)
            <div class="meta-item">
                <span class="meta-label">Оценка времени:</span>
                <span>{{ $task->estimated_time }} ч.</span>
            </div>
            @endif
            
            <div class="meta-item">
                <span class="meta-label">Статус:</span>
                <span>{{ $task->status_label }}</span>
            </div>
            
            <div class="meta-item">
                <span class="meta-label">Пространство:</span>
                <span>{{ $space->name }}</span>
            </div>
        </div>
    </div>

    <!-- Информация о ссылках в PDF -->
    <div style="background: #e3f2fd; border: 1px solid #90caf9; border-radius: 5px; padding: 10px; margin: 15px 0; font-size: 10pt;">
        <p style="margin: 0; color: #1565c0;"><strong>📋 Информация о файлах и ссылках:</strong></p>
        <p style="margin: 5px 0 0 0; color: #1976d2;">Все ссылки в данном PDF документе активны и доступны для перехода. Кликните по любой ссылке, чтобы скачать файл или перейти к ресурсу.</p>
    </div>

    @if($task->description)
    <div class="content-section">
        <h2 class="section-title">Описание</h2>
        <div class="task-content">
            {{ $task->description }}
        </div>
    </div>
    @endif

    <div class="content-section">
        <h2 class="section-title">Содержимое задачи</h2>
        @if($task->parsed_content)
            <div class="task-content">
                {!! $task->parsed_content !!}
            </div>
        @else
            <div class="no-content">
                Содержимое задачи отсутствует
            </div>
        @endif
    </div>

    <div class="footer">
        <p>Документ создан {{ now()->format('d.m.Y в H:i') }}</p>
        <p>Система управления задачами - {{ config('app.name', 'Kanban') }}</p>
        @if(isset($taskUrl))
        <p style="margin-top: 10px; font-size: 10pt; color: #007bff;">
            <strong>Ссылка на задачу:</strong> 
            <a href="{{ $taskUrl }}" target="_blank" style="color: #007bff; text-decoration: underline; word-break: break-all;">
                {{ $taskUrl }}
            </a>
        </p>
        @endif
    </div>
</body>
</html>
