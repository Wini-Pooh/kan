@extends('layouts.app')

@section('content')
    <!-- Мобильная обертка для фиксированной шапки -->
    <div class="mobile-header-wrapper">
        <div class="task-header-fixed content-locked" data-task-id="{{ $task->id }}" data-csrf-token="{{ csrf_token() }}" id="taskHeader">
            <div class="container-fluid">
                <div class="row align-items-center d-flex justify-content-between">
                    <div class="col-md-4 col-4">
                        <div class="task-title-container">
                            <a href="{{ route('spaces.show', [$task->space->organization, $task->space]) }}" 
                               class="back-to-kanban-btn" 
                               title="Вернуться к канбану">
                                <i class="fas fa-arrow-left"></i>
                            </a>
                            <h2 class="task-title" id="taskTitle" onclick="editTaskTitle()">
                                {{ $task->title ?? 'Без названия' }}
                            </h2>
                            <input type="text" class="form-control task-title-input d-none" id="taskTitleInput"
                                value="{{ $task->title ?? '' }}" onkeypress="handleTitleKeyPress(event)"
                                onblur="saveTaskTitle()" maxlength="200">
                        </div>
                    </div>
                 

                <!-- Правая часть - Действия -->
                <div class="col-md-4 col-4 text-end">
                    <div class="d-flex justify-content-end align-items-center gap-3">
                        <!-- Кнопки действий -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="task-content mt-4">
        <div class="container-fluid content-locked" id="taskContent">
            <!-- Скрытый input для загрузки изображений (множественный) -->
            <input type="file" id="imageUploader" accept="image/*,video/*" style="display: none;" multiple
                onchange="handleImageUpload(event)">
                
            <!-- Скрытый input для загрузки документов (множественный) -->
            <input type="file" id="documentUploader" accept=".pdf,.doc,.docx,.txt,.zip,.rar,.xls,.xlsx,.ppt,.pptx" style="display: none;" multiple
                onchange="handleDocumentUpload(event)">
                
            <!-- Скрытый input для добавления изображений в существующий блок -->
            <input type="file" id="additionalImageUploader" accept="image/*,video/*" style="display: none;" multiple
                onchange="handleAdditionalImageUpload(event)">
                
            <!-- Скрытый input для добавления документов в существующий блок -->
            <input type="file" id="additionalDocumentUploader" accept=".pdf,.doc,.docx,.txt,.zip,.rar,.xls,.xlsx,.ppt,.pptx" style="display: none;" multiple
                onchange="handleAdditionalDocumentUpload(event)">

            <!-- Полотно для контента (листы A4) -->
            <div class="content-canvas" id="contentCanvas">
                @if (!empty($task->content))
                    {!! $task->parsed_content !!}
                @else
                    <!-- Первый пустой лист A4 -->
                    <div class="a4-page" data-page="1">
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
                            <div class="empty-page">
                                <i class="fas fa-plus-circle"></i>
                                <p>Нажмите здесь, чтобы добавить контент</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Модальное окно для просмотра изображений -->
            <div class="modal fade" id="imageModal" tabindex="-1">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Просмотр изображения</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body text-center">
                            <img id="modalImage" src="" alt="" class="img-fluid">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Индикатор автосохранения -->
            <div class="auto-save-indicator" id="autoSaveIndicator">
                <i class="fas fa-sync-alt fa-spin"></i>
                <span>Сохранение...</span>
            </div>
        </div>
    </div>

    <!-- Фиксированная нижняя панель инструментов -->
    <div class="bottom-toolbar-fixed">
        <div class="bottom-toolbar-content">
            <!-- Аватар создателя задачи (слева) -->
            @if ($task->creator)
                <div class="creator-avatar-container" title="Создатель: {{ $task->creator->name }}">
                    <img src="{{ $task->creator->avatar ?? '/images/default-avatar.png' }}"
                        alt="{{ $task->creator->name }}" class="creator-avatar">
                </div>
            @endif

            <!-- Центральная группа кнопок -->
            <div class="toolbar-center-group">
               
            <button class="toolbar-icon disabled" onclick="addTextBlock()" title="Добавить текстовый блок">
                <i class="fas fa-font"></i>
            </button>
            <button class="toolbar-icon disabled" onclick="triggerImageUpload()" title="Добавить изображение">
                <i class="fas fa-image"></i>
            </button>
            
            <!-- Кнопка блокировки с выплывающим меню -->
            <div class="lock-button-container position-relative">
                <button class="toolbar-icon lock-icon" id="editLockToggle" onclick="toggleEditMode()" title="Разблокировать редактирование" onmouseover="showLockMenu()" onmouseout="hideLockMenu()">
                    <i class="fas fa-lock" id="lockIcon"></i>
                </button>
                
                <!-- Выплывающее меню -->
                <div class="lock-menu d-none" id="lockMenu" onmouseover="keepLockMenuOpen()" onmouseout="hideLockMenu()">
                   
                        <div class="d-flex justify-content-center align-items-center gap-3">
                          
                                <button class="action-btn print-btn" onclick="printTask()" title="Распечатать задачу">
                                    <!-- SVG иконка принтера -->
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                                        <path d="M6 9V4h12v5" stroke="currentColor" stroke-width="2" />
                                        <rect x="4" y="9" width="16" height="7" rx="2" stroke="currentColor"
                                            stroke-width="2" />
                                        <path d="M6 16v4h12v-4" stroke="currentColor" stroke-width="2" />
                                    </svg>
                                </button>
                                <button class="action-btn pdf-btn" onclick="downloadTaskPDF()" title="Скачать PDF">
                                    <!-- SVG иконка PDF -->
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                                        <rect x="3" y="3" width="18" height="18" rx="2" stroke="currentColor"
                                            stroke-width="2" /><text x="7" y="16" font-size="8" fill="currentColor">PDF</text>
                                    </svg>
                                </button>
                                @if(($task->space->created_by === Auth::id()) || ($task->created_by === Auth::id()))
                                    @if($task->isArchived())
                                        <button class="action-btn archive-btn" onclick="unarchiveTask()" title="Разархивировать задачу">
                                            <i class="fas fa-undo"></i>
                                        </button>
                                    @else
                                        <button class="action-btn archive-btn" onclick="archiveTask()" title="Архивировать задачу">
                                            <i class="fas fa-archive"></i>
                                        </button>
                                    @endif
                                @endif
                           
                            <!-- Иконка времени/календаря -->
                       
                                <i class="fas fa-calendar-alt time-icon" onclick="toggleDatePicker()"></i>
                                <!-- Календарь -->
                                <div class="date-picker-container d-none" id="datePickerContainer">
                                    <div class="date-picker-header">Сроки выполнения</div>

                                    <!-- Дата начала -->
                                    <div class="date-field mb-3">
                                        <label for="startDateInput" class="form-label">Дата начала:</label>
                                        <input type="date" class="form-control" id="startDateInput"
                                            value="{{ $task->start_date ?? '' }}" onchange="saveStartDate()">
                                    </div>

                                    <!-- Дата окончания -->
                                    <div class="date-field mb-3">
                                        <label for="dueDateInput" class="form-label">Дата окончания:</label>
                                        <input type="date" class="form-control" id="dueDateInput"
                                            value="{{ $task->due_date ?? '' }}" onchange="saveDueDate()">
                                    </div>

                                    <div class="date-picker-actions">
                                        <button class="btn btn-sm btn-outline-danger" onclick="clearAllDates()">Очистить
                                            все</button>
                                        <button class="btn btn-sm " onclick="closeDatePicker()">Готово</button>
                                    </div>
                                </div>
                            
                        </div>
                   
                </div>
            </div>
            
            <button class="toolbar-icon disabled" onclick="addPageBreak()" title="Добавить новый лист">
                <i class="fas fa-plus-square"></i>
            </button>
            <button class="toolbar-icon disabled" onclick="triggerDocumentUpload()" title="Добавить документ">
                <i class="fas fa-file-alt"></i>
            </button>
          
         
            <div class="save-status d-none" id="saveStatus">
                <i class="fas fa-check-circle"></i>
                <span>Сохранено</span>
            </div>
            </div>

            <!-- Исполнитель задачи (справа) -->
            <div class="assignee-container position-relative">
                @if ($task->assignee_id)
                    <!-- Если есть исполнитель -->
                    <div class="assignee-avatar-toolbar" data-priority="{{ $task->priority ?? 'low' }}" onclick="toggleAssigneeMenu()" title="Исполнитель: {{ $task->assignee->name ?? 'Пользователь' }}">
                        <img src="{{ $task->assignee->avatar ?? '/images/default-avatar.png' }}"
                            alt="{{ $task->assignee->name ?? 'Пользователь' }}" class="assignee-avatar-img">
                    </div>
                @else
                    <!-- Если нет исполнителя -->
                    <div class="no-assignee-toolbar" onclick="toggleAssigneeMenu()" title="Назначить исполнителя">
                        <i class="fas fa-user-plus"></i>
                    </div>
                @endif

                <!-- Выпадающий список участников -->
                <div class="assignee-menu d-none" id="assigneeMenu">
                    <!-- Блок выбора приоритета (компактный) -->
                    <div class="priority-selector-compact" data-priority="{{ $task->priority ?? 'low' }}">
                        <div class="priority-selector-header">Приоритет задачи:</div>
                        <div class="priority-circles-row">
                            <div class="priority-circle-option low" data-priority="low" onclick="changePriorityCompact('low')" title="Низкий"></div>
                            <div class="priority-circle-option medium" data-priority="medium" onclick="changePriorityCompact('medium')" title="Средний"></div>
                            <div class="priority-circle-option high" data-priority="high" onclick="changePriorityCompact('high')" title="Высокий"></div>
                            <div class="priority-circle-option urgent" data-priority="urgent" onclick="changePriorityCompact('urgent')" title="Срочный"></div>
                            <div class="priority-circle-option critical" data-priority="critical" onclick="changePriorityCompact('critical')" title="Критический"></div>
                            <div class="priority-circle-option blocked" data-priority="blocked" onclick="changePriorityCompact('blocked')" title="Заблокирован"></div>
                        </div>
                    </div>
                    
                    <div class="assignee-menu-header">Выберите исполнителя</div>
                    <div class="assignee-options">
                        <div class="assignee-option" data-user-id="" onclick="changeAssignee('')">
                            <div class="user-avatar-small">
                                <i class="fas fa-user-slash"></i>
                            </div>
                            <span>Не назначен</span>
                        </div>
                        @foreach ($space->activeMembers as $member)
                            <div class="assignee-option" data-user-id="{{ $member->id }}"
                                onclick="changeAssignee('{{ $member->id }}')">
                                <div class="user-avatar-small">
                                    <img src="{{ $member->avatar ?? '/images/default-avatar.png' }}"
                                        alt="{{ $member->name }}">
                                </div>
                                <span>{{ $member->name }}</span>
                                @if ($member->id === $task->assignee_id)
                                    <i class="fas fa-check text-success ms-auto"></i>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div> <!-- Закрываем мобильную обертку шапки -->
    @include('task.show_style')
    @vite(['resources/css/task-editor.css', 'resources/css/keyboard-shortcuts.css', 'resources/js/task-show.js', 'resources/js/keyboard-shortcuts.js'])
    <style>.sidebar-toggle {
        display: NONE !important }.modal-backdrop.fade.show {
    display: none;
}</style>
@endsection
