@extends('layouts.app')

@section('content')
<style>
    /* Базовые стили для элементов колонки */
    .sidebar-toggle {
        display: none !important
    }
    .column-info {
        position: relative;
        max-width: 180px;
        overflow: hidden;
    }
    
    .column-title {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        display: block;
        margin: 0;
        padding: 0;
        cursor: default;
        position: relative;
    }
    
    /* Стиль для бегущей строки */
    .column-title.marquee {
        overflow: visible;
        animation: marquee-scroll 6s linear infinite;
        animation-delay: 0.3s;
    }
    
    /* Анимация прокрутки текста */
    @keyframes marquee-scroll {
        0%, 10% { transform: translateX(0); }
        40%, 60% { transform: translateX(min(-50%, calc(-100% + 170px))); }
        90%, 100% { transform: translateX(0); }
    }
    
    /* Дополнительные стили для отладки */
    .column-title[data-needs-marquee="true"] {
        text-decoration: underline dotted rgba(0,0,0,0.2);
    }
    
    /* Стили для перетаскивания колонок */
    .column-header {
        cursor: grab;
    }
    
    .column-dragging {
        opacity: 0.6;
        pointer-events: none;
    }
    
    .column-drop-target-before {
        box-shadow: -4px 0 0 0 #3490dc;
    }
    
    .column-drop-target-after {
        box-shadow: 4px 0 0 0 #3490dc;
    }
    
    .kanban-column {
        transition: box-shadow 0.2s ease;
    }
</style>
<div class="kanban-container">
    <div class="kanban-header">
        <h1 class="kanban-title">
            <a href="{{ route('organizations.show', $organization) }}" 
               class="back-arrow-link" 
               title="Вернуться к организации {{ $organization->name }}">
                <i class="fas fa-arrow-left"></i>
            </a>
            <span class="board-title-text">{{ $space->name ?? 'Kanban Board' }}</span>
        </h1>
        <div class="header-actions">
            <a href="{{ route('spaces.archive', [$organization, $space]) }}" class="add-column-btn" style="background-color: #6c757d; text-decoration: none;">
                <i class="fas fa-archive"></i>
                <span class="d-none d-sm-inline">Архив</span>
                @php
                    $archivedCount = $space->archivedTasks()->count();
                @endphp
                @if($archivedCount > 0)
                    <span class="badge bg-light text-dark ms-1">{{ $archivedCount }}</span>
                @endif
            </a>
            
                {{-- <!-- Кнопки действий для всех устройств -->
                <a href="#" onclick="createNewColumn(); return false;" class="btn btn-sm btn-success" title="Добавить новую колонку">
                    <i class="fas fa-plus"></i>
                </a>
                <a href="#" onclick="showHiddenColumns(); return false;" class="btn btn-sm btn-info" title="Показать скрытые колонки">
                    <i class="fas fa-eye"></i>
                </a> --}}
        </div>
    </div>
    
    <div class="kanban-board" id="kanban-board">
        @if($columns && $columns->count() > 0)
            @foreach($columns as $column)
            <div class="kanban-column" data-column-id="{{ $column->id }}" data-status="{{ $column->slug }}">
                <div class="column-header" style="border-left: 4px solid {{ $column->color }}" draggable="true" data-column-id="{{ $column->id }}">
                    <div class="column-info">
                        <h3 class="column-title" ondblclick="editColumnNameInline({{ $column->id }})" title="Двойной клик для редактирования">{{ $column->name }}</h3>
                    </div>
                    <div class="column-actions">
                        <button class="column-action-btn add-task" onclick="createQuickTask({{ $column->id }}, '{{ $column->slug }}')" title="Добавить задачу">
                            <i class="fas fa-plus"></i>
                        </button>
                        @if(!$column->is_default)
                        <button class="column-action-btn delete" onclick="deleteColumn({{ $column->id }})" title="Удалить колонку">
                            <i class="fas fa-trash"></i>
                        </button>
                        @endif
                    </div>
                </div>
                <div class="column-content" 
                     ondrop="drop(event)" 
                     ondragover="allowDrop(event)"
                     data-column-id="{{ $column->id }}"
                     data-status="{{ $column->slug }}">
                    
                    @if($column->tasks->count() === 0)
                    <!-- Кнопка создания новой задачи в пустой колонке -->
                    <div class="add-task-placeholder" onclick="createQuickTask({{ $column->id }}, '{{ $column->slug }}')" style="margin-bottom: 15px;">
                        <i class="fas fa-plus"></i>
                        <span>Добавить задачу</span>
                    </div>
                    @endif
                    
                    @foreach($column->tasks as $task)
                    <div class="task-card {{ $task->status === 'done' ? 'completed' : '' }}" 
                         draggable="true" 
                         data-task-id="{{ $task->id }}">
                        <div class="task-header">
                            <div class="task-title" ondblclick="editTaskNameInline({{ $task->id }}, event)" title="Двойной клик для редактирования">{{ $task->title }}</div>
                            <div class="status-square medium" data-priority="{{ $task->priority ?? 'low' }}" 
                                 title="{{ $task->priority_label }}"
                                 onclick="toggleTaskStatusMenu({{ $task->id }})"></div>
                        </div>
                        
                        <!-- Мета-информация задачи -->
                        <div class="task-meta">
                            @if($task->start_date || $task->due_date)
                            <div class="task-dates">
                                @if($task->start_date)
                                    <span class="date-item" title="Дата начала">
                                        <i class="fas fa-calendar-plus"></i>
                                        {{ $task->start_date->format('d.m.Y') }}
                                    </span>
                                @endif
                                @if($task->due_date)
                                    <span class="date-item" title="Дата окончания">
                                        <i class="fas fa-calendar-minus"></i>
                                        {{ $task->due_date->format('d.m.Y') }}
                                    </span>
                                @endif
                            </div>
                            @endif
                            
                            <!-- Аватар назначенного пользователя -->
                            @if($task->assignedUser || $task->assignee)
                                @php
                                    $assignedUser = $task->assignedUser ?? $task->assignee;
                                @endphp
                                <div class="task-assignee">
                                    <div class="user-avatar" title="Назначено: {{ $assignedUser->name }}">
                                        @if($assignedUser->photo)
                                            <img src="{{ asset('storage/' . $assignedUser->photo) }}" alt="{{ $assignedUser->name }}">
                                        @else
                                            <div class="avatar-placeholder">
                                                {{ strtoupper(substr($assignedUser->name, 0, 1)) }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                        
                        <!-- Кнопки действий с задачей -->
                        @if(($space->created_by === Auth::id()) || ($task->created_by === Auth::id()))
                        <div class="task-actions" onclick="event.stopPropagation();">
                            <button class="task-action-btn archive-btn" onclick="archiveTaskFromBoard({{ $task->id }})" title="Архивировать задачу">
                                <i class="fas fa-archive"></i>
                            </button>
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
            
            <!-- Блок для добавления новой колонки -->
            <div class="kanban-column add-column-placeholder add-column-container" onclick="createNewColumn()">
                <div class="column-header add-column-header">
                    <div class="add-column-content">
                        <i class="fas fa-plus add-column-icon"></i>
                        <span class="add-column-text">Добавить колонку</span>
                    </div>
                </div>
            </div>
        @else
            <!-- Блок для добавления первой колонки -->
            <div class="kanban-column add-column-placeholder add-column-container" onclick="createNewColumn()">
                <div class="column-header add-column-header">
                    <div class="add-column-content">
                        <i class="fas fa-plus add-column-icon"></i>
                        <span class="add-column-text">Добавить первую колонку</span>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Модальное окно для редактирования колонки -->
<div class="modal fade" id="editColumnModal" tabindex="-1" aria-labelledby="editColumnModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editColumnModalLabel">Редактировать колонку</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editColumnForm">
                    <input type="hidden" id="editColumnId">
                    <div class="mb-3">
                        <label for="editColumnName" class="form-label">Название колонки</label>
                        <input type="text" class="form-control" id="editColumnName" required maxlength="50">
                    </div>
                    <div class="mb-3">
                        <label for="editColumnColor" class="form-label">Цвет колонки</label>
                        <input type="color" class="form-control form-control-color" id="editColumnColor">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="button" class="btn " onclick="updateColumn()">Сохранить изменения</button>
            </div>
        </div>
    </div>
</div>



<script>
let draggedElement = null;
let isDragging = false;
let dragStartTime = 0;
let mouseDownTarget = null;
let taskIdCounter = {{ ($tasks->flatten()->max('id') ?? 0) + 1 }}; // Начинаем с следующего ID после максимального
const spaceId = {{ $space->id }};
const currentUserId = {{ Auth::id() }};
const spaceCreatedBy = {{ $space->created_by }};
let csrfToken = '{{ csrf_token() }}';

// Функция для получения свежего CSRF токена
function getCsrfToken() {
    const metaToken = document.querySelector('meta[name="csrf-token"]');
    if (metaToken) {
        csrfToken = metaToken.getAttribute('content');
    }
    return csrfToken;
}

// Отладочная информация
console.log('Kanban Board initialized:', {
    spaceId: spaceId,
    taskIdCounter: taskIdCounter,
    csrfToken: csrfToken ? 'Present' : 'Missing'
});

// Функция для определения позиции вставки при сортировке
function getDragAfterElement(container, y) {
    const draggableElements = [...container.querySelectorAll('.task-card:not(.dragging)')];
    
    return draggableElements.reduce((closest, child) => {
        const box = child.getBoundingClientRect();
        const offset = y - box.top - box.height / 2;
        
        if (offset < 0 && offset > closest.offset) {
            return { offset: offset, element: child };
        } else {
            return closest;
        }
    }, { offset: Number.NEGATIVE_INFINITY }).element;
}

// Функции для drag and drop
function allowDrop(ev) {
    ev.preventDefault();
    ev.currentTarget.classList.add('drag-over');
    
    // Если перетаскиваем в рамках той же колонки, нужно определить позицию для вставки
    if (draggedElement) {
        const targetColumn = ev.currentTarget;
        const draggedColumn = draggedElement.closest('.column-content');
        
        // Если это та же колонка, обработаем сортировку
        if (targetColumn === draggedColumn) {
            const afterElement = getDragAfterElement(targetColumn, ev.clientY);
            const addButton = targetColumn.querySelector('.add-task-placeholder');
            
            if (afterElement == null) {
                // Вставляем в конец (после всех задач)
                targetColumn.appendChild(draggedElement);
            } else if (afterElement === addButton) {
                // Вставляем после кнопки добавления (в начало списка задач)
                addButton.insertAdjacentElement('afterend', draggedElement);
            } else {
                // Вставляем перед найденным элементом
                targetColumn.insertBefore(draggedElement, afterElement);
            }
        }
    }
}

function drag(ev) {
    console.log('Drag started for task:', ev.target.getAttribute('data-task-id'));
    isDragging = true;
    draggedElement = ev.target;
    ev.target.classList.add('dragging');
    ev.dataTransfer.setData("text", ev.target.getAttribute('data-task-id'));
    ev.dataTransfer.effectAllowed = 'move';
}

function drop(ev) {
    ev.preventDefault();
    ev.currentTarget.classList.remove('drag-over');
    
    console.log('Drop event triggered');
    console.log('draggedElement:', draggedElement);
    
    if (draggedElement) {
        const taskId = draggedElement.getAttribute('data-task-id');
        const targetColumn = ev.currentTarget;
        const draggedColumn = draggedElement.closest('.column-content');
        const targetColumnId = targetColumn.getAttribute('data-column-id');
        const targetStatus = targetColumn.getAttribute('data-status');
        
        console.log('Dropping task:', taskId, 'to column:', targetColumnId, 'status:', targetStatus);
        
        // Проверяем, что у нас есть все необходимые данные
        if (!taskId) {
            console.error('Task ID is missing!');
            alert('Ошибка: не удалось определить ID задачи');
            return;
        }
        
        // Нормализуем статус к допустимым значениям
        let normalizedStatus = 'todo'; // По умолчанию
        if (targetStatus) {
            const statusLower = targetStatus.toLowerCase();
            if (statusLower.includes('progress') || statusLower.includes('process') || 
                statusLower.includes('doing') || statusLower.includes('work') ||
                statusLower === 'progress') {
                normalizedStatus = 'progress';
            } else if (statusLower.includes('done') || statusLower.includes('completed') || 
                       statusLower.includes('finish') || statusLower === 'done') {
                normalizedStatus = 'done';
            } else {
                normalizedStatus = 'todo';
            }
        }
        
        console.log('Normalized status:', normalizedStatus);
        
        // Проверяем, перемещаем ли в ту же колонку (изменение порядка) или в другую
        const isSameColumn = targetColumn === draggedColumn;
        
        if (!isSameColumn) {
            // Перемещение в другую колонку
            const addButton = targetColumn.querySelector('.add-task-placeholder');
            
            // Добавляем задачу после кнопки добавления
            if (addButton && addButton.nextSibling) {
                targetColumn.insertBefore(draggedElement, addButton.nextSibling);
            } else if (addButton) {
                addButton.insertAdjacentElement('afterend', draggedElement);
            } else {
                targetColumn.appendChild(draggedElement);
            }
            
            // Обновляем статус задачи через AJAX
            if (targetColumnId) {
                updateTaskColumnAjax(taskId, targetColumnId, normalizedStatus);
            } else {
                updateTaskStatusAjax(taskId, normalizedStatus);
            }
        } else {
            // Изменение порядка в той же колонке - не нужно обновлять статус
            console.log('Task reordered within same column');
            // Позиция уже установлена в allowDrop, но можно добавить логику сохранения порядка
        }
        
        // Обновляем счетчики
        updateTaskCounts();
        
        // Убираем визуальные эффекты
        draggedElement.classList.remove('dragging');
        draggedElement.style.pointerEvents = ''; // Восстанавливаем события
        draggedElement = null; // Очищаем переменную
        isDragging = false; // Сбрасываем флаг перетаскивания
        
        console.log('Drop completed successfully');
    }
}

// Переменные для перетаскивания колонок
let draggedColumn = null;
let columnDragStarted = false;

// Обработчики для перетаскивания колонок
document.addEventListener('DOMContentLoaded', function() {
    // Находим все заголовки колонок и добавляем им обработчики
    const columnHeaders = document.querySelectorAll('.column-header');
    
    columnHeaders.forEach(header => {
        header.addEventListener('dragstart', handleColumnDragStart);
        header.addEventListener('dragend', handleColumnDragEnd);
    });
    
    // Находим доску (родительский элемент для колонок)
    const board = document.getElementById('kanban-board');
    if (board) {
        board.addEventListener('dragover', handleColumnDragOver);
        board.addEventListener('drop', handleColumnDrop);
    }
});

// Обработчик начала перетаскивания колонки
function handleColumnDragStart(e) {
    // Проверяем, что перетаскивание начали именно с заголовка колонки
    if (!e.currentTarget.classList.contains('column-header')) {
        return;
    }
    
    const columnId = e.currentTarget.getAttribute('data-column-id');
    if (!columnId) {
        return;
    }
    
    console.log('Column drag started:', columnId);
    columnDragStarted = true;
    
    // Сохраняем ссылку на всю колонку, а не только на заголовок
    draggedColumn = e.currentTarget.closest('.kanban-column');
    if (!draggedColumn) {
        console.error('Error: could not find kanban-column element');
        return;
    }
    
    draggedColumn.classList.add('column-dragging');
    
    e.dataTransfer.setData('text/plain', columnId);
    e.dataTransfer.effectAllowed = 'move';
    
    // Добавляем небольшую задержку для визуального эффекта
    setTimeout(() => {
        if (draggedColumn) {
            draggedColumn.style.opacity = '0.6';
        }
    }, 0);
}

// Обработчик перемещения колонки над целевой областью
function handleColumnDragOver(e) {
    if (!columnDragStarted || !draggedColumn) {
        return;
    }
    
    e.preventDefault();
    
    // Находим ближайшую колонку для вставки
    const closestColumn = getDropTargetColumn(e.clientX);
    if (closestColumn && closestColumn !== draggedColumn) {
        // Удаляем класс у всех колонок
        document.querySelectorAll('.kanban-column').forEach(column => {
            column.classList.remove('column-drop-target-before', 'column-drop-target-after');
        });
        
        // Определяем, куда вставлять: до или после ближайшей колонки
        const rect = closestColumn.getBoundingClientRect();
        const middleX = rect.left + rect.width / 2;
        
        if (e.clientX < middleX) {
            closestColumn.classList.add('column-drop-target-before');
        } else {
            closestColumn.classList.add('column-drop-target-after');
        }
    }
}

// Функция для определения ближайшей колонки для вставки
function getDropTargetColumn(clientX) {
    const columns = [...document.querySelectorAll('.kanban-column:not(.column-dragging):not(.add-column-placeholder)')];
    
    // Находим ближайшую колонку
    return columns.reduce((closest, column) => {
        const box = column.getBoundingClientRect();
        const offset = Math.abs(clientX - box.left - box.width / 2);
        
        if (offset < closest.offset) {
            return { offset: offset, element: column };
        }
        return closest;
    }, { offset: Number.POSITIVE_INFINITY, element: null }).element;
}

// Обработчик завершения перетаскивания колонки
function handleColumnDragEnd(e) {
    if (!columnDragStarted) {
        return;
    }
    
    console.log('Column drag ended');
    columnDragStarted = false;
    
    if (draggedColumn) {
        draggedColumn.style.opacity = '';
        draggedColumn.classList.remove('column-dragging');
        draggedColumn = null;
    }
    
    // Удаляем классы-индикаторы у всех колонок
    document.querySelectorAll('.kanban-column').forEach(column => {
        column.classList.remove('column-drop-target-before', 'column-drop-target-after');
    });
}

// Обработчик сброса перетаскиваемой колонки
function handleColumnDrop(e) {
    if (!columnDragStarted || !draggedColumn) {
        return;
    }
    
    e.preventDefault();
    
    const columnId = e.dataTransfer.getData('text/plain');
    const targetColumn = getDropTargetColumn(e.clientX);
    
    if (targetColumn && draggedColumn && targetColumn !== draggedColumn) {
        console.log('Dropping column', columnId, 'near', targetColumn.getAttribute('data-column-id'));
        
        // Определяем, вставлять до или после
        const rect = targetColumn.getBoundingClientRect();
        const middleX = rect.left + rect.width / 2;
        const insertBefore = e.clientX < middleX;
        
        // Визуально перемещаем колонку
        const board = document.getElementById('kanban-board');
        
        if (insertBefore) {
            board.insertBefore(draggedColumn, targetColumn);
        } else {
            // Вставляем после, учитывая следующий элемент
            const nextElement = targetColumn.nextElementSibling;
            if (nextElement) {
                board.insertBefore(draggedColumn, nextElement);
            } else {
                board.appendChild(draggedColumn);
            }
        }
        
        // Сохраняем изменения через AJAX
        saveColumnOrder();
    }
    
    // Сбрасываем состояние
    handleColumnDragEnd(e);
}

// Функция для сохранения нового порядка колонок
function saveColumnOrder() {
    // Собираем все ID колонок в новом порядке
    const columnOrder = [];
    document.querySelectorAll('.kanban-column:not(.add-column-placeholder)').forEach(column => {
        const columnId = column.getAttribute('data-column-id');
        if (columnId) {
            columnOrder.push(columnId);
        }
    });
    
    console.log('Saving new column order:', columnOrder);
    
    // Отправляем на сервер
    fetch(`/api/spaces/${spaceId}/columns/reorder`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken(),
            'Accept': 'application/json',
        },
        credentials: 'same-origin',
        body: JSON.stringify({
            columns: columnOrder
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            console.log('Column order updated successfully');
        } else {
            console.error('Failed to update column order:', data.message);
            alert('Ошибка при обновлении порядка колонок: ' + (data.message || 'Неизвестная ошибка'));
        }
    })
    .catch(error => {
        console.error('Error saving column order:', error);
        alert('Ошибка при сохранении порядка колонок: ' + error.message);
    });
}

// AJAX функция для обновления колонки задачи
function updateTaskColumnAjax(taskId, columnId, newStatus) {
    console.log('Updating task:', { taskId, columnId, newStatus, spaceId });
    
    // Проверяем, что все параметры переданы
    if (!taskId || taskId === 'null' || taskId === 'undefined') {
        console.error('Invalid taskId:', taskId);
        alert('Ошибка: некорректный ID задачи');
        return;
    }
    
    if (!spaceId) {
        console.error('Invalid spaceId:', spaceId);
        alert('Ошибка: некорректный ID пространства');
        return;
    }
    
    fetch(`/api/spaces/${spaceId}/tasks/${taskId}/status`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken(),
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin',
        body: JSON.stringify({
            column_id: columnId,
            status: newStatus
        })
    })
    .then(response => {
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);
        
        if (!response.ok) {
            return response.text().then(text => {
                console.error('Response text:', text);
                let errorMessage = `HTTP error! status: ${response.status}`;
                try {
                    const errorData = JSON.parse(text);
                    if (errorData.message) {
                        errorMessage = errorData.message;
                    }
                    if (errorData.errors) {
                        errorMessage += '. Ошибки: ' + Object.values(errorData.errors).flat().join(', ');
                    }
                } catch (e) {
                    errorMessage += '. Response: ' + text;
                }
                throw new Error(errorMessage);
            });
        }
        return response.json();
    })
    .then(data => {
        console.log('Success response:', data);
        if (data.success) {
            updateTaskStatus(taskId, newStatus);
            console.log('Задача перемещена в колонку:', data.message);
        } else {
            console.error('Ошибка обновления колонки задачи:', data.message);
            alert('Ошибка обновления задачи: ' + (data.message || 'Неизвестная ошибка'));
            // Возвращаем задачу обратно в случае ошибки
            location.reload();
        }
    })
    .catch(error => {
        console.error('Ошибка запроса:', error);
        alert('Ошибка обновления задачи: ' + error.message);
        location.reload();
    });
}

// AJAX функция для обновления статуса задачи
function updateTaskStatusAjax(taskId, newStatus) {
    console.log('Updating task status:', { taskId, newStatus, spaceId });
    
    // Проверяем, что все параметры переданы
    if (!taskId || taskId === 'null' || taskId === 'undefined') {
        console.error('Invalid taskId:', taskId);
        alert('Ошибка: некорректный ID задачи');
        return;
    }
    
    if (!spaceId) {
        console.error('Invalid spaceId:', spaceId);
        alert('Ошибка: некорректный ID пространства');
        return;
    }
    
    fetch(`/api/spaces/${spaceId}/tasks/${taskId}/status`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken(),
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin',
        body: JSON.stringify({
            status: newStatus
        })
    })
    .then(response => {
        console.log('Response status:', response.status);
        
        if (!response.ok) {
            return response.text().then(text => {
                console.error('Response text:', text);
                let errorMessage = `HTTP error! status: ${response.status}`;
                try {
                    const errorData = JSON.parse(text);
                    if (errorData.message) {
                        errorMessage = errorData.message;
                    }
                    if (errorData.errors) {
                        errorMessage += '. Ошибки: ' + Object.values(errorData.errors).flat().join(', ');
                    }
                } catch (e) {
                    errorMessage += '. Response: ' + text;
                }
                throw new Error(errorMessage);
            });
        }
        return response.json();
    })
    .then(data => {
        console.log('Success response:', data);
        if (data.success) {
            updateTaskStatus(taskId, newStatus);
            console.log('Статус задачи обновлен:', data.message);
        } else {
            console.error('Ошибка обновления статуса:', data.message);
            alert('Ошибка обновления статуса: ' + (data.message || 'Неизвестная ошибка'));
            // Возвращаем задачу обратно в случае ошибки
            location.reload();
        }
    })
    .catch(error => {
        console.error('Ошибка запроса:', error);
        alert('Ошибка обновления статуса: ' + error.message);
        location.reload();
    });
}

// Обработчики событий для улучшения UX
document.addEventListener('DOMContentLoaded', function() {
    // Инициализируем перетаскивание задач
    initTaskDragAndDrop();
    
    // Инициализируем перетаскивание колонок
    initColumnDragAndDrop();
    
    // Обновляем счетчики при загрузке
    updateTaskCounts();
    
    // Инициализируем мобильные функции канбан-доски (подключаемые через kanban-mobile.js)
    if (typeof initMobileKanban === 'function') {
        initMobileKanban();
    }
});

// Функция для инициализации перетаскивания задач
function initTaskDragAndDrop() {
    // Убираем drag-over эффект при покидании области
    const columns = document.querySelectorAll('.column-content');
    columns.forEach(column => {
        column.addEventListener('dragleave', function(e) {
            if (!this.contains(e.relatedTarget)) {
                this.classList.remove('drag-over');
            }
        });
        
        column.addEventListener('dragend', function(e) {
            this.classList.remove('drag-over');
            if (draggedElement) {
                draggedElement.classList.remove('dragging');
                draggedElement.style.pointerEvents = ''; // Восстанавливаем события
                draggedElement = null; // Очищаем переменную
            }
            isDragging = false; // Сбрасываем флаг перетаскивания
        });
    });
    
    // Добавляем обработчики событий для всех задач
    const taskCards = document.querySelectorAll('.task-card');
    taskCards.forEach(card => {
        // Обработчик клика по задаче
        card.addEventListener('click', function(e) {
            // Если не было перетаскивания, открываем задачу
            if (!isDragging) {
                const taskId = this.getAttribute('data-task-id');
                if (taskId) {
                    openTaskPage(taskId);
                }
            }
        });
        
        // Обработчик начала перетаскивания
        card.addEventListener('dragstart', function(e) {
            console.log('Dragstart event triggered for task:', e.target.getAttribute('data-task-id'));
            isDragging = true;
            draggedElement = e.target;
            this.classList.add('dragging');
        });
        
        // Обработчик окончания перетаскивания
        card.addEventListener('dragend', function(e) {
            console.log('Dragend event triggered');
            this.classList.remove('dragging');
            this.style.pointerEvents = '';
            if (draggedElement === this) {
                draggedElement = null;
            }
            // Небольшая задержка перед сбросом флага, чтобы избежать случайного клика
            setTimeout(() => {
                isDragging = false;
            }, 50);
        });
    });
}

// Функция для инициализации перетаскивания колонок
function initColumnDragAndDrop() {
    // Находим все заголовки колонок и добавляем им обработчики
    const columnHeaders = document.querySelectorAll('.column-header');
    
    columnHeaders.forEach(header => {
        header.addEventListener('dragstart', handleColumnDragStart);
        header.addEventListener('dragend', handleColumnDragEnd);
    });
    
    // Находим доску (родительский элемент для колонок)
    const board = document.getElementById('kanban-board');
    if (board) {
        board.addEventListener('dragover', handleColumnDragOver);
        board.addEventListener('drop', handleColumnDrop);
    }
}

function updateTaskStatus(taskId, newStatus) {
    const taskCard = document.querySelector(`[data-task-id="${taskId}"]`);
    if (taskCard) {
        // Обновляем визуальное состояние
        if (newStatus === 'done') {
            taskCard.classList.add('completed');
        } else {
            taskCard.classList.remove('completed');
        }
        
        console.log(`Задача ${taskId} перемещена в статус: ${newStatus}`);
    }
}

function updateTaskCounts() {
    // Обновляем отображение placeholder для всех колонок
    const columns = document.querySelectorAll('.kanban-column');
    columns.forEach(column => {
        const taskCards = column.querySelectorAll('.task-card');
        const taskCount = taskCards.length;
        const placeholder = column.querySelector('.add-task-placeholder');
        
        // Управляем отображением placeholder'а
        if (placeholder) {
            if (taskCount === 0) {
                placeholder.style.display = ''; // Показываем для пустых колонок
            } else {
                placeholder.style.display = 'none'; // Скрываем для колонок с задачами
            }
        }
    });
}

// Функции для управления колонками
function createNewColumn() {
    // Создаем колонку с базовым именем
    const defaultName = 'Новая колонка';
    const defaultColor = '#6c757d';
    
    fetch(`/api/spaces/${spaceId}/columns`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
        },
        credentials: 'same-origin',
        body: JSON.stringify({
            name: defaultName,
            color: defaultColor
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Создаем элемент новой колонки
            const newColumn = createColumnElement(data.column);
            
            // Вставляем колонку перед блоком добавления
            const addColumnBlock = document.querySelector('.add-column-placeholder');
            addColumnBlock.parentNode.insertBefore(newColumn, addColumnBlock);
            
            // Сразу переводим название в режим редактирования
            setTimeout(() => {
                editColumnNameInline(data.column.id);
            }, 100);
            
            console.log('Создана новая колонка:', data.column);
        } else {
            alert('Ошибка создания колонки: ' + (data.message || 'Неизвестная ошибка'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Ошибка соединения с сервером: ' + error.message);
    });
}

function createColumnElement(column) {
    const columnDiv = document.createElement('div');
    columnDiv.className = 'kanban-column';
    columnDiv.setAttribute('data-column-id', column.id);
    columnDiv.setAttribute('data-status', column.slug);
    
    // Получаем текущее количество колонок для определения позиции
    const existingColumns = document.querySelectorAll('.kanban-column:not(.add-column-placeholder)');
    const isFirst = existingColumns.length === 0;
    const isLast = true; // Новая колонка всегда последняя
    
    columnDiv.innerHTML = `
        <div class="column-header" style="border-left: 4px solid ${column.color}" draggable="true" data-column-id="${column.id}">
            <div class="column-info">
                <h3 class="column-title" ondblclick="editColumnNameInline(${column.id})" title="Двойной клик для редактирования">${column.name}</h3>
            </div>
            <div class="column-actions">
                <button class="column-action-btn add-task" onclick="createQuickTask(${column.id}, '${column.slug}')" title="Добавить задачу">
                    <i class="fas fa-plus"></i>
                </button>
                <button class="column-action-btn" onclick="editColumn(${column.id})" title="Редактировать колонку">
                    <i class="fas fa-edit"></i>
                </button>
                ${!column.is_default ? `<button class="column-action-btn delete" onclick="deleteColumn(${column.id})" title="Удалить колонку">
                    <i class="fas fa-trash"></i>
                </button>` : ''}
            </div>
        </div>
        <div class="column-content" 
             ondrop="drop(event)" 
             ondragover="allowDrop(event)"
             data-column-id="${column.id}"
             data-status="${column.slug}">
            
            <!-- Кнопка создания новой задачи в пустой колонке -->
            <div class="add-task-placeholder" onclick="createQuickTask(${column.id}, '${column.slug}')" style="margin-bottom: 15px;">
                <i class="fas fa-plus"></i>
                <span>Добавить задачу</span>
            </div>
        </div>
    `;
    
    return columnDiv;
}

function editColumn(columnId) {
    // Получаем данные колонки
    const columnElement = document.querySelector(`[data-column-id="${columnId}"]`);
    const columnTitle = columnElement.querySelector('.column-title').textContent;
    const columnColor = columnElement.querySelector('.column-header').style.borderLeftColor;
    
    // Заполняем форму
    document.getElementById('editColumnId').value = columnId;
    document.getElementById('editColumnName').value = columnTitle;
    // Преобразуем RGB в HEX если нужно
    document.getElementById('editColumnColor').value = rgbToHex(columnColor) || '#6c757d';
    
    const modal = new bootstrap.Modal(document.getElementById('editColumnModal'));
    modal.show();
}

function updateColumn() {
    const columnId = document.getElementById('editColumnId').value;
    const name = document.getElementById('editColumnName').value;
    const color = document.getElementById('editColumnColor').value;
    
    if (!name.trim()) {
        alert('Пожалуйста, введите название колонки');
        return;
    }
    
    fetch('/sanctum/csrf-cookie', {
        method: 'GET',
        credentials: 'same-origin',
    })
    .then(() => {
        return fetch(`/api/spaces/${spaceId}/columns/${columnId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                name: name,
                color: color
            })
        });
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Обновляем колонку в DOM
            const columnElement = document.querySelector(`[data-column-id="${columnId}"]`);
            const titleElement = columnElement.querySelector('.column-title');
            titleElement.textContent = data.column.name;
            
            // Отложенная проверка, чтобы стили успели обновиться
            setTimeout(() => {
                // Проверяем, нужен ли эффект прокрутки для обновленного заголовка
                const containerWidth = titleElement.clientWidth;
                const textWidth = titleElement.scrollWidth;
                
                console.log(`Updated column title: ${titleElement.textContent}, Container width: ${containerWidth}, Text width: ${textWidth}`);
                
                if (textWidth > containerWidth + 5) {
                    titleElement.dataset.needsMarquee = 'true';
                    titleElement.setAttribute('title', titleElement.textContent);
                    
                    // Добавляем обработчики, если их еще нет
                    titleElement.removeEventListener('mouseenter', handleTitleMouseEnter);
                    titleElement.removeEventListener('mouseleave', handleTitleMouseLeave);
                    titleElement.addEventListener('mouseenter', handleTitleMouseEnter);
                    titleElement.addEventListener('mouseleave', handleTitleMouseLeave);
                } else {
                    titleElement.dataset.needsMarquee = 'false';
                }
            }, 100);
            columnElement.querySelector('.column-header').style.borderLeftColor = data.column.color;
            
            // Закрываем модальное окно
            const modal = bootstrap.Modal.getInstance(document.getElementById('editColumnModal'));
            modal.hide();
            
            console.log('Колонка обновлена:', data.column);
        } else {
            alert('Ошибка обновления колонки: ' + (data.message || 'Неизвестная ошибка'));
        }
    })
    .catch(error => {
        console.error('Ошибка запроса:', error);
        alert('Ошибка соединения с сервером: ' + error.message);
    });
}

function deleteColumn(columnId) {
    // Находим колонку чтобы проверить количество задач в ней
    const columnElement = document.querySelector(`[data-column-id="${columnId}"]`);
    const tasksInColumn = columnElement ? columnElement.querySelectorAll('.task-card').length : 0;
    
    let confirmMessage;
    if (tasksInColumn > 0) {
        confirmMessage = `Вы уверены, что хотите удалить эту колонку? В ней находится ${tasksInColumn} задач(и), которые будут автоматически архивированы.`;
    } else {
        confirmMessage = 'Вы уверены, что хотите удалить эту колонку?';
    }
    
    if (confirm(confirmMessage)) {
        console.log('Удаление колонки:', columnId, 'в пространстве:', spaceId);
        
        fetch(`/api/spaces/${spaceId}/columns/${columnId}`, {
            method: 'DELETE', // Используем тот же метод, но на сервере просто скрываем колонку
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        })
        .then(response => {
            console.log('Ответ сервера:', response.status, response.statusText);
            return response.json().then(data => {
                console.log('Данные ответа:', data);
                if (response.ok) {
                    if (data.success) {
                        const columnElement = document.querySelector(`[data-column-id="${columnId}"]`);
                        if (columnElement) {
                            columnElement.remove();
                            console.log('Колонка удалена:', columnId);
                        }
                        
                        // Создаем улучшенное сообщение с ссылкой на архив
                        let message = data.message || 'Колонка успешно удалена';
                        if (data.archived_tasks_count && data.archived_tasks_count > 0) {
                            const archiveUrl = `{{ route('spaces.archive', [$organization, $space]) }}`;
                            message += ` <a href="${archiveUrl}" style="color: #007bff; text-decoration: underline;">Перейти в архив</a>`;
                        }
                        // Добавляем информацию о возможности восстановления, но в более общем контексте
                        message += ` <button onclick="showHiddenColumns()" class="btn-link text-info" style="background:none;border:none;padding:0;cursor:pointer;text-decoration:underline;">Управление удаленными колонками</button>`;
                        showSuccessMessage(message);
                        
                        // Если были архивированы задачи, обновляем счетчик архива
                        if (data.archived_tasks_count && data.archived_tasks_count > 0) {
                            // Обновляем страницу через небольшую задержку, чтобы пользователь увидел сообщение
                            setTimeout(() => {
                                window.location.reload();
                            }, 3000);
                        }
                    } else {
                        showErrorMessage('Ошибка удаления колонки: ' + (data.message || 'Неизвестная ошибка'));
                    }
                } else {
                    // Обрабатываем ошибки сервера (400, 403, 404 и т.д.)
                    console.error('Ошибка сервера:', response.status, data);
                    showErrorMessage(data.message || `Ошибка сервера: ${response.status}`);
                }
            });
        })
        .catch(error => {
            console.error('Ошибка запроса:', error);
            showErrorMessage('Ошибка соединения с сервером');
        });
    }
}


// Функция moveColumn больше не используется, так как колонки перемещаются через drag and drop

// Функция для отображения модального окна со скрытыми колонками
function showHiddenColumns() {
    // Создаем модальное окно, если его еще нет
    let hiddenColumnsModal = document.getElementById('hiddenColumnsModal');
    if (!hiddenColumnsModal) {
        const modalHtml = `
        <div class="modal fade" id="hiddenColumnsModal" tabindex="-1" aria-labelledby="hiddenColumnsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="hiddenColumnsModalLabel">Восстановление удаленных колонок</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i> Здесь вы можете восстановить ранее удаленные колонки.
                        </div>
                        <div id="hiddenColumnsContainer" class="row g-3">
                            <div class="text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Загрузка...</span>
                                </div>
                                <p class="mt-2">Загрузка удаленных колонок...</p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                    </div>
                </div>
            </div>
        </div>`;
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        hiddenColumnsModal = document.getElementById('hiddenColumnsModal');
    }
    
    // Показываем модальное окно
    const modal = new bootstrap.Modal(hiddenColumnsModal);
    modal.show();
    
    // Загружаем скрытые колонки
    loadHiddenColumns();
}

// Функция для загрузки скрытых колонок
function loadHiddenColumns() {
    const hiddenColumnsContainer = document.getElementById('hiddenColumnsContainer');
    
    fetch(`/api/spaces/${spaceId}/columns/hidden`, {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.columns) {
            if (data.columns.length === 0) {
                hiddenColumnsContainer.innerHTML = `
                    <div class="col-12 text-center py-4">
                        <i class="fas fa-info-circle fa-3x text-muted mb-3"></i>
                        <p>Нет удаленных колонок</p>
                    </div>`;
                return;
            }
            
            let columnsHtml = '';
            data.columns.forEach(column => {
                columnsHtml += `
                <div class="col-md-6 col-lg-4">
                    <div class="card" style="border-left: 4px solid ${column.color || '#6c757d'};">
                        <div class="card-body">
                            <h5 class="card-title">${column.name}</h5>
                            <p class="card-text">
                                <small class="text-muted">Задач: ${column.tasks_count || 0}</small>
                            </p>
                            <button class="btn btn-sm btn-primary" onclick="restoreColumn(${column.id})">
                                <i class="fas fa-undo me-1"></i> Восстановить
                            </button>
                        </div>
                    </div>
                </div>`;
            });
            hiddenColumnsContainer.innerHTML = columnsHtml;
        } else {
            hiddenColumnsContainer.innerHTML = `
                <div class="col-12 text-center py-3">
                    <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                    <p>Ошибка при загрузке удаленных колонок</p>
                    <p class="text-muted small">${data.message || 'Неизвестная ошибка'}</p>
                </div>`;
        }
    })
    .catch(error => {
        console.error('Ошибка при загрузке удаленных колонок:', error);
        hiddenColumnsContainer.innerHTML = `
            <div class="col-12 text-center py-3">
                <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                <p>Ошибка при загрузке удаленных колонок</p>
                <p class="text-muted small">${error.message || 'Неизвестная ошибка'}</p>
            </div>`;
    });
}

// Функция для восстановления скрытой колонки
function restoreColumn(columnId) {
    fetch(`/api/spaces/${spaceId}/columns/${columnId}/restore`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccessMessage('Колонка успешно восстановлена и добавлена на доску');
            
            // Перезагружаем список скрытых колонок
            loadHiddenColumns();
            
            // Перезагружаем страницу после небольшой задержки
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showErrorMessage('Ошибка при восстановлении колонки: ' + (data.message || 'Неизвестная ошибка'));
        }
    })
    .catch(error => {
        console.error('Ошибка при восстановлении колонки:', error);
        showErrorMessage('Ошибка при восстановлении колонки: ' + error.message);
    });
}

function editColumnNameInline(columnId) {
    const columnElement = document.querySelector(`[data-column-id="${columnId}"]`);
    const titleElement = columnElement.querySelector('.column-title');
    const currentText = titleElement.textContent.trim();
    
    // Убираем эффект прокрутки, если он активен
    titleElement.classList.remove('marquee');
    
    // Проверяем, не находится ли уже в режиме редактирования
    if (columnElement.querySelector('.column-title-input')) {
        return;
    }
    
    // Создаем input для редактирования
    const input = document.createElement('input');
    input.type = 'text';
    input.value = currentText;
    input.className = 'column-title-input';
    input.style.cssText = `
        width: ${Math.max(120, currentText.length * 10 + 40)}px;
        max-width: 250px;
        padding: 4px 8px;
        border: 2px solid var(--sketch-green);
        border-radius: 4px;
        background-color: var(--sketch-white);
        font-size: 1.1rem;
        font-weight: bold;
        outline: none;
        margin: 0;
    `;
    
    // Заменяем заголовок на input
    titleElement.style.display = 'none';
    titleElement.parentNode.insertBefore(input, titleElement);
    
    // Фокусируемся и выделяем текст
    input.focus();
    input.select();
    
    // Функция для сохранения изменений
    function saveChanges() {
        const newName = input.value.trim();
        
        if (newName && newName !== currentText && newName.length > 0) {
            fetch(`/api/columns/${columnId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    name: newName
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    titleElement.textContent = newName;
                    titleElement.setAttribute('title', 'Двойной клик для редактирования');
                } else {
                    alert('Ошибка при обновлении названия колонки: ' + (data.message || ''));
                    titleElement.textContent = currentText;
                }
                // Возвращаем обычный заголовок
                input.remove();
                titleElement.style.display = '';
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Ошибка при обновлении названия колонки: ' + error.message);
                titleElement.textContent = currentText;
                // Возвращаем обычный заголовок
                input.remove();
                titleElement.style.display = '';
            });
        } else if (newName.length === 0) {
            alert('Название колонки не может быть пустым');
            input.focus();
            return;
        } else {
            // Отменяем изменения
            input.remove();
            titleElement.style.display = '';
        }
    }
    
    // Обработчики событий
    input.addEventListener('blur', saveChanges);
    input.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            saveChanges();
        } else if (e.key === 'Escape') {
            e.preventDefault();
            // Отменяем изменения
            input.remove();
            titleElement.style.display = '';
        }
    });
}

// Вспомогательная функция для преобразования RGB в HEX
function rgbToHex(rgb) {
    if (!rgb || rgb.indexOf('rgb') === -1) return rgb;
    
    const result = rgb.match(/\d+/g);
    if (!result || result.length < 3) return rgb;
    
    return "#" + ((1 << 24) + (parseInt(result[0]) << 16) + (parseInt(result[1]) << 8) + parseInt(result[2])).toString(16).slice(1);
}

// Функции для управления задачами
function createQuickTask(columnId, columnSlug) {
    const taskTitle = 'Новая задача';
    
    // Нормализуем статус к допустимым значениям
    let status = 'todo'; // По умолчанию
    
    if (columnSlug) {
        const statusLower = columnSlug.toLowerCase();
        if (statusLower.includes('progress') || statusLower.includes('process') || 
            statusLower.includes('doing') || statusLower.includes('work') ||
            statusLower === 'progress') {
            status = 'progress';
        } else if (statusLower.includes('done') || statusLower.includes('completed') || 
                   statusLower.includes('finish') || statusLower === 'done') {
            status = 'done';
        } else {
            status = 'todo';
        }
    }
    
    console.log('Creating task with:', {
        title: taskTitle,
        columnId,
        columnSlug,
        normalizedStatus: status,
        spaceId
    });
    
    // Отправляем AJAX запрос для создания задачи
    fetch(`/api/spaces/${spaceId}/tasks`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken(),
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin',
        body: JSON.stringify({
            title: taskTitle,
            description: '',
            assignee: '',
            estimated_time: '',
            priority: 'medium',
            status: status,
            column_id: columnId
        })
    })
    .then(response => {
        if (!response.ok) {
            // Для ошибок валидации (422) попытаемся получить детали
            if (response.status === 422) {
                return response.json().then(errorData => {
                    console.error('Validation errors:', errorData);
                    const errorMessages = errorData.errors ? 
                        Object.values(errorData.errors).flat().join(', ') : 
                        errorData.message || 'Ошибка валидации';
                    throw new Error(`Validation error: ${errorMessages}`);
                });
            }
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            const taskCard = createSimpleTaskCard(data.task);
            
            // Добавляем задачу в соответствующую колонку
            const targetColumn = document.querySelector(`[data-column-id="${columnId}"] .column-content`);
            const addButton = targetColumn.querySelector('.add-task-placeholder');
            
            if (addButton) {
                // Если есть placeholder для пустой колонки, заменяем его задачей
                addButton.insertAdjacentElement('afterend', taskCard);
                addButton.style.display = 'none'; // Скрываем placeholder когда появляется первая задача
            } else {
                // Добавляем в конец колонки
                targetColumn.appendChild(taskCard);
            }
            
            // Обновляем счетчики
            updateTaskCounts();
            
            // Сразу переводим в режим редактирования названия
            setTimeout(() => {
                const titleElement = taskCard.querySelector('.task-title');
                if (titleElement) {
                    // Имитируем двойной клик для редактирования
                    const event = { target: titleElement, stopPropagation: () => {} };
                    editTaskNameInline(data.task.id, event);
                }
            }, 100);
            
            console.log('Добавлена новая задача:', data.task);
        } else {
            alert('Ошибка создания задачи: ' + (data.message || 'Неизвестная ошибка'));
        }
    })
    .catch(error => {
        console.error('Ошибка запроса:', error);
        alert('Ошибка соединения с сервером: ' + error.message);
    });
}

function createSimpleTaskCard(task) {
    const taskCard = document.createElement('div');
    taskCard.className = 'task-card';
    taskCard.draggable = true;
    taskCard.setAttribute('data-task-id', task.id);
    
    // Обработчик клика по задаче
    taskCard.addEventListener('click', function(e) {
        if (!isDragging) {
            const taskId = this.getAttribute('data-task-id');
            if (taskId) {
                openTaskPage(taskId);
            }
        }
    });
    
    // Обработчик начала перетаскивания
    taskCard.addEventListener('dragstart', function(e) {
        console.log('Dragstart event triggered for new task:', e.target.getAttribute('data-task-id'));
        isDragging = true;
        draggedElement = e.target;
        this.classList.add('dragging');
        e.dataTransfer.setData("text", e.target.getAttribute('data-task-id'));
        e.dataTransfer.effectAllowed = 'move';
    });
    
    // Обработчик окончания перетаскивания
    taskCard.addEventListener('dragend', function(e) {
        console.log('Dragend event triggered for new task');
        this.classList.remove('dragging');
        this.style.pointerEvents = '';
        if (draggedElement === this) {
            draggedElement = null;
        }
        setTimeout(() => {
            isDragging = false;
        }, 50);
    });
    
    // Проверяем права доступа для отображения кнопок действий
    const canManageTask = (spaceCreatedBy === currentUserId) || (task.created_by === currentUserId);
    
    let taskActionsHtml = '';
    if (canManageTask) {
        taskActionsHtml = `
            <div class="task-actions" onclick="event.stopPropagation();">
                <button class="task-action-btn archive-btn" onclick="archiveTaskFromBoard(${task.id})" title="Архивировать задачу">
                    <i class="fas fa-archive"></i>
                </button>
            </div>
        `;
    }
    
    taskCard.innerHTML = `
        <div class="task-header">
            <div class="task-title" ondblclick="editTaskNameInline(${task.id}, event)" title="Двойной клик для редактирования">${task.title}</div>
            <div class="status-square medium" data-priority="medium" 
                 title="Средний приоритет"
                 onclick="toggleTaskStatusMenu(${task.id})"></div>
        </div>
        ${taskActionsHtml}
    `;
    
    return taskCard;
}

function openTaskPage(taskId) {
    // Переходим на страницу просмотра/редактирования задачи
    window.location.href = `/tasks/${taskId}`;
}

// Безопасная функция открытия задачи (оставлена для совместимости)
function openTaskPageSafe(taskId, event) {
    if (!isDragging) {
        openTaskPage(taskId);
    }
}

function editTaskNameInline(taskId, event) {
    event.stopPropagation(); // Предотвращаем открытие страницы задачи
    
    const titleElement = event.target;
    const currentText = titleElement.textContent;
    
    // Создаем input для редактирования
    const input = document.createElement('input');
    input.type = 'text';
    input.value = currentText;
    input.className = 'task-title-edit';
    input.style.cssText = `
        background: none;
        border: 1px solid var(--sketch-blue);
        border-radius: 3px;
        padding: 2px 4px;
        font-size: inherit;
        font-weight: inherit;
        width: 100%;
        color: inherit;
    `;
    
    // Заменяем заголовок на input
    titleElement.style.display = 'none';
    titleElement.parentNode.insertBefore(input, titleElement);
    input.focus();
    input.select();
    
    function saveChanges() {
        const newName = input.value.trim();
        
        if (newName && newName !== currentText) {
            // Отправляем запрос на сервер
            fetch(`/api/spaces/${spaceId}/tasks/${taskId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    title: newName
                })
            })
            .then(response => {
                if (!response.ok) {
                    // Для ошибок валидации (422) попытаемся получить детали
                    if (response.status === 422) {
                        return response.json().then(errorData => {
                            console.error('Validation errors:', errorData);
                            const errorMessages = errorData.errors ? 
                                Object.values(errorData.errors).flat().join(', ') : 
                                errorData.message || 'Ошибка валидации';
                            throw new Error(`Validation error: ${errorMessages}`);
                        });
                    }
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    titleElement.textContent = newName;
                    titleElement.setAttribute('title', 'Двойной клик для редактирования');
                } else {
                    alert('Ошибка при обновлении названия задачи: ' + (data.message || ''));
                    titleElement.textContent = currentText;
                }
                // Возвращаем обычный заголовок
                input.remove();
                titleElement.style.display = '';
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Ошибка при обновлении названия задачи: ' + error.message);
                titleElement.textContent = currentText;
                // Возвращаем обычный заголовок
                input.remove();
                titleElement.style.display = '';
            });
        } else if (newName.length === 0) {
            alert('Название задачи не может быть пустым');
            input.focus();
            return;
        } else {
            // Отменяем изменения
            input.remove();
            titleElement.style.display = '';
        }
    }
    
    // Обработчики событий
    input.addEventListener('blur', saveChanges);
    input.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            saveChanges();
        } else if (e.key === 'Escape') {
            e.preventDefault();
            // Отменяем изменения
            input.remove();
            titleElement.style.display = '';
        }
    });
}

function deleteTask(taskId) {
    if (confirm('Вы уверены, что хотите удалить эту задачу?')) {
        fetch(`/api/spaces/${spaceId}/tasks/${taskId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                const taskCard = document.querySelector(`[data-task-id="${taskId}"]`);
                if (taskCard) {
                    taskCard.remove();
                    updateTaskCounts(); // Это автоматически покажет placeholder если колонка стала пустой
                    console.log('Удалена задача:', taskId);
                }
            } else {
                alert('Ошибка удаления задачи: ' + (data.message || 'Неизвестная ошибка'));
            }
        })
        .catch(error => {
            console.error('Ошибка запроса:', error);
            alert('Ошибка соединения с сервером: ' + error.message);
        });
    }
}

// Дополнительные функции для совместимости
function openTask(taskId) {
    console.log('Открытие задачи:', taskId);
    alert(`Просмотр задачи #${taskId} - функция будет реализована позже`);
}

function changePriority(taskId) {
    console.log('Изменение приоритета задачи:', taskId);
    alert(`Изменение приоритета задачи #${taskId} - функция будет реализована позже`);
}

function toggleTaskStatusMenu(taskId) {
    console.log('Переключение меню статуса задачи:', taskId);
    // Временно открываем страницу задачи для изменения приоритета
    openTaskPage(taskId);
}

function changeTime(taskId) {
    console.log('Изменение времени задачи:', taskId);
    alert(`Изменение времени задачи #${taskId} - функция будет реализована позже`);
}

// Функции для отображения уведомлений
function showSuccessMessage(message) {
    // Создаем уведомление об успехе
    const notification = document.createElement('div');
    notification.className = 'notification success';
    notification.innerHTML = `
        <i class="fas fa-check-circle"></i>
        <span>${message}</span>
        <button onclick="this.parentElement.remove()" class="notification-close">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    // Добавляем стили, если их еще нет
    if (!document.getElementById('notification-styles')) {
        const styles = document.createElement('style');
        styles.id = 'notification-styles';
        styles.textContent = `
            .notification {
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 15px 20px;
                border-radius: 8px;
                color: white;
                font-weight: 500;
                z-index: 1000;
                display: flex;
                align-items: center;
                gap: 10px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                animation: slideIn 0.3s ease-out;
                max-width: 400px;
            }
            .notification.success {
                background-color: #28a745;
            }
            .notification.error {
                background-color: #dc3545;
            }
            .notification-close {
                background: none;
                border: none;
                color: white;
                cursor: pointer;
                padding: 0;
                margin-left: auto;
            }
            @keyframes slideIn {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
        `;
        document.head.appendChild(styles);
    }
    
    document.body.appendChild(notification);
    
    // Автоматически убираем уведомление через 7 секунд для важных сообщений
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 7000);
}

function showErrorMessage(message) {
    // Создаем уведомление об ошибке
    const notification = document.createElement('div');
    notification.className = 'notification error';
    notification.innerHTML = `
        <i class="fas fa-exclamation-circle"></i>
        <span>${message}</span>
        <button onclick="this.parentElement.remove()" class="notification-close">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    document.body.appendChild(notification);
    
    // Автоматически убираем уведомление через 7 секунд (дольше для ошибок)
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 7000);
}

// Обработка эффекта прокрутки заголовков колонок
document.addEventListener('DOMContentLoaded', () => {
    // Небольшая задержка, чтобы страница полностью загрузилась и стили применились
    setTimeout(() => {
        console.log('Setting up column titles hover effects');
        setupColumnTitlesHover();
    }, 500);
    
    // Добавляем наблюдателя за изменениями в DOM для обработки новых колонок
    const observer = new MutationObserver((mutations) => {
        console.log('DOM mutation detected');
        mutations.forEach((mutation) => {
            if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                setTimeout(setupColumnTitlesHover, 100);
            }
        });
    });
    
    const kanbanBoard = document.querySelector('#kanban-board');
    if (kanbanBoard) {
        observer.observe(kanbanBoard, {
            childList: true,
            subtree: true
        });
        console.log('Mutation observer set up for kanban board');
    } else {
        console.error('Kanban board element not found');
    }
});

// Функция для проверки нужна ли бегущая строка
function checkIfNeedsMarquee(titleElement) {
    // Небольшая хитрость для принудительного перерасчета размеров
    titleElement.style.display = 'inline-block';
    void titleElement.offsetWidth; // Вызывает reflow
    
    const containerWidth = titleElement.parentNode.clientWidth;
    const textWidth = titleElement.scrollWidth;
    
    titleElement.style.display = '';
    
    console.log(`Checking marquee need for: "${titleElement.textContent.trim()}", Container: ${containerWidth}px, Text: ${textWidth}px`);
    
    return textWidth > containerWidth + 2;
}

// Ручной запуск эффекта бегущей строки (для отладки)
function startMarquee(titleElement) {
    if (titleElement && titleElement.classList) {
        console.log(`Manually starting marquee for "${titleElement.textContent.trim()}"`);
        titleElement.classList.add('marquee');
        return true;
    }
    return false;
}

// Настройка обработчиков событий для заголовков колонок
function setupColumnTitlesHover() {
    const columnTitles = document.querySelectorAll('.column-title');
    
    columnTitles.forEach(titleElement => {
        // Удаляем существующие обработчики, если они есть
        titleElement.removeEventListener('mouseenter', handleTitleMouseEnter);
        titleElement.removeEventListener('mouseleave', handleTitleMouseLeave);
        
        // Проверяем нужна ли бегущая строка
        const needsMarquee = checkIfNeedsMarquee(titleElement);
        
        if (needsMarquee) {
            titleElement.setAttribute('title', titleElement.textContent.trim()); // Добавляем всплывающую подсказку
            titleElement.dataset.needsMarquee = 'true';
            titleElement.addEventListener('mouseenter', handleTitleMouseEnter);
            titleElement.addEventListener('mouseleave', handleTitleMouseLeave);
            console.log(`Set up marquee for: "${titleElement.textContent.trim()}"`);
        } else {
            titleElement.dataset.needsMarquee = 'false';
        }
    });
}

// Таймеры для каждого заголовка колонки
const titleHoverTimers = new Map();

// Обработчик наведения мыши на заголовок колонки
function handleTitleMouseEnter(e) {
    const titleElement = e.target;
    
    // Если текст не обрезается, не добавляем эффект
    if (titleElement.dataset.needsMarquee !== 'true') {
        return;
    }
    
    console.log(`Mouse entered title: ${titleElement.textContent}, starting hover timer`);
    
    // Устанавливаем таймер на 1.5 секунды
    const timerId = setTimeout(() => {
        console.log(`Timer triggered for: ${titleElement.textContent}, adding marquee class`);
        titleElement.classList.add('marquee');
        // Визуальный индикатор активной анимации для отладки
        titleElement.style.outline = '1px dashed #00ff00';
    }, 1500);
    
    titleHoverTimers.set(titleElement, timerId);
}

// Обработчик ухода мыши с заголовка колонки
function handleTitleMouseLeave(e) {
    const titleElement = e.target;
    
    console.log(`Mouse left title: ${titleElement.textContent}`);
    
    // Очищаем таймер, если мышь убрали до активации эффекта
    if (titleHoverTimers.has(titleElement)) {
        clearTimeout(titleHoverTimers.get(titleElement));
        titleHoverTimers.delete(titleElement);
        console.log(`Timer cancelled for: ${titleElement.textContent}`);
    }
    
    // Убираем эффект прокрутки и индикатор отладки
    titleElement.classList.remove('marquee');
    titleElement.style.outline = '';
}

// Функция архивирования задачи с канбан доски
function archiveTaskFromBoard(taskId) {
    if (confirm('Вы уверены, что хотите архивировать эту задачу?')) {
        fetch(`/tasks/${taskId}/archive`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Удаляем карточку задачи из доски
                const taskCard = document.querySelector(`[data-task-id="${taskId}"]`);
                if (taskCard) {
                    taskCard.remove();
                    updateTaskCounts();
                }
                showSuccessMessage('Задача успешно архивирована');
            } else {
                showErrorMessage(data.message || 'Произошла ошибка при архивировании');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showErrorMessage('Произошла ошибка при архивировании задачи');
        });
    }
}
</script>

@endsection
