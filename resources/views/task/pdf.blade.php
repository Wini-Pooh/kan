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
            max-width: 100%;
            height: auto;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 4px;
            margin: 10px 0;
            display: block;
            page-break-inside: avoid;
        }
        
        .task-content div {
            page-break-inside: avoid;
            margin: 10px 0;
        }
        
        /* Стили для подписей к изображениям */
        .task-content p[style*="font-style: italic"] {
            text-align: center;
            font-size: 10pt;
            color: #666;
            margin: 5px 0;
            page-break-before: avoid;
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
    </div>
</body>
</html>
