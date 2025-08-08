@extends('layouts.app')

@section('content')
<div class="kanban-container">
    <div class="kanban-header">
        <h1 class="kanban-title">{{ $space->name ?? 'Kanban Board' }}</h1>
        <div class="header-actions">
            <button class="add-column-btn" onclick="showAddColumnModal()">
                <i class="fas fa-columns"></i>
                Добавить колонку
            </button>
        </div>
    </div>
    
    <div class="kanban-board" id="kanban-board">
        @if($columns && $columns->count() > 0)
            @foreach($columns as $column)
            <div class="kanban-column" data-column-id="{{ $column->id }}" data-status="{{ $column->slug }}">
                <div class="column-header" style="border-left: 4px solid {{ $column->color }}">
                    <div class="column-move-actions">
                        @if(!$loop->first)
                        <button class="column-move-btn" onclick="moveColumn({{ $column->id }}, 'left')" title="Переместить влево">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        @endif
                    </div>
                    <div class="column-info">
                        <h3 class="column-title" ondblclick="editColumnNameInline({{ $column->id }})" title="Двойной клик для редактирования">{{ $column->name }}</h3>
                        <span class="task-count" id="count-{{ $column->id }}">{{ $column->tasks->count() }}</span>
                    </div>
                    <div class="column-move-actions">
                        @if(!$loop->last)
                        <button class="column-move-btn" onclick="moveColumn({{ $column->id }}, 'right')" title="Переместить вправо">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                        @endif
                    </div>
                    <div class="column-actions">
                    
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
                    
                    <!-- Кнопка создания новой задачи в колонке -->
                    <div class="add-task-placeholder" onclick="createQuickTask({{ $column->id }}, '{{ $column->slug }}')" style="margin-bottom: 15px;">
                        <i class="fas fa-plus"></i>
                        <span>Добавить задачу</span>
                    </div>
                    
                    @foreach($column->tasks as $task)
                    <div class="task-card {{ $task->status === 'done' ? 'completed' : '' }}" draggable="true" ondragstart="drag(event)" data-task-id="{{ $task->id }}" onclick="openTaskPage({{ $task->id }})">
                        <div class="task-header">
                            <div class="task-title" ondblclick="editTaskNameInline({{ $task->id }}, event)" title="Двойной клик для редактирования">{{ $task->title }}</div>
                            <div class="task-priority priority-{{ $task->priority }}"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
            
            <!-- Блок для добавления новой колонки -->
            <div class="kanban-column add-column-placeholder" onclick="createNewColumn()">
                <div class="column-header add-column-header">
                    <div class="add-column-content">
                        <i class="fas fa-plus add-column-icon"></i>
                        <span class="add-column-text">Добавить колонку</span>
                    </div>
                </div>
            </div>
        @else
            <!-- Блок для добавления первой колонки -->
            <div class="kanban-column add-column-placeholder" onclick="createNewColumn()">
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

<!-- Модальное окно для добавления колонки -->
<div class="modal fade" id="addColumnModal" tabindex="-1" aria-labelledby="addColumnModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addColumnModalLabel">Добавить новую колонку</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addColumnForm">
                    <div class="mb-3">
                        <label for="columnName" class="form-label">Название колонки</label>
                        <input type="text" class="form-control" id="columnName" required>
                    </div>
                    <div class="mb-3">
                        <label for="columnColor" class="form-label">Цвет колонки</label>
                        <input type="color" class="form-control form-control-color" id="columnColor" value="#6c757d">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary" onclick="addColumn()">Добавить колонку</button>
            </div>
        </div>
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
                        <input type="text" class="form-control" id="editColumnName" required>
                    </div>
                    <div class="mb-3">
                        <label for="editColumnColor" class="form-label">Цвет колонки</label>
                        <input type="color" class="form-control form-control-color" id="editColumnColor">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary" onclick="updateColumn()">Сохранить изменения</button>
            </div>
        </div>
    </div>
</div>



<script>
let draggedElement = null;
let taskIdCounter = {{ ($tasks->flatten()->max('id') ?? 0) + 1 }}; // Начинаем с следующего ID после максимального
const spaceId = {{ $space->id }};
const csrfToken = '{{ csrf_token() }}';

// Отладочная информация
console.log('Kanban Board initialized:', {
    spaceId: spaceId,
    taskIdCounter: taskIdCounter,
    csrfToken: csrfToken ? 'Present' : 'Missing'
});

// Функции для drag and drop
function allowDrop(ev) {
    ev.preventDefault();
    ev.currentTarget.classList.add('drag-over');
}

function drag(ev) {
    draggedElement = ev.target;
    ev.target.classList.add('dragging');
    ev.dataTransfer.setData("text", ev.target.getAttribute('data-task-id'));
}

function drop(ev) {
    ev.preventDefault();
    ev.currentTarget.classList.remove('drag-over');
    
    if (draggedElement) {
        const taskId = ev.dataTransfer.getData("text");
        const targetColumn = ev.currentTarget;
        const targetColumnId = targetColumn.getAttribute('data-column-id');
        const targetStatus = targetColumn.getAttribute('data-status');
        
        // Находим кнопку добавления задачи в целевой колонке
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
            updateTaskColumnAjax(taskId, targetColumnId, targetStatus);
        } else {
            updateTaskStatusAjax(taskId, targetStatus);
        }
        
        // Обновляем счетчики
        updateTaskCounts();
        
        // Убираем визуальные эффекты
        draggedElement.classList.remove('dragging');
        draggedElement = null;
    }
}

// AJAX функция для обновления колонки задачи
function updateTaskColumnAjax(taskId, columnId, newStatus) {
    fetch(`/api/spaces/${spaceId}/tasks/${taskId}/status`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        credentials: 'same-origin',
        body: JSON.stringify({
            column_id: columnId,
            status: newStatus
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
            updateTaskStatus(taskId, newStatus);
            console.log('Задача перемещена в колонку:', data.message);
        } else {
            console.error('Ошибка обновления колонки задачи:', data.message);
            // Возвращаем задачу обратно в случае ошибки
            location.reload();
        }
    })
    .catch(error => {
        console.error('Ошибка запроса:', error);
        location.reload();
    });
}

// AJAX функция для обновления статуса задачи
function updateTaskStatusAjax(taskId, newStatus) {
    fetch(`/api/spaces/${spaceId}/tasks/${taskId}/status`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        credentials: 'same-origin',
        body: JSON.stringify({
            status: newStatus
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
            updateTaskStatus(taskId, newStatus);
            console.log('Статус задачи обновлен:', data.message);
        } else {
            console.error('Ошибка обновления статуса:', data.message);
            // Возвращаем задачу обратно в случае ошибки
            location.reload();
        }
    })
    .catch(error => {
        console.error('Ошибка запроса:', error);
        location.reload();
    });
}

// Обработчики событий для улучшения UX
document.addEventListener('DOMContentLoaded', function() {
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
            }
        });
    });
    
    // Обновляем счетчики при загрузке
    updateTaskCounts();
});

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
    // Обновляем счетчики для всех колонок
    const columns = document.querySelectorAll('.kanban-column');
    columns.forEach(column => {
        const columnId = column.getAttribute('data-column-id');
        const taskCount = column.querySelectorAll('.task-card').length;
        
        if (columnId) {
            const countElement = document.getElementById(`count-${columnId}`);
            if (countElement) {
                countElement.textContent = taskCount;
            }
        } else {
            // Для обратной совместимости со старыми статическими колонками
            const status = column.getAttribute('data-status');
            const countElement = document.getElementById(`${status}-count`);
            if (countElement) {
                countElement.textContent = taskCount;
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

function showAddColumnModal() {
    const modal = new bootstrap.Modal(document.getElementById('addColumnModal'));
    modal.show();
}

function addColumn() {
    const name = document.getElementById('columnName').value;
    const color = document.getElementById('columnColor').value;
    
    if (!name.trim()) {
        alert('Пожалуйста, введите название колонки');
        return;
    }
    
    // Сначала получаем CSRF cookie
    fetch('/sanctum/csrf-cookie', {
        method: 'GET',
        credentials: 'same-origin',
    })
    .then(() => {
        // Затем выполняем основной запрос
        return fetch(`/api/spaces/${spaceId}/columns`, {
            method: 'POST',
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
            // Создаем новую колонку
            const newColumn = createColumnElement(data.column);
            document.getElementById('kanban-board').appendChild(newColumn);
            
            // Очищаем форму и закрываем модальное окно
            document.getElementById('addColumnForm').reset();
            const modal = bootstrap.Modal.getInstance(document.getElementById('addColumnModal'));
            modal.hide();
            
            console.log('Добавлена новая колонка:', data.column);
        } else {
            alert('Ошибка создания колонки: ' + (data.message || 'Неизвестная ошибка'));
        }
    })
    .catch(error => {
        console.error('Ошибка запроса:', error);
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
        <div class="column-header" style="border-left: 4px solid ${column.color}">
            <div class="column-move-actions">
                ${!isFirst ? `<button class="column-move-btn" onclick="moveColumn(${column.id}, 'left')" title="Переместить влево">
                    <i class="fas fa-chevron-left"></i>
                </button>` : ''}
            </div>
            <div class="column-info">
                <h3 class="column-title" ondblclick="editColumnNameInline(${column.id})" title="Двойной клик для редактирования">${column.name}</h3>
                <span class="task-count" id="count-${column.id}">0</span>
            </div>
            <div class="column-move-actions">
                ${!isLast ? `<button class="column-move-btn" onclick="moveColumn(${column.id}, 'right')" title="Переместить вправо">
                    <i class="fas fa-chevron-right"></i>
                </button>` : ''}
            </div>
            <div class="column-actions">
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
            
            <!-- Кнопка создания новой задачи в колонке -->
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
            columnElement.querySelector('.column-title').textContent = data.column.name;
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
    if (confirm('Вы уверены, что хотите удалить эту колонку? Все задачи в ней будут потеряны.')) {
        fetch(`/api/spaces/${spaceId}/columns/${columnId}`, {
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
                const columnElement = document.querySelector(`[data-column-id="${columnId}"]`);
                if (columnElement) {
                    columnElement.remove();
                    console.log('Колонка удалена:', columnId);
                }
            } else {
                alert('Ошибка удаления колонки: ' + (data.message || 'Неизвестная ошибка'));
            }
        })
        .catch(error => {
            console.error('Ошибка запроса:', error);
            alert('Ошибка соединения с сервером');
        });
    }
}

function moveColumn(columnId, direction) {
    fetch(`/api/columns/${columnId}/move`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
        },
        credentials: 'same-origin',
        body: JSON.stringify({
            direction: direction
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
            location.reload();
        } else {
            alert('Ошибка при перемещении колонки: ' + (data.message || 'Неизвестная ошибка'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Ошибка при перемещении колонки: ' + error.message);
    });
}

function editColumnNameInline(columnId) {
    const columnElement = document.querySelector(`[data-column-id="${columnId}"]`);
    const titleElement = columnElement.querySelector('.column-title');
    const currentText = titleElement.textContent.trim();
    
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
    
    // Маппинг slug колонки к валидным статусам
    let status = 'todo'; // По умолчанию
    
    // Нормализуем slug и определяем статус
    const normalizedSlug = columnSlug.toLowerCase().replace(/[^a-z0-9]/g, '-');
    
    if (normalizedSlug.includes('progress') || normalizedSlug.includes('process') || 
        normalizedSlug.includes('doing') || normalizedSlug.includes('в-работе') ||
        normalizedSlug.includes('работе') || columnSlug === 'progress') {
        status = 'progress';
    } else if (normalizedSlug.includes('done') || normalizedSlug.includes('completed') || 
               normalizedSlug.includes('выполнено') || normalizedSlug.includes('готово') ||
               normalizedSlug.includes('finish') || columnSlug === 'done') {
        status = 'done';
    } else {
        status = 'todo'; // Все остальное в "К выполнению"
    }
    
    console.log('Creating task with:', {
        title: taskTitle,
        columnId,
        columnSlug,
        normalizedSlug,
        status,
        spaceId
    });
    
    // Отправляем AJAX запрос для создания задачи
    fetch(`/api/spaces/${spaceId}/tasks`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
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
            const taskCard = createSimpleTaskCard(data.task.id, data.task.title);
            
            // Добавляем задачу в соответствующую колонку (после кнопки добавления)
            const targetColumn = document.querySelector(`[data-column-id="${columnId}"] .column-content`);
            const addButton = targetColumn.querySelector('.add-task-placeholder');
            addButton.insertAdjacentElement('afterend', taskCard);
            
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

function createSimpleTaskCard(taskId, title) {
    const taskCard = document.createElement('div');
    taskCard.className = 'task-card';
    taskCard.draggable = true;
    taskCard.setAttribute('data-task-id', taskId);
    taskCard.ondragstart = drag;
    taskCard.onclick = function() { openTaskPage(taskId); };
    
    taskCard.innerHTML = `
        <div class="task-header">
            <div class="task-title" ondblclick="editTaskNameInline(${taskId}, event)" title="Двойной клик для редактирования">${title}</div>
            <div class="task-priority priority-medium"></div>
        </div>
    `;
    
    return taskCard;
}

function openTaskPage(taskId) {
    // Переходим на страницу просмотра/редактирования задачи
    window.location.href = `/tasks/${taskId}`;
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
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
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
                    updateTaskCounts();
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

function changeTime(taskId) {
    console.log('Изменение времени задачи:', taskId);
    alert(`Изменение времени задачи #${taskId} - функция будет реализована позже`);
}
</script>

@endsection
