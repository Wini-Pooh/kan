@extends('layouts.app')

@section('content')
<!-- Фиксированная шапка задачи -->
<div class="task-header-fixed" data-task-id="{{ $task->id }}" data-csrf-token="{{ csrf_token() }}">
    <div class="container-fluid">
        <div class="row align-items-center">
            <!-- Левая часть - Название задачи -->
            <div class="col-md-4">
                <div class="task-title-container">
                    <h2 class="task-title" id="taskTitle" onclick="editTaskTitle()">
                        {{ $task->title }}
                    </h2>
                    <input type="text" class="form-control task-title-input d-none" 
                           id="taskTitleInput" value="{{ $task->title }}" 
                           onblur="saveTaskTitle()" 
                           onkeypress="handleTitleKeyPress(event)"
                           oninput="debounce(saveTaskTitleAuto, 1000, 'title')">
                </div>
            </div>

            <!-- Центральная часть - Статус и время -->
            <div class="col-md-4 text-center">
                <div class="d-flex justify-content-center align-items-center gap-3">
                    <!-- Статус задачи -->
                    <div class="status-container position-relative">
                        <div class="status-square" 
                             id="statusSquare" 
                             data-priority="{{ $task->priority }}"
                             onclick="toggleStatusMenu()">
                        </div>
                        <!-- Выпадающее меню статуса -->
                        <div class="status-menu d-none" id="statusMenu">
                            <div class="status-menu-header">Приоритет задачи</div>
                            <div class="priority-options">
                                <div class="priority-option" data-priority="low" onclick="changePriority('low')">
                                    <div class="priority-circle low"></div>
                                    <span>Низкий</span>
                                </div>
                                <div class="priority-option" data-priority="medium" onclick="changePriority('medium')">
                                    <div class="priority-circle medium"></div>
                                    <span>Средний</span>
                                </div>
                                <div class="priority-option" data-priority="high" onclick="changePriority('high')">
                                    <div class="priority-circle high"></div>
                                    <span>Высокий</span>
                                </div>
                                <div class="priority-option" data-priority="urgent" onclick="changePriority('urgent')">
                                    <div class="priority-circle urgent"></div>
                                    <span>Срочный</span>
                                </div>
                                <div class="priority-option" data-priority="critical" onclick="changePriority('critical')">
                                    <div class="priority-circle critical"></div>
                                    <span>Критический</span>
                                </div>
                                <div class="priority-option" data-priority="blocked" onclick="changePriority('blocked')">
                                    <div class="priority-circle blocked"></div>
                                    <span>Заблокирован</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Иконка времени/календаря -->
                    <div class="time-container position-relative">
                        <i class="fas fa-calendar-alt time-icon" onclick="toggleDatePicker()"></i>
                        <!-- Календарь -->
                        <div class="date-picker-container d-none" id="datePickerContainer">
                            <div class="date-picker-header">Сроки выполнения</div>
                            
                            <!-- Дата начала -->
                            <div class="date-field mb-3">
                                <label for="startDateInput" class="form-label">Дата начала:</label>
                                <input type="date" class="form-control" id="startDateInput" 
                                       value="{{ $task->start_date ?? '' }}" 
                                       onchange="debounce(saveStartDate, 500, 'startDate')">
                            </div>
                            
                            <!-- Дата окончания -->
                            <div class="date-field mb-3">
                                <label for="dueDateInput" class="form-label">Дата окончания:</label>
                                <input type="date" class="form-control" id="dueDateInput" 
                                       value="{{ $task->due_date ?? '' }}" 
                                       onchange="debounce(saveDueDate, 500, 'dueDate')">
                            </div>
                            
                            <div class="date-picker-actions">
                                <button class="btn btn-sm btn-outline-danger" onclick="clearAllDates()">Очистить все</button>
                                <button class="btn btn-sm btn-primary" onclick="closeDatePicker()">Готово</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Правая часть - Исполнитель -->
            <div class="col-md-4 text-end">
                <div class="assignee-container position-relative">
                    @if($task->assignee_id)
                        <!-- Если есть исполнитель -->
                        <div class="assignee-avatar" onclick="toggleAssigneeMenu()">
                            <img src="{{ $task->assignee->avatar ?? '/images/default-avatar.png' }}" 
                                 alt="{{ $task->assignee->name ?? 'Пользователь' }}" 
                                 class="user-avatar">
                            <span class="assignee-name">{{ $task->assignee->name ?? 'Пользователь' }}</span>
                        </div>
                    @else
                        <!-- Если нет исполнителя -->
                        <div class="no-assignee" onclick="toggleAssigneeMenu()">
                            <i class="fas fa-user-plus"></i>
                            <span>Назначить исполнителя</span>
                        </div>
                    @endif

                    <!-- Выпадающий список участников -->
                    <div class="assignee-menu d-none" id="assigneeMenu">
                        <div class="assignee-menu-header">Выберите исполнителя</div>
                        <div class="assignee-options">
                            <div class="assignee-option" data-user-id="" onclick="changeAssignee(null)">
                                <div class="user-avatar-small">
                                    <i class="fas fa-user-slash"></i>
                                </div>
                                <span>Не назначен</span>
                            </div>
                            @foreach($space->activeMembers as $member)
                                <div class="assignee-option" data-user-id="{{ $member->id }}" 
                                     onclick="changeAssignee({{ $member->id }})">
                                    <div class="user-avatar-small">
                                        <img src="{{ $member->avatar ?? '/images/default-avatar.png' }}" 
                                             alt="{{ $member->name }}">
                                    </div>
                                    <span>{{ $member->name }}</span>
                                    @if($member->id === $task->assignee_id)
                                        <i class="fas fa-check text-success ms-auto"></i>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Основной контент -->
<div class="container-fluid task-content">
    <div class="row justify-content-center">
        <div class="col-10">
            <!-- Полотно для контента задачи -->
            <div class="task-canvas">
                <!-- Панель инструментов -->
                <div class="canvas-toolbar">
                    <div class="toolbar-section">
                        <div class="toolbar-title">Контент задачи</div>
                    </div>
                    <div class="toolbar-section">
                        <button type="button" class="toolbar-btn save-btn" onclick="saveContent()" title="Сохранить">
                            <i class="fas fa-save"></i>
                            <span>Сохранить</span>
                        </button>
                    </div>
                </div>

                <!-- Основное полотно контента -->
                <div class="content-canvas" id="contentCanvas">
                    @if($task->content)
                        <div class="content-display">
                            <div class="editable-content" contenteditable="true" data-placeholder="Добавьте описание задачи...">{!! $task->parsed_content !!}</div>
                        </div>
                    @elseif($task->description)
                        <div class="content-display">
                            <div class="editable-content" contenteditable="true" data-placeholder="Добавьте описание задачи...">{!! nl2br(e($task->description)) !!}</div>
                        </div>
                    @else
                        <div class="content-display">
                            <div class="editable-content" contenteditable="true" data-placeholder="Добавьте описание задачи..."></div>
                        </div>
                    @endif
                </div>
                </div>

                <!-- Скрытые поля для загрузки файлов -->
                <input type="file" id="imageUpload" accept="image/*" multiple style="display: none;" onchange="handleImageUpload(event)">
                <input type="file" id="videoUpload" accept="video/*" style="display: none;" onchange="handleVideoUpload(event)">
                <input type="file" id="fileUpload" style="display: none;" onchange="handleFileUpload(event)">
            </div>
        </div>
        
      
    </div>
</div>

<!-- Фиксированная нижняя панель с иконками -->
<div class="bottom-toolbar-fixed">
    <div class="bottom-toolbar-content">
        <div class="toolbar-icon" onclick="addTextBlock()" title="Добавить текст">
            <i class="fas fa-font"></i>
        </div>
        <div class="toolbar-icon" onclick="openImageUpload()" title="Добавить изображение">
            <i class="fas fa-image"></i>
        </div>
        <div class="toolbar-icon" onclick="openVideoUpload()" title="Добавить видео">
            <i class="fas fa-video"></i>
        </div>
        <div class="toolbar-icon" onclick="openFileUpload()" title="Добавить файл">
            <i class="fas fa-file"></i>
        </div>
    </div>
</div>

<!-- Модальные окна для загрузки файлов -->
<div class="modal fade" id="uploadImageModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Загрузить изображение</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="upload-area" onclick="document.getElementById('imageInput').click()">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <p>Нажмите или перетащите изображение сюда</p>
                    <input type="file" id="imageInput" accept="image/*" style="display: none;" onchange="handleImageUpload(this)">
                </div>
                <div class="upload-preview" id="imagePreview" style="display: none;">
                    <img id="previewImage" src="" alt="Preview">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary" onclick="insertImage()">Добавить</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uploadVideoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Добавить видео</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Загрузить файл</label>
                    <div class="upload-area" onclick="document.getElementById('videoInput').click()">
                        <i class="fas fa-video"></i>
                        <p>Нажмите или перетащите видео сюда</p>
                        <input type="file" id="videoInput" accept="video/*" style="display: none;" onchange="handleVideoUpload(this)">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Или вставить ссылку</label>
                    <input type="url" class="form-control" id="videoUrl" placeholder="https://youtube.com/watch?v=...">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary" onclick="insertVideo()">Добавить</button>
            </div>
        </div>
    </div>
</div>

<!-- Скрытые input элементы для загрузки файлов -->
<input type="file" id="imageUpload" accept="image/*" style="display: none;" onchange="handleImageUpload(event)">
<input type="file" id="videoUpload" accept="video/*" style="display: none;" onchange="handleVideoUpload(event)">
<input type="file" id="fileUpload" accept="*/*" style="display: none;" onchange="handleFileUpload(event)">

<style>
/* Стили для отображения файлов */
.content-display {
    padding: 20px;
    line-height: 1.6;
    font-size: 16px;
    min-height: 200px;
}

.editable-content {
    min-height: 150px;
    padding: 15px;
    border: 2px dashed transparent;
    border-radius: 8px;
    transition: all 0.3s ease;
    background: transparent;
    outline: none;
}

.editable-content:hover {
    border-color: #dee2e6;
    background: #f8f9fa;
}

.editable-content:focus {
    border-color: #007bff;
    background: #fff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.editable-content:empty::before {
    content: attr(data-placeholder);
    color: #adb5bd;
    pointer-events: none;
}

.file-block {
    margin: 15px 0;
    padding: 10px;
    border-radius: 8px;
    background: #f8f9fa;
    border: 1px solid #e9ecef;
}

.file-block img {
    display: block;
    max-width: 100%;
    height: auto;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transition: transform 0.2s ease;
}

.file-block img:hover {
    transform: scale(1.02);
    cursor: pointer;
}

.file-block video {
    display: block;
    max-width: 100%;
    height: auto;
    border-radius: 8px;
}

.file-info {
    margin-top: 8px;
    font-size: 14px;
    color: #6c757d;
}

.file-info i {
    margin-right: 5px;
}

.document-block .card {
    border: 1px solid #dee2e6;
    transition: box-shadow 0.2s ease;
}

.document-block .card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.document-block a {
    color: #495057;
    text-decoration: none;
}

.document-block a:hover {
    color: #007bff;
}

/* Responsive для файлов */
@media (max-width: 768px) {
    .content-display {
        padding: 15px;
    }
    
    .file-block {
        margin: 10px 0;
        padding: 8px;
    }
    
    .editable-content {
        padding: 10px;
    }
}

/* Фиксированная шапка */
.task-header-fixed {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    background: white;
    border-bottom: 2px solid #e9ecef;
    z-index: 1000;
    padding: 15px 0;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.task-content {
    margin-top: 80px;
    margin-bottom: 100px;
    padding: 20px;
}

/* Название задачи */
.task-title-container {
    position: relative;
}

.task-title {
    margin: 0;
    cursor: pointer;
    padding: 8px 12px;
    border-radius: 6px;
    transition: background-color 0.2s;
    color: #2c3e50;
    font-weight: 600;
}

.task-title:hover {
    background-color: #f8f9fa;
}

.task-title-input {
    font-size: 1.5rem;
    font-weight: 600;
    border: 2px solid #007bff;
    border-radius: 6px;
}

/* Статус квадрат */
.status-container {
    display: inline-block;
}

.status-square {
    width: 32px;
    height: 32px;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;
    border: 2px solid transparent;
    position: relative;
}

.status-square:hover {
    transform: scale(1.1);
    border-color: #007bff;
}

.status-square[data-priority="low"] {
    background: linear-gradient(135deg, #28a745, #20c997);
}

.status-square[data-priority="medium"] {
    background: linear-gradient(135deg, #ffc107, #fd7e14);
}

.status-square[data-priority="high"] {
    background: linear-gradient(135deg, #fd7e14, #dc3545);
}

.status-square[data-priority="urgent"] {
    background: linear-gradient(135deg, #dc3545, #e83e8c);
}

.status-square[data-priority="critical"] {
    background: linear-gradient(135deg, #6f42c1, #e83e8c);
}

.status-square[data-priority="blocked"] {
    background: linear-gradient(135deg, #6c757d, #495057);
}

/* Меню статуса */
.status-menu {
    position: absolute;
    top: 100%;
    left: 50%;
    transform: translateX(-50%);
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    padding: 12px;
    min-width: 200px;
    z-index: 1001;
    margin-top: 8px;
}

.status-menu-header {
    font-weight: 600;
    margin-bottom: 8px;
    color: #495057;
    font-size: 14px;
}

.priority-option {
    display: flex;
    align-items: center;
    padding: 8px 12px;
    cursor: pointer;
    border-radius: 6px;
    transition: background-color 0.2s;
    gap: 10px;
}

.priority-option:hover {
    background-color: #f8f9fa;
}

.priority-circle {
    width: 20px;
    height: 20px;
    border-radius: 50%;
}

.priority-circle.low { background: #28a745; }
.priority-circle.medium { background: #ffc107; }
.priority-circle.high { background: #fd7e14; }
.priority-circle.urgent { background: #dc3545; }
.priority-circle.critical { background: #6f42c1; }
.priority-circle.blocked { background: #6c757d; }

/* Иконка времени */
.time-icon {
    font-size: 24px;
    color: #6c757d;
    cursor: pointer;
    transition: color 0.2s;
    padding: 8px;
    border-radius: 6px;
}

.time-icon:hover {
    color: #007bff;
    background-color: #f8f9fa;
}

/* Календарь */
.date-picker-container {
    position: absolute;
    top: 100%;
    left: 50%;
    transform: translateX(-50%);
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    padding: 16px;
    min-width: 280px;
    z-index: 1001;
    margin-top: 8px;
}

.date-picker-header {
    font-weight: 600;
    margin-bottom: 12px;
    color: #495057;
    font-size: 14px;
    text-align: center;
}

.date-field {
    margin-bottom: 12px;
}

.date-field label {
    display: block;
    margin-bottom: 4px;
    font-size: 13px;
    font-weight: 500;
    color: #495057;
}

.date-field input[type="date"] {
    width: 100%;
    padding: 6px 8px;
    border: 1px solid #ced4da;
    border-radius: 4px;
    font-size: 13px;
}

.date-field input[type="date"]:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    outline: 0;
}

.date-picker-actions {
    margin-top: 12px;
    display: flex;
    gap: 8px;
    justify-content: flex-end;
}

/* Исполнитель */
.assignee-container {
    display: inline-block;
}

.assignee-avatar, .no-assignee {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    padding: 6px 12px;
    border-radius: 6px;
    transition: background-color 0.2s;
    border: 1px solid transparent;
}

.assignee-avatar:hover, .no-assignee:hover {
    background-color: #f8f9fa;
    border-color: #dee2e6;
}

.user-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    object-fit: cover;
}

.no-assignee {
    color: #6c757d;
    font-style: italic;
}

.no-assignee i {
    font-size: 20px;
}

/* Меню исполнителей */
.assignee-menu {
    position: absolute;
    top: 100%;
    right: 0;
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    padding: 12px;
    min-width: 220px;
    z-index: 1001;
    margin-top: 8px;
}

.assignee-menu-header {
    font-weight: 600;
    margin-bottom: 8px;
    color: #495057;
    font-size: 14px;
}

.assignee-option {
    display: flex;
    align-items: center;
    padding: 8px 12px;
    cursor: pointer;
    border-radius: 6px;
    transition: background-color 0.2s;
    gap: 10px;
}

.assignee-option:hover {
    background-color: #f8f9fa;
}

.user-avatar-small {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    overflow: hidden;
    background: #e9ecef;
    display: flex;
    align-items: center;
    justify-content: center;
}

.user-avatar-small img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.user-avatar-small i {
    color: #6c757d;
    font-size: 14px;
}

/* Responsive */
@media (max-width: 768px) {
    .task-header-fixed .col-md-4 {
        margin-bottom: 10px;
    }
    
    .task-title {
        font-size: 1.2rem;
    }
    
    .assignee-name {
        display: none;
    }
}

/* Индикаторы сохранения */
.save-status {
    font-size: 14px;
    margin-left: 8px;
    display: none;
    vertical-align: middle;
}

.save-status.saving {
    color: #007bff;
}

.save-status.saved {
    color: #28a745;
}

.save-status.error {
    color: #dc3545;
}

/* Уведомления */
.notification {
    position: fixed;
    top: 100px;
    right: -300px;
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    padding: 16px 20px;
    z-index: 9999;
    transition: right 0.3s ease;
    display: flex;
    align-items: center;
    gap: 10px;
    max-width: 300px;
}

/* Стили для фотогалереи */
.gallery-block .block-content {
    padding: 0;
}

.gallery-container {
    width: 100%;
}

.gallery-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 10px;
    margin-bottom: 15px;
}

.gallery-item {
    position: relative;
    cursor: pointer;
    border-radius: 8px;
    overflow: hidden;
    aspect-ratio: 1;
    background: #f8f9fa;
}

.gallery-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.gallery-item:hover img {
    transform: scale(1.05);
}

.image-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.gallery-item:hover .image-overlay,
.image-container:hover .image-overlay {
    opacity: 1;
}

.image-overlay i {
    color: white;
    font-size: 24px;
}

.gallery-caption {
    padding: 15px;
    border-top: 1px solid #e9ecef;
}

/* Стили для отдельных изображений */
.image-block .image-container {
    position: relative;
    cursor: pointer;
    border-radius: 8px;
    overflow: hidden;
}

.image-block .block-image {
    width: 100%;
    height: auto;
    display: block;
    transition: transform 0.3s ease;
}

.image-container:hover .block-image {
    transform: scale(1.02);
}

.image-caption {
    margin-top: 10px;
    padding: 8px 12px;
    font-style: italic;
    color: #6c757d;
    border-left: 3px solid #e9ecef;
    background: #f8f9fa;
}

/* Модальное окно для просмотра изображений */
.image-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.9);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10000;
    cursor: pointer;
}

.image-modal .modal-content {
    position: relative;
    max-width: 90%;
    max-height: 90%;
    cursor: default;
}

.modal-image {
    width: 100%;
    height: auto;
    max-width: 90vw;
    max-height: 80vh;
    object-fit: contain;
    border-radius: 8px;
}

.close-modal {
    position: absolute;
    top: -40px;
    right: 0;
    color: white;
    font-size: 30px;
    cursor: pointer;
    z-index: 10001;
}

.close-modal:hover {
    color: #ccc;
}

.modal-caption {
    color: white;
    text-align: center;
    margin-top: 10px;
    font-size: 14px;
}

/* Автосохранение индикатор */
.auto-save-indicator {
    position: fixed;
    top: 20px;
    right: 20px;
    background: rgba(0, 0, 0, 0.8);
    color: white;
    padding: 8px 12px;
    border-radius: 4px;
    font-size: 12px;
    z-index: 9999;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.auto-save-indicator.show {
    opacity: 1;
}

/* Стили для контентного полотна */
.task-content-canvas {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    overflow: hidden;
    margin-bottom: 20px;
}

.content-toolbar {
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
    padding: 12px 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.toolbar-group {
    display: flex;
    gap: 8px;
}

.toolbar-btn {
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    padding: 8px 12px;
    cursor: pointer;
    transition: all 0.2s;
    color: #495057;
    font-size: 14px;
}

.toolbar-btn:hover {
    background: #007bff;
    color: white;
    border-color: #007bff;
}

.content-area {
    min-height: 400px;
    padding: 20px;
    position: relative;
}

.empty-state {
    text-align: center;
    color: #6c757d;
    padding: 60px 20px;
}

.empty-state i {
    font-size: 48px;
    margin-bottom: 16px;
    color: #dee2e6;
}

.content-block {
    position: relative;
    margin-bottom: 20px;
    border: 2px solid transparent;
    border-radius: 8px;
    transition: all 0.2s;
    background: white;
}

.content-block:hover {
    border-color: #007bff;
    box-shadow: 0 2px 8px rgba(0,123,255,0.15);
}

.content-block.active {
    border-color: #007bff;
    box-shadow: 0 4px 12px rgba(0,123,255,0.2);
}

.block-content {
    padding: 16px;
}

.text-content {
    line-height: 1.6;
    color: #495057;
    font-size: 16px;
}

.text-editor {
    width: 100%;
    min-height: 120px;
    border: none;
    outline: none;
    resize: vertical;
    font-family: inherit;
    font-size: 16px;
    line-height: 1.6;
    padding: 0;
}

.image-content img {
    max-width: 100%;
    height: auto;
    border-radius: 6px;
}

.video-content video {
    width: 100%;
    border-radius: 6px;
}

.file-content {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    background: #f8f9fa;
    border-radius: 6px;
}

.file-icon {
    width: 32px;
    height: 32px;
    background: #007bff;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 14px;
}

.file-info {
    flex: 1;
}

.file-name {
    font-weight: 500;
    color: #495057;
}

.file-size {
    font-size: 12px;
    color: #6c757d;
}

.block-actions {
    position: absolute;
    top: 8px;
    right: 8px;
    display: flex;
    gap: 4px;
    opacity: 0;
    transition: opacity 0.2s;
    background: rgba(255,255,255,0.95);
    border-radius: 6px;
    padding: 4px;
}

.content-block:hover .block-actions {
    opacity: 1;
}

.action-btn {
    background: none;
    border: none;
    padding: 6px;
    border-radius: 4px;
    cursor: pointer;
    transition: background-color 0.2s;
    color: #6c757d;
    font-size: 12px;
}

.action-btn:hover {
    background: #e9ecef;
}

.edit-btn:hover {
    color: #007bff;
}

.delete-btn:hover {
    color: #dc3545;
}

/* Боковая панель */
.task-sidebar {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    padding: 20px;
}

.sidebar-section {
    margin-bottom: 24px;
    padding-bottom: 20px;
    border-bottom: 1px solid #e9ecef;
}

.sidebar-section:last-child {
    border-bottom: none;
    margin-bottom: 0;
}

.sidebar-title {
    font-weight: 600;
    color: #495057;
    margin-bottom: 16px;
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.detail-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
}

.detail-item label {
    font-weight: 500;
    color: #6c757d;
    font-size: 14px;
}

.detail-item span {
    color: #495057;
    font-size: 14px;
}

.assignee-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.user-avatar-small {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #007bff;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 600;
}

/* Модальные окна загрузки */
.upload-area {
    border: 2px dashed #dee2e6;
    border-radius: 8px;
    padding: 40px 20px;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s;
    background: #f8f9fa;
}

.upload-area:hover {
    border-color: #007bff;
    background: #e3f2fd;
}

.upload-area i {
    font-size: 48px;
    color: #007bff;
    margin-bottom: 16px;
}

.upload-area p {
    color: #6c757d;
    margin: 0;
}

.upload-preview {
    margin-top: 16px;
    text-align: center;
}

.upload-preview img {
    max-width: 100%;
    max-height: 200px;
    border-radius: 6px;
}

/* Анимации */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.content-block {
    animation: fadeIn 0.3s ease;
}
}

.notification.show {
    right: 20px;
}

.notification.success {
    border-left: 4px solid #28a745;
}

.notification.error {
    border-left: 4px solid #dc3545;
}

.notification i {
    font-size: 18px;
}

.notification.success i {
    color: #28a745;
}

.notification.error i {
    color: #dc3545;
}

.notification span {
    font-weight: 500;
    color: #495057;
}

/* Стили для полотна контента */
.task-canvas {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    margin-top: 20px;
    overflow: hidden;
}

.canvas-toolbar {
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
    padding: 16px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.toolbar-section {
    display: flex;
    align-items: center;
    gap: 16px;
}

.toolbar-title {
    font-weight: 600;
    color: #495057;
    font-size: 16px;
}

.toolbar-buttons {
    display: flex;
    gap: 12px;
}

.toolbar-btn {
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 8px 16px;
    display: flex;
    align-items: center;
    gap: 8px;
    color: #6c757d;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.2s;
}

.toolbar-btn:hover {
    background: #007bff;
    color: white;
    border-color: #007bff;
}

.toolbar-btn.save-btn {
    background: #28a745;
    color: white;
    border-color: #28a745;
}

.toolbar-btn.save-btn:hover {
    background: #218838;
    border-color: #1e7e34;
}

.content-canvas {
    min-height: 500px;
    padding: 20px;
    background: white;
}

.empty-canvas {
    text-align: center;
    padding: 80px 20px;
    color: #6c757d;
}

.empty-icon {
    font-size: 64px;
    color: #dee2e6;
    margin-bottom: 24px;
}

.empty-canvas h3 {
    color: #495057;
    margin-bottom: 16px;
}

.empty-canvas p {
    color: #6c757d;
    margin-bottom: 32px;
    font-size: 16px;
}

/* Блоки контента */
.content-block {
    position: relative;
    margin-bottom: 20px;
    border: 2px solid transparent;
    border-radius: 8px;
    transition: all 0.2s;
}

.content-block:hover {
    border-color: #007bff;
}

.content-block.active {
    border-color: #007bff;
    box-shadow: 0 0 0 3px rgba(0,123,255,0.1);
}

/* Текстовые блоки */
.text-block .block-content {
    padding: 16px;
    min-height: 60px;
}

.text-content {
    font-size: 16px;
    line-height: 1.6;
    color: #495057;
    outline: none;
    min-height: 40px;
}

.text-content:focus {
    outline: none;
}

.text-content:empty:before {
    content: "Начните писать...";
    color: #6c757d;
    font-style: italic;
}

/* Блоки изображений */
.image-block .block-content {
    text-align: center;
    padding: 16px;
}

.image-block img {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.image-caption {
    margin-top: 12px;
    font-size: 14px;
    color: #6c757d;
    font-style: italic;
}

/* Блоки видео */
.video-block .block-content {
    text-align: center;
    padding: 16px;
}

.video-block video {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
}

/* Блоки файлов */
.file-block .block-content {
    padding: 16px;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    background: #f8f9fa;
}

.file-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.file-icon {
    width: 48px;
    height: 48px;
    background: #007bff;
    color: white;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
}

.file-details h4 {
    margin: 0;
    font-size: 16px;
    color: #495057;
}

.file-details p {
    margin: 4px 0 0 0;
    font-size: 14px;
    color: #6c757d;
}

/* Панель инструментов блока */
.block-toolbar {
    position: absolute;
    top: -12px;
    right: 16px;
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    padding: 4px;
    display: none;
    gap: 4px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.content-block:hover .block-toolbar,
.content-block.active .block-toolbar {
    display: flex;
}

.block-btn {
    background: none;
    border: none;
    padding: 6px 8px;
    border-radius: 4px;
    color: #6c757d;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 14px;
}

.block-btn:hover {
    background: #f8f9fa;
    color: #007bff;
}

.block-btn.delete-btn:hover {
    color: #dc3545;
}

/* Drag and drop */
.content-canvas.drag-over {
    background: #e3f2fd;
    border: 2px dashed #007bff;
}

.drop-zone {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,123,255,0.1);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

.drop-zone.active {
    display: flex;
}

.drop-zone-content {
    background: white;
    padding: 40px;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    text-align: center;
}

.drop-zone-content i {
    font-size: 48px;
    color: #007bff;
    margin-bottom: 16px;
}

.drop-zone-content h3 {
    color: #495057;
    margin-bottom: 8px;
}

.drop-zone-content p {
    color: #6c757d;
    margin: 0;
}

/* Фиксированная нижняя панель */
.bottom-toolbar-fixed {
    position: fixed;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(222, 226, 230, 0.8);
    border-radius: 30px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.12);
    z-index: 1000;
    padding: 8px 16px;
}

.bottom-toolbar-content {
    display: flex;
    align-items: center;
    gap: 12px;
    justify-content: center;
}

.toolbar-icon {
    width: 48px;
    height: 48px;
    background: rgba(248, 249, 250, 0.8);
    border: 1px solid rgba(222, 226, 230, 0.6);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    color: #6c757d;
    font-size: 18px;
    position: relative;
    overflow: hidden;
}

.toolbar-icon::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, #007bff, #0056b3);
    opacity: 0;
    transition: opacity 0.2s ease;
    border-radius: 50%;
}

.toolbar-icon i {
    position: relative;
    z-index: 1;
    transition: color 0.2s ease;
}

.toolbar-icon:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0,123,255,0.25);
    border-color: #007bff;
}

.toolbar-icon:hover::before {
    opacity: 1;
}

.toolbar-icon:hover i {
    color: white;
}

.toolbar-icon:active {
    transform: translateY(0);
}

/* Адаптивность для мобильных устройств */
@media (max-width: 768px) {
    .bottom-toolbar-fixed {
        bottom: 15px;
        padding: 6px 12px;
    }
    
    .bottom-toolbar-content {
        gap: 10px;
    }
    
    .toolbar-icon {
        width: 44px;
        height: 44px;
        font-size: 16px;
    }
}

@media (max-width: 480px) {
    .bottom-toolbar-fixed {
        bottom: 10px;
        padding: 4px 8px;
    }
    
    .bottom-toolbar-content {
        gap: 8px;
    }
    
    .toolbar-icon {
        width: 40px;
        height: 40px;
        font-size: 14px;
    }
}
</style>

@vite(['resources/js/task-show.js'])

<script>
// Переменные taskId и csrfToken теперь получаются из data-атрибутов в task-show.js

// Редактирование названия задачи
function editTaskTitle() {
    titleClickCount++;
    
    if (titleClickCount === 1) {
        titleClickTimer = setTimeout(() => {
            titleClickCount = 0;
        }, 300);
    } else if (titleClickCount === 2) {
        clearTimeout(titleClickTimer);
        titleClickCount = 0;
        
        const titleElement = document.getElementById('taskTitle');
        const inputElement = document.getElementById('taskTitleInput');
        
        titleElement.classList.add('d-none');
        inputElement.classList.remove('d-none');
        inputElement.focus();
        inputElement.select();
    }
}

function handleTitleKeyPress(event) {
    if (event.key === 'Enter') {
        saveTaskTitle();
    } else if (event.key === 'Escape') {
        cancelTitleEdit();
    }
}

function saveTaskTitle() {
    const titleElement = document.getElementById('taskTitle');
    const inputElement = document.getElementById('taskTitleInput');
    const newTitle = inputElement.value.trim();
    
    if (newTitle === '') {
        showNotification('Название задачи не может быть пустым', 'error');
        return;
    }
    
    // Если название не изменилось, не сохраняем
    if (newTitle === titleElement.textContent.trim()) {
        titleElement.classList.remove('d-none');
        inputElement.classList.add('d-none');
        return;
    }
    
    // Показываем индикатор сохранения
    showSaveStatus(titleElement.parentElement, 'saving');
    
    // AJAX запрос для сохранения
    fetch(`/api/tasks/${taskId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({
            title: newTitle
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            titleElement.textContent = newTitle;
            titleElement.classList.remove('d-none');
            inputElement.classList.add('d-none');
            showSaveStatus(titleElement.parentElement, 'saved');
            showNotification('Название задачи сохранено');
        } else {
            throw new Error(data.message || 'Ошибка сохранения');
        }
    })
    .catch(error => {
        console.error('Ошибка:', error);
        showSaveStatus(titleElement.parentElement, 'error');
        showNotification('Ошибка при сохранении названия', 'error');
    });
}

// Автосохранение названия без закрытия поля редактирования
function saveTaskTitleAuto() {
    const titleElement = document.getElementById('taskTitle');
    const inputElement = document.getElementById('taskTitleInput');
    const newTitle = inputElement.value.trim();
    
    if (newTitle === '' || newTitle === titleElement.textContent.trim()) {
        return;
    }
    
    // Показываем индикатор сохранения
    showSaveStatus(titleElement.parentElement, 'saving');
    
    // AJAX запрос для сохранения
    fetch(`/api/tasks/${taskId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({
            title: newTitle
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            titleElement.textContent = newTitle;
            showSaveStatus(titleElement.parentElement, 'saved');
            // Не показываем уведомление для автосохранения
        } else {
            throw new Error(data.message || 'Ошибка сохранения');
        }
    })
    .catch(error => {
        console.error('Ошибка:', error);
        showSaveStatus(titleElement.parentElement, 'error');
    });
}

function cancelTitleEdit() {
    const titleElement = document.getElementById('taskTitle');
    const inputElement = document.getElementById('taskTitleInput');
    
    inputElement.value = titleElement.textContent;
    titleElement.classList.remove('d-none');
    inputElement.classList.add('d-none');
}

// Управление меню статуса
function toggleStatusMenu() {
    const menu = document.getElementById('statusMenu');
    menu.classList.toggle('d-none');
    
    // Закрываем другие меню
    document.getElementById('assigneeMenu').classList.add('d-none');
    document.getElementById('datePickerContainer').classList.add('d-none');
}

function changePriority(priority) {
    const statusSquare = document.getElementById('statusSquare');
    const statusContainer = statusSquare.parentElement;
    
    // Показываем индикатор сохранения
    showSaveStatus(statusContainer, 'saving');
    
    // AJAX запрос для изменения приоритета
    fetch(`/api/tasks/${taskId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({
            priority: priority
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            statusSquare.setAttribute('data-priority', priority);
            document.getElementById('statusMenu').classList.add('d-none');
            showSaveStatus(statusContainer, 'saved');
            showNotification('Приоритет задачи изменен');
        } else {
            throw new Error(data.message || 'Ошибка изменения приоритета');
        }
    })
    .catch(error => {
        console.error('Ошибка:', error);
        showSaveStatus(statusContainer, 'error');
        showNotification('Ошибка при изменении приоритета', 'error');
    });
}

// Управление календарем
function toggleDatePicker() {
    const container = document.getElementById('datePickerContainer');
    container.classList.toggle('d-none');
    
    // Закрываем другие меню
    document.getElementById('statusMenu').classList.add('d-none');
    document.getElementById('assigneeMenu').classList.add('d-none');
}

function saveStartDate() {
    const startDateInput = document.getElementById('startDateInput');
    const dueDateInput = document.getElementById('dueDateInput');
    const startDate = startDateInput.value;
    const timeContainer = document.querySelector('.time-container');
    
    // Проверяем, что дата начала не позже даты окончания
    if (startDate && dueDateInput.value && startDate > dueDateInput.value) {
        showNotification('Дата начала не может быть позже даты окончания', 'error');
        startDateInput.value = '';
        return;
    }
    
    // Показываем индикатор сохранения
    showSaveStatus(timeContainer, 'saving');
    
    // AJAX запрос для сохранения даты начала
    fetch(`/api/tasks/${taskId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({
            start_date: startDate || null
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSaveStatus(timeContainer, 'saved');
            showNotification('Дата начала сохранена');
        } else {
            throw new Error(data.message || 'Ошибка сохранения даты');
        }
    })
    .catch(error => {
        console.error('Ошибка:', error);
        showSaveStatus(timeContainer, 'error');
        showNotification('Ошибка при сохранении даты начала', 'error');
    });
}

function saveDueDate() {
    const startDateInput = document.getElementById('startDateInput');
    const dueDateInput = document.getElementById('dueDateInput');
    const dueDate = dueDateInput.value;
    const timeContainer = document.querySelector('.time-container');
    
    // Проверяем, что дата окончания не раньше даты начала
    if (dueDate && startDateInput.value && dueDate < startDateInput.value) {
        showNotification('Дата окончания не может быть раньше даты начала', 'error');
        dueDateInput.value = '';
        return;
    }
    
    // Показываем индикатор сохранения
    showSaveStatus(timeContainer, 'saving');
    
    // AJAX запрос для сохранения даты окончания
    fetch(`/api/tasks/${taskId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({
            due_date: dueDate || null
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSaveStatus(timeContainer, 'saved');
            showNotification('Дата окончания сохранена');
        } else {
            throw new Error(data.message || 'Ошибка сохранения даты');
        }
    })
    .catch(error => {
        console.error('Ошибка:', error);
        showSaveStatus(timeContainer, 'error');
        showNotification('Ошибка при сохранении даты окончания', 'error');
    });
}

function clearAllDates() {
    const startDateInput = document.getElementById('startDateInput');
    const dueDateInput = document.getElementById('dueDateInput');
    const timeContainer = document.querySelector('.time-container');
    
    startDateInput.value = '';
    dueDateInput.value = '';
    
    // Показываем индикатор сохранения
    showSaveStatus(timeContainer, 'saving');
    
    // AJAX запрос для очистки дат
    fetch(`/api/tasks/${taskId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({
            start_date: null,
            due_date: null
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSaveStatus(timeContainer, 'saved');
            showNotification('Даты очищены');
        } else {
            throw new Error(data.message || 'Ошибка очистки дат');
        }
    })
    .catch(error => {
        console.error('Ошибка:', error);
        showSaveStatus(timeContainer, 'error');
        showNotification('Ошибка при очистке дат', 'error');
    });
}

function closeDatePicker() {
    document.getElementById('datePickerContainer').classList.add('d-none');
}

// Управление исполнителями
function toggleAssigneeMenu() {
    const menu = document.getElementById('assigneeMenu');
    menu.classList.toggle('d-none');
    
    // Закрываем другие меню
    document.getElementById('statusMenu').classList.add('d-none');
    document.getElementById('datePickerContainer').classList.add('d-none');
}

function changeAssignee(userId) {
    const assigneeContainer = document.querySelector('.assignee-container');
    
    // Показываем индикатор сохранения
    showSaveStatus(assigneeContainer, 'saving');
    
    // AJAX запрос для изменения исполнителя
    fetch(`/api/tasks/${taskId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({
            assignee_id: userId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Обновляем UI без перезагрузки страницы
            updateAssigneeUI(data.task);
            document.getElementById('assigneeMenu').classList.add('d-none');
            showSaveStatus(assigneeContainer, 'saved');
            showNotification('Исполнитель назначен');
        } else {
            throw new Error(data.message || 'Ошибка назначения исполнителя');
        }
    })
    .catch(error => {
        console.error('Ошибка:', error);
        showSaveStatus(assigneeContainer, 'error');
        showNotification('Ошибка при назначении исполнителя', 'error');
    });
}

// Функция для обновления UI исполнителя
function updateAssigneeUI(task) {
    const assigneeContainer = document.querySelector('.assignee-container');
    const existingContent = assigneeContainer.querySelector('.assignee-avatar, .no-assignee');
    
    if (task.assignee_id && task.assignee) {
        // Если есть исполнитель
        existingContent.innerHTML = `
            <img src="${task.assignee.avatar || '/images/default-avatar.png'}" 
                 alt="${task.assignee.name}" 
                 class="user-avatar">
            <span class="assignee-name">${task.assignee.name}</span>
        `;
        existingContent.className = 'assignee-avatar';
    } else {
        // Если нет исполнителя
        existingContent.innerHTML = `
            <i class="fas fa-user-plus"></i>
            <span>Назначить исполнителя</span>
        `;
        existingContent.className = 'no-assignee';
    }
    
    // Обновляем меню исполнителей
    updateAssigneeMenu(task.assignee_id);
}

// Функция для обновления меню исполнителей
function updateAssigneeMenu(currentAssigneeId) {
    const assigneeOptions = document.querySelectorAll('.assignee-option');
    
    assigneeOptions.forEach(option => {
        const userId = option.getAttribute('data-user-id');
        const checkIcon = option.querySelector('.fa-check');
        
        if (checkIcon) {
            checkIcon.remove();
        }
        
        if ((userId === '' && !currentAssigneeId) || (userId == currentAssigneeId)) {
            option.innerHTML += '<i class="fas fa-check text-success ms-auto"></i>';
        }
    });
}

// Закрытие меню при клике вне их
document.addEventListener('click', function(event) {
    // Закрываем меню статуса
    const statusMenu = document.getElementById('statusMenu');
    const statusSquare = document.getElementById('statusSquare');
    if (!statusSquare.contains(event.target) && !statusMenu.contains(event.target)) {
        statusMenu.classList.add('d-none');
    }
    
    // Закрываем календарь
    const datePickerContainer = document.getElementById('datePickerContainer');
    const timeIcon = document.querySelector('.time-icon');
    if (!timeIcon.contains(event.target) && !datePickerContainer.contains(event.target)) {
        datePickerContainer.classList.add('d-none');
    }
    
    // Закрываем меню исполнителей
    const assigneeMenu = document.getElementById('assigneeMenu');
    const assigneeContainer = document.querySelector('.assignee-container');
    if (!assigneeContainer.contains(event.target) && !assigneeMenu.contains(event.target)) {
        assigneeMenu.classList.add('d-none');
    }
});
        body: JSON.stringify({
            due_date: dueDate || null
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSaveStatus(timeContainer, 'saved');
            showNotification('Дата окончания сохранена');
        } else {
            throw new Error(data.message || 'Ошибка сохранения даты');
        }
    })
    .catch(error => {
        console.error('Ошибка:', error);
        showSaveStatus(timeContainer, 'error');
        showNotification('Ошибка при сохранении даты окончания', 'error');
    });
}

function clearAllDates() {
    const startDateInput = document.getElementById('startDateInput');
    const dueDateInput = document.getElementById('dueDateInput');
    
    // Очищаем оба поля
    startDateInput.value = '';
    dueDateInput.value = '';
    
    // Сохраняем изменения
    saveStartDate();
    saveDueDate();
}

function closeDatePicker() {
    document.getElementById('datePickerContainer').classList.add('d-none');
}

// Управление исполнителями
function toggleAssigneeMenu() {
    const menu = document.getElementById('assigneeMenu');
    menu.classList.toggle('d-none');
    
    // Закрываем другие меню
    document.getElementById('statusMenu').classList.add('d-none');
    document.getElementById('datePickerContainer').classList.add('d-none');
}

function changeAssignee(userId) {
    const assigneeContainer = document.querySelector('.assignee-container');
    
    // Показываем индикатор сохранения
    showSaveStatus(assigneeContainer, 'saving');
    
    // AJAX запрос для изменения исполнителя
    fetch(`/api/tasks/${taskId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({
            assignee_id: userId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Обновляем UI без перезагрузки страницы
            updateAssigneeUI(data.task);
            document.getElementById('assigneeMenu').classList.add('d-none');
            showSaveStatus(assigneeContainer, 'saved');
            showNotification('Исполнитель назначен');
        } else {
            throw new Error(data.message || 'Ошибка назначения исполнителя');
        }
    })
    .catch(error => {
        console.error('Ошибка:', error);
        showSaveStatus(assigneeContainer, 'error');
        showNotification('Ошибка при назначении исполнителя', 'error');
    });
}

// Функция для обновления UI исполнителя
function updateAssigneeUI(task) {
    const assigneeContainer = document.querySelector('.assignee-container');
    const existingContent = assigneeContainer.querySelector('.assignee-avatar, .no-assignee');
    
    if (task.assignee_id && task.assignee) {
        // Если есть исполнитель
        existingContent.innerHTML = `
            <img src="${task.assignee.avatar || '/images/default-avatar.png'}" 
                 alt="${task.assignee.name}" 
                 class="user-avatar">
            <span class="assignee-name">${task.assignee.name}</span>
        `;
        existingContent.className = 'assignee-avatar';
    } else {
        // Если нет исполнителя
        existingContent.innerHTML = `
            <i class="fas fa-user-plus"></i>
            <span>Назначить исполнителя</span>
        `;
        existingContent.className = 'no-assignee';
    }
    
    // Обновляем меню исполнителей
    updateAssigneeMenu(task.assignee_id);
}

// Функция для обновления меню исполнителей
function updateAssigneeMenu(currentAssigneeId) {
    const assigneeOptions = document.querySelectorAll('.assignee-option');
    
    assigneeOptions.forEach(option => {
        const userId = option.getAttribute('data-user-id');
        const checkIcon = option.querySelector('.fa-check');
        
        if (checkIcon) {
            checkIcon.remove();
        }
        
        if ((userId === '' && !currentAssigneeId) || (userId == currentAssigneeId)) {
            option.innerHTML += '<i class="fas fa-check text-success ms-auto"></i>';
        }
    });
}

// Закрытие меню при клике вне их
document.addEventListener('click', function(event) {
    const statusMenu = document.getElementById('statusMenu');
    const assigneeMenu = document.getElementById('assigneeMenu');
    const datePickerContainer = document.getElementById('datePickerContainer');
    
    if (!event.target.closest('.status-container')) {
        statusMenu.classList.add('d-none');
    }
    
    if (!event.target.closest('.assignee-container')) {
        assigneeMenu.classList.add('d-none');
    }
    
    if (!event.target.closest('.time-container')) {
        datePickerContainer.classList.add('d-none');
    }
});

// Остальные функции теперь загружаются из task-show.js

// Drag and Drop функциональность
document.addEventListener('DOMContentLoaded', function() {
    const canvas = document.getElementById('contentCanvas');
    const contentArea = document.getElementById('contentArea');
    const emptyState = contentArea.querySelector('.empty-state');
    
    if (emptyState) {
        emptyState.remove();
    }
    
    const blockId = 'block_' + blockIdCounter++;
    const textBlock = document.createElement('div');
    textBlock.className = 'content-block text-block active';
    textBlock.setAttribute('data-type', 'text');
    textBlock.setAttribute('data-id', blockId);
    
    textBlock.innerHTML = `
        <div class="block-content">
            <textarea class="text-editor" placeholder="Введите текст..." autofocus></textarea>
        </div>
        <div class="block-actions">
            <button class="action-btn save-btn" onclick="saveTextBlock(this)" title="Сохранить">
                <i class="fas fa-check"></i>
            </button>
            <button class="action-btn delete-btn" onclick="deleteBlock(this)" title="Удалить">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    `;
    
    contentArea.appendChild(textBlock);
    
    // Фокус на текстовую область
    const textarea = textBlock.querySelector('.text-editor');
    textarea.focus();
    
    // Автоматическое изменение размера
    textarea.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = this.scrollHeight + 'px';
    });
    
    // Сохранение на Ctrl+Enter
    textarea.addEventListener('keydown', function(e) {
        if (e.ctrlKey && e.key === 'Enter') {
            saveTextBlock(textBlock.querySelector('.save-btn'));
        }
    });
}

// Сохранение текстового блока
function saveTextBlock(button) {
    const block = button.closest('.content-block');
    const textarea = block.querySelector('.text-editor');
    const text = textarea.value.trim();
    
    if (!text) {
        deleteBlock(button);
        return;
    }
    
    // Заменяем textarea на обычный div с текстом
    const textContent = document.createElement('div');
    textContent.className = 'text-content';
    textContent.innerHTML = text.replace(/\n/g, '<br>');
    
    const blockContent = block.querySelector('.block-content');
    blockContent.innerHTML = '';
    blockContent.appendChild(textContent);
    
    // Заменяем кнопку сохранения на кнопку редактирования
    button.innerHTML = '<i class="fas fa-edit"></i>';
    button.onclick = function() { editTextBlock(this); };
    button.className = 'action-btn edit-btn';
    button.title = 'Редактировать';
    
    block.classList.remove('active');
    
    // Здесь можно добавить AJAX сохранение на сервер
    saveContentToServer();
}

// Редактирование текстового блока
function editTextBlock(button) {
    const block = button.closest('.content-block');
    const textContent = block.querySelector('.text-content');
    const text = textContent.innerHTML.replace(/<br>/g, '\n');
    
    // Заменяем div на textarea
    const textarea = document.createElement('textarea');
    textarea.className = 'text-editor';
    textarea.value = text;
    
    const blockContent = block.querySelector('.block-content');
    blockContent.innerHTML = '';
    blockContent.appendChild(textarea);
    
    // Заменяем кнопку редактирования на кнопку сохранения
    button.innerHTML = '<i class="fas fa-check"></i>';
    button.onclick = function() { saveTextBlock(this); };
    button.className = 'action-btn save-btn';
    button.title = 'Сохранить';
    
    block.classList.add('active');
    textarea.focus();
    
    // Автоматическое изменение размера
    textarea.style.height = 'auto';
    textarea.style.height = textarea.scrollHeight + 'px';
    
    textarea.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = this.scrollHeight + 'px';
    });
}

// Добавление изображения
function addImageBlock() {
    const modal = new bootstrap.Modal(document.getElementById('uploadImageModal'));
    modal.show();
}

// Обработка загрузки изображения
function handleImageUpload(input) {
    const file = input.files[0];
    if (!file) return;
    
    currentUploadFile = file;
    
    const reader = new FileReader();
    reader.onload = function(e) {
        const preview = document.getElementById('imagePreview');
        const previewImage = document.getElementById('previewImage');
        
        previewImage.src = e.target.result;
        preview.style.display = 'block';
    };
    reader.readAsDataURL(file);
}

// Вставка изображения в контент
function insertImage() {
    if (!currentUploadFile) return;
    
    const contentArea = document.getElementById('contentArea');
    const emptyState = contentArea.querySelector('.empty-state');
    
    if (emptyState) {
        emptyState.remove();
    }
    
    const blockId = 'block_' + blockIdCounter++;
    const imageBlock = document.createElement('div');
    imageBlock.className = 'content-block image-block';
    imageBlock.setAttribute('data-type', 'image');
    imageBlock.setAttribute('data-id', blockId);
    
    const reader = new FileReader();
    reader.onload = function(e) {
        imageBlock.innerHTML = `
            <div class="block-content">
                <div class="image-content">
                    <img src="${e.target.result}" alt="Изображение" style="max-width: 100%; height: auto;">
                </div>
            </div>
            <div class="block-actions">
                <button class="action-btn delete-btn" onclick="deleteBlock(this)" title="Удалить">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
    };
    reader.readAsDataURL(currentUploadFile);
    
    contentArea.appendChild(imageBlock);
    
    // Закрываем модальное окно
    const modal = bootstrap.Modal.getInstance(document.getElementById('uploadImageModal'));
    modal.hide();
    
    // Очищаем форму
    document.getElementById('imageInput').value = '';
    document.getElementById('imagePreview').style.display = 'none';
    currentUploadFile = null;
    
    // Сохраняем на сервер
    saveContentToServer();
}

// Добавление видео
function addVideoBlock() {
    const modal = new bootstrap.Modal(document.getElementById('uploadVideoModal'));
    modal.show();
}

// Обработка загрузки видео
function handleVideoUpload(input) {
    const file = input.files[0];
    if (!file) return;
    
    currentUploadFile = file;
}

// Вставка видео в контент
function insertVideo() {
    const videoUrl = document.getElementById('videoUrl').value;
    const contentArea = document.getElementById('contentArea');
    const emptyState = contentArea.querySelector('.empty-state');
    
    if (emptyState) {
        emptyState.remove();
    }
    
    const blockId = 'block_' + blockIdCounter++;
    const videoBlock = document.createElement('div');
    videoBlock.className = 'content-block video-block';
    videoBlock.setAttribute('data-type', 'video');
    videoBlock.setAttribute('data-id', blockId);
    
    if (videoUrl) {
        // Обработка YouTube ссылок
        let embedUrl = videoUrl;
        if (videoUrl.includes('youtube.com/watch?v=')) {
            const videoId = videoUrl.split('v=')[1].split('&')[0];
            embedUrl = `https://www.youtube.com/embed/${videoId}`;
        } else if (videoUrl.includes('youtu.be/')) {
            const videoId = videoUrl.split('youtu.be/')[1].split('?')[0];
            embedUrl = `https://www.youtube.com/embed/${videoId}`;
        }
        
        videoBlock.innerHTML = `
            <div class="block-content">
                <div class="video-content">
                    <iframe src="${embedUrl}" frameborder="0" allowfullscreen style="width: 100%; height: 315px; border-radius: 6px;"></iframe>
                </div>
            </div>
            <div class="block-actions">
                <button class="action-btn delete-btn" onclick="deleteBlock(this)" title="Удалить">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
    } else if (currentUploadFile) {
        const reader = new FileReader();
        reader.onload = function(e) {
            videoBlock.innerHTML = `
                <div class="block-content">
                    <div class="video-content">
                        <video controls style="width: 100%; border-radius: 6px;">
                            <source src="${e.target.result}" type="${currentUploadFile.type}">
                            Ваш браузер не поддерживает воспроизведение видео.
                        </video>
                    </div>
                </div>
                <div class="block-actions">
                    <button class="action-btn delete-btn" onclick="deleteBlock(this)" title="Удалить">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `;
        };
        reader.readAsDataURL(currentUploadFile);
    }
    
    contentArea.appendChild(videoBlock);
    
    // Закрываем модальное окно
    const modal = bootstrap.Modal.getInstance(document.getElementById('uploadVideoModal'));
    modal.hide();
    
    // Очищаем форму
    document.getElementById('videoInput').value = '';
    document.getElementById('videoUrl').value = '';
    currentUploadFile = null;
    
    // Сохраняем на сервер
    saveContentToServer();
}

// Добавление файла
function addFileBlock() {
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = '*/*';
    
    input.onchange = function(e) {
        const file = e.target.files[0];
        if (!file) return;
        
        const contentArea = document.getElementById('contentArea');
        const emptyState = contentArea.querySelector('.empty-state');
        
        if (emptyState) {
            emptyState.remove();
        }
        
        const blockId = 'block_' + blockIdCounter++;
        const fileBlock = document.createElement('div');
        fileBlock.className = 'content-block file-block';
        fileBlock.setAttribute('data-type', 'file');
        fileBlock.setAttribute('data-id', blockId);
        
        // Определяем иконку файла по расширению
        const extension = file.name.split('.').pop().toLowerCase();
        let icon = 'fa-file';
        
        if (['jpg', 'jpeg', 'png', 'gif', 'bmp'].includes(extension)) {
            icon = 'fa-file-image';
        } else if (['mp4', 'avi', 'mov', 'wmv'].includes(extension)) {
            icon = 'fa-file-video';
        } else if (['mp3', 'wav', 'flac'].includes(extension)) {
            icon = 'fa-file-audio';
        } else if (['pdf'].includes(extension)) {
            icon = 'fa-file-pdf';
        } else if (['doc', 'docx'].includes(extension)) {
            icon = 'fa-file-word';
        } else if (['xls', 'xlsx'].includes(extension)) {
            icon = 'fa-file-excel';
        } else if (['zip', 'rar', '7z'].includes(extension)) {
            icon = 'fa-file-archive';
        }
        
        fileBlock.innerHTML = `
            <div class="block-content">
                <div class="file-content">
                    <div class="file-icon">
                        <i class="fas ${icon}"></i>
                    </div>
                    <div class="file-info">
                        <div class="file-name">${file.name}</div>
                        <div class="file-size">${formatFileSize(file.size)}</div>
                    </div>
                </div>
            </div>
            <div class="block-actions">
                <button class="action-btn delete-btn" onclick="deleteBlock(this)" title="Удалить">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
        
        contentArea.appendChild(fileBlock);
        
        // Сохраняем на сервер
        saveContentToServer();
    };
    
    input.click();
}

// Удаление блока
function deleteBlock(button) {
    const block = button.closest('.content-block');
    const contentArea = document.getElementById('contentArea');
    
    block.remove();
    
    // Если не осталось блоков, показываем пустое состояние
    if (contentArea.children.length === 0) {
        contentArea.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-plus-circle"></i>
                <h4>Добавьте контент к задаче</h4>
                <p>Нажмите на любую кнопку выше, чтобы добавить текст, изображения или другие файлы</p>
            </div>
        `;
    }
    
    // Сохраняем изменения на сервер
    saveContentToServer();
}

// Сохранение всего контента на сервер
function saveContent() {
    saveContentToServer();
    showNotification('Контент сохранен');
}

// AJAX сохранение контента
function saveContentToServer() {
    const contentArea = document.getElementById('contentArea');
    const blocks = contentArea.querySelectorAll('.content-block');
    
    const content = [];
    
    blocks.forEach(block => {
        const type = block.getAttribute('data-type');
        const id = block.getAttribute('data-id');
        
        let blockData = { type, id };
        
        if (type === 'text') {
            const textContent = block.querySelector('.text-content');
            if (textContent) {
                blockData.content = textContent.innerHTML;
            }
        } else if (type === 'image') {
            const img = block.querySelector('img');
            if (img) {
                blockData.src = img.src;
                blockData.alt = img.alt;
            }
        } else if (type === 'video') {
            const video = block.querySelector('video source');
            const iframe = block.querySelector('iframe');
            if (video) {
                blockData.src = video.src;
                blockData.type = video.type;
            } else if (iframe) {
                blockData.src = iframe.src;
                blockData.type = 'embed';
            }
        } else if (type === 'file') {
            const fileName = block.querySelector('.file-name');
            const fileSize = block.querySelector('.file-size');
            if (fileName && fileSize) {
                blockData.name = fileName.textContent;
                blockData.size = fileSize.textContent;
            }
        }
        
        content.push(blockData);
    });
    
    // AJAX запрос для сохранения контента
    fetch(`/api/tasks/${taskId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({
            content: JSON.stringify(content)
        })
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            console.error('Ошибка сохранения контента:', data.message);
        }
    })
    .catch(error => {
        console.error('Ошибка:', error);
    });
}

// Форматирование размера файла
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// Drag and Drop для файлов
document.addEventListener('DOMContentLoaded', function() {
    const contentArea = document.getElementById('contentArea');
    
    contentArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        e.stopPropagation();
        contentArea.style.backgroundColor = '#e3f2fd';
    });
    
    contentArea.addEventListener('dragleave', function(e) {
        e.preventDefault();
        e.stopPropagation();
        contentArea.style.backgroundColor = '';
    });
    
    contentArea.addEventListener('drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        contentArea.style.backgroundColor = '';
        
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            Array.from(files).forEach(file => {
                if (file.type.startsWith('image/')) {
                    currentUploadFile = file;
                    insertImage();
                } else if (file.type.startsWith('video/')) {
                    currentUploadFile = file;
                    insertVideo();
                } else {
                    // Добавляем как обычный файл
                    const event = { target: { files: [file] } };
                    addFileBlock();
                }
            });
        }
    });
});

// ==================== ФУНКЦИИ ДЛЯ РАБОТЫ С ПОЛОТНОМ КОНТЕНТА ====================

// Добавление текстового блока
function addTextBlock() {
    const canvas = document.getElementById('contentCanvas');
    const emptyState = canvas.querySelector('.empty-canvas');
    
    if (emptyState) {
        emptyState.remove();
    }
    
    const blockId = 'block_' + Date.now();
    const textBlock = document.createElement('div');
    textBlock.className = 'content-block text-block active';
    textBlock.setAttribute('data-type', 'text');
    textBlock.setAttribute('data-id', blockId);
    
    textBlock.innerHTML = `
        <div class="block-content">
            <div class="text-content editable" contenteditable="true"></div>
        </div>
        <div class="block-toolbar">
            <button class="block-btn" onclick="formatText('bold')" title="Жирный">
                <i class="fas fa-bold"></i>
            </button>
            <button class="block-btn" onclick="formatText('italic')" title="Курсив">
                <i class="fas fa-italic"></i>
            </button>
            <button class="block-btn" onclick="formatText('underline')" title="Подчеркнутый">
                <i class="fas fa-underline"></i>
            </button>
            <button class="block-btn delete-btn" onclick="deleteBlock(this)" title="Удалить">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    `;
    
    canvas.appendChild(textBlock);
    
    // Устанавливаем фокус на новый блок
    const textContent = textBlock.querySelector('.text-content');
    textContent.focus();
    
    // Обработчики событий
    setupBlockEvents(textBlock);
}

// Форматирование текста
function formatText(command) {
    document.execCommand(command, false, null);
}

// Открытие загрузки изображений
function openImageUpload() {
    document.getElementById('imageUpload').click();
}

// Открытие загрузки видео
function openVideoUpload() {
    document.getElementById('videoUpload').click();
}

// Открытие загрузки файлов
function openFileUpload() {
    document.getElementById('fileUpload').click();
}

// Простая загрузка файла на сервер
function uploadFileToServer(file) {
    const formData = new FormData();
    formData.append('file', file);
    formData.append('_token', document.querySelector('[data-csrf-token]').getAttribute('data-csrf-token'));
    
    // Показываем индикатор загрузки
    showUploadProgress();
    
    fetch(`/tasks/${getCurrentTaskId()}/upload`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('[data-csrf-token]').getAttribute('data-csrf-token')
        }
    })
    .then(response => response.json())
    .then(data => {
        hideUploadProgress();
        
        if (data.success) {
            // Добавляем HTML файла к контенту
            const canvas = document.getElementById('contentCanvas');
            const emptyState = canvas.querySelector('.empty-canvas');
            
            if (emptyState) {
                emptyState.remove();
            }
            
            // Добавляем HTML к содержимому
            canvas.insertAdjacentHTML('beforeend', data.file_html);
            
            // Показываем уведомление
            showNotification('Файл успешно загружен', 'success');
            
            // Автоматически сохраняем контент
            saveContent();
        } else {
            showNotification(data.message || 'Ошибка при загрузке файла', 'error');
        }
    })
    .catch(error => {
        hideUploadProgress();
        console.error('Error:', error);
        showNotification('Ошибка при загрузке файла', 'error');
    });
}

// Получение ID текущей задачи
function getCurrentTaskId() {
    return document.querySelector('[data-task-id]').getAttribute('data-task-id');
}

// Показать индикатор загрузки
function showUploadProgress() {
    const indicator = document.createElement('div');
    indicator.id = 'uploadIndicator';
    indicator.className = 'upload-indicator';
    indicator.innerHTML = `
        <div class="upload-spinner">
            <i class="fas fa-spinner fa-spin"></i>
            <span>Загрузка файла...</span>
        </div>
    `;
    document.body.appendChild(indicator);
}

// Скрыть индикатор загрузки
function hideUploadProgress() {
    const indicator = document.getElementById('uploadIndicator');
    if (indicator) {
        indicator.remove();
    }
}

// Показать уведомление
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
        <span>${message}</span>
    `;
    
    document.body.appendChild(notification);
    
    // Автоматически скрываем через 3 секунды
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Обработка загрузки изображений
function handleImageUpload(event) {
    const files = event.target.files;
    
    for (let file of files) {
        if (file.type.startsWith('image/')) {
            uploadFileToServer(file);
        }
    }
    
    event.target.value = '';
}

// Обработка загрузки видео
function handleVideoUpload(event) {
    const file = event.target.files[0];
    
    if (file && file.type.startsWith('video/')) {
        uploadFileToServer(file);
    }
    
    event.target.value = '';
}

// Обработка загрузки файлов
function handleFileUpload(event) {
    const file = event.target.files[0];
    
    if (file) {
        uploadFileToServer(file);
    }
    
    event.target.value = '';
}

// Удаление блока
function deleteBlock(button) {
    const block = button.closest('.content-block');
    if (confirm('Удалить этот элемент?')) {
        block.remove();
        
        // Если не осталось блоков, показываем пустое состояние
        const canvas = document.getElementById('contentCanvas');
        if (!canvas.querySelector('.content-block')) {
            showEmptyState();
        }
    }
}

// Показать пустое состояние
function showEmptyState() {
    const canvas = document.getElementById('contentCanvas');
    canvas.innerHTML = `
        <div class="empty-canvas">
            <div class="empty-icon">
                <i class="fas fa-edit"></i>
            </div>
            <h3>Создайте содержимое задачи</h3>
            <p>Добавьте текст, изображения, видео или файлы, чтобы описать задачу более подробно</p>
            <button class="btn btn-primary btn-lg" onclick="addTextBlock()">
                <i class="fas fa-plus"></i> Начать писать
            </button>
        </div>
    `;
}

// Настройка событий для блока
function setupBlockEvents(block) {
    // Клик по блоку - активация
    block.addEventListener('click', function() {
        document.querySelectorAll('.content-block.active').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
    });
    
    // Обработка редактируемых элементов
    const editableElements = block.querySelectorAll('.editable');
    editableElements.forEach(element => {
        element.addEventListener('input', function() {
            // Автосохранение при изменении
            debounce(saveContent, 2000, 'content');
        });
        
        element.addEventListener('paste', function(e) {
            // Обработка вставки для сохранения форматирования
            e.preventDefault();
            const text = e.clipboardData.getData('text/plain');
            document.execCommand('insertText', false, text);
        });
    });
}

// Сохранение контента
function saveContent() {
    const canvas = document.getElementById('contentCanvas');
    const content = canvas.innerHTML;
    
    fetch(`/api/tasks/${taskId}/content`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({
            content: content
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Контент сохранен', 'success');
        } else {
            showNotification('Ошибка сохранения', 'error');
        }
    })
    .catch(error => {
        console.error('Ошибка:', error);
        showNotification('Ошибка сохранения', 'error');
    });
}

// Утилитарные функции
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function getFileIcon(mimeType) {
    if (mimeType.startsWith('image/')) return 'fa-image';
    if (mimeType.startsWith('video/')) return 'fa-video';
    if (mimeType.startsWith('audio/')) return 'fa-music';
    if (mimeType.includes('pdf')) return 'fa-file-pdf';
    if (mimeType.includes('word')) return 'fa-file-word';
    if (mimeType.includes('excel') || mimeType.includes('spreadsheet')) return 'fa-file-excel';
    if (mimeType.includes('powerpoint') || mimeType.includes('presentation')) return 'fa-file-powerpoint';
    if (mimeType.includes('zip') || mimeType.includes('rar')) return 'fa-file-archive';
    return 'fa-file';
}

// Drag and Drop функциональность
document.addEventListener('DOMContentLoaded', function() {
    const canvas = document.getElementById('contentCanvas');
    
    // Предотвращаем стандартное поведение браузера
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        canvas.addEventListener(eventName, preventDefaults, false);
        document.body.addEventListener(eventName, preventDefaults, false);
    });
    
    // Подсветка при наведении
    ['dragenter', 'dragover'].forEach(eventName => {
        canvas.addEventListener(eventName, highlight, false);
    });
    
    ['dragleave', 'drop'].forEach(eventName => {
        canvas.addEventListener(eventName, unhighlight, false);
    });
    
    // Обработка сброса файлов
    canvas.addEventListener('drop', handleDrop, false);
    
    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    function highlight() {
        canvas.classList.add('drag-over');
    }
    
    function unhighlight() {
        canvas.classList.remove('drag-over');
    }
    
    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        
        handleFiles(files);
    }
    
    function handleFiles(files) {
        Array.from(files).forEach(file => {
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    addImageBlock(e.target.result, file.name);
                };
                reader.readAsDataURL(file);
            } else if (file.type.startsWith('video/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    addVideoBlock(e.target.result, file.name);
                };
                reader.readAsDataURL(file);
            } else {
                addFileBlock(file);
            }
        });
    }
});

// Стили для индикатора загрузки и уведомлений
const uploadStyles = `
/* Индикатор загрузки */
.upload-indicator {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: rgba(0, 0, 0, 0.8);
    color: white;
    padding: 20px 30px;
    border-radius: 10px;
    z-index: 9999;
    text-align: center;
}

.upload-spinner {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 16px;
}

.upload-spinner i {
    font-size: 20px;
}

/* Уведомления */
.notification {
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 15px 20px;
    border-radius: 8px;
    color: white;
    z-index: 9999;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 14px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    animation: slideIn 0.3s ease-out;
}

.notification-success {
    background: #28a745;
}

.notification-error {
    background: #dc3545;
}

.notification-info {
    background: #007bff;
}

@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

/* Стили для прикрепленных файлов */
.file-attachment {
    margin: 15px 0;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    overflow: hidden;
    background: #f8f9fa;
}

.file-attachment img,
.file-attachment video {
    display: block;
    max-width: 100%;
    height: auto;
    border-radius: 8px 8px 0 0;
}

.file-attachment .file-info {
    padding: 10px;
    background: white;
    border-top: 1px solid #e9ecef;
    font-size: 12px;
    color: #6c757d;
}

.file-attachment .btn {
    margin: 10px;
}
`;

// Добавляем стили в head
if (!document.getElementById('upload-styles')) {
    const styleSheet = document.createElement('style');
    styleSheet.id = 'upload-styles';
    styleSheet.innerHTML = uploadStyles;
    document.head.appendChild(styleSheet);
}
</script>

@endsection
