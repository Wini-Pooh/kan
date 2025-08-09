@extends('layouts.app')

@section('content')
    <div class="task-header-fixed" data-task-id="{{ $task->id }}" data-csrf-token="{{ csrf_token() }}">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <div class="task-title-container">
                        <!-- Кнопка возврата к канбану -->
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

                <div class="col-md-4 text-center">
                    <div class="d-flex justify-content-center align-items-center gap-3">
                        <div class="status-container position-relative">
                            <div class="status-square" id="statusSquare" data-priority="{{ $task->priority ?? 'low' }}"
                                onclick="toggleStatusMenu()">
                            </div>
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
                                    <div class="priority-option" data-priority="critical"
                                        onclick="changePriority('critical')">
                                        <div class="priority-circle critical"></div>
                                        <span>Критический</span>
                                    </div>
                                    <div class="priority-option" data-priority="blocked"
                                        onclick="changePriority('blocked')">
                                        <div class="priority-circle blocked"></div>
                                        <span>Заблокирован</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="task-actions">
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

                <!-- Правая часть - Действия и Исполнитель -->
                <div class="col-md-4 text-end">
                    <div class="d-flex justify-content-end align-items-center gap-3">
                        <!-- Кнопки действий -->


                        <!-- Исполнитель -->
                        <div class="assignee-container position-relative">
                            @if ($task->assignee_id)
                                <!-- Если есть исполнитель -->
                                <div class="assignee-avatar" onclick="toggleAssigneeMenu()">
                                    <img src="{{ $task->assignee->avatar ?? '/images/default-avatar.png' }}"
                                        alt="{{ $task->assignee->name ?? 'Пользователь' }}" class="user-avatar">
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
            </div>
        </div>
    </div>

    <div class="task-content mt-4">
        <div class="container-fluid">
            <!-- Скрытый input для загрузки файлов -->
            <input type="file" id="imageUploader" accept="image/*" style="display: none;"
                onchange="handleImageUpload(event)">

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
            <button class="toolbar-icon" onclick="addTextBlock()" title="Добавить текстовый блок">
                <i class="fas fa-font"></i>
            </button>
            <button class="toolbar-icon" onclick="triggerImageUpload()" title="Добавить изображение">
                <i class="fas fa-image"></i>
            </button>
            <button class="toolbar-icon" onclick="addPageBreak()" title="Добавить новый лист">
                <i class="fas fa-file-alt"></i>
            </button>
            <button class="toolbar-icon" onclick="toggleFormatMenu()" title="Форматирование текста (выделите текст)">
                <i class="fas fa-palette"></i>
            </button>
            <button class="toolbar-icon save-icon" onclick="saveContent()" title="Сохранить содержимое">
                <i class="fas fa-save"></i>
            </button>
            <div class="save-status d-none" id="saveStatus">
                <i class="fas fa-check-circle"></i>
                <span>Сохранено</span>
            </div>
        </div>
    </div>
    @include('task.show_style')
    @vite(['resources/css/task-editor.css', 'resources/js/task-show.js'])
@endsection
