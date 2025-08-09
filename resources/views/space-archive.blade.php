@extends('layouts.app')

@section('content')
<div class="container-fluid">
  
    <div class="row">
         
    @include('layouts.sidebar')

        <!-- Основной контент -->
        <div class="col-md-9 col-lg-10 main-content">
            <div class="archive-container">
                <div class="archive-header mb-4">
                    <h1 class="archive-title d-flex justify-content-between align-items-center">
                        <a href="{{ route('spaces.show', [$organization, $space]) }}" 
                           class="back-arrow-link" 
                           title="Вернуться к пространству {{ $space->name }}">
                            <i class="fas fa-arrow-left"></i>
                        </a>
                       
                        Архив пространства "{{ $space->name }}"
                    </h1>
                  
                </div>

                @if($archivedTasks && $archivedTasks->count() > 0)
                    <div class="row g-4">
                        @foreach($archivedTasks as $task)
                            <div class="col-lg-4 col-md-6 col-sm-12">
                                <div class="task-card archived-task-card {{ $task->status === 'done' ? 'completed' : '' }}" 
                                     onclick="openTask({{ $task->id }}, event)"
                                     onkeydown="handleCardKeydown(event, {{ $task->id }})"
                                     tabindex="0"
                                     role="button"
                                     aria-label="Открыть задачу: {{ $task->title }}">
                                    
                                    <div class="task-header">
                                        <div class="task-title">{{ $task->title }}</div>
                                        <div class="status-square" data-priority="{{ $task->priority ?? 'low' }}" 
                                             title="{{ $task->priority_label }}"></div>
                                    </div>
                                    
                                    @if($task->description)
                                        <div class="task-description">{{ Str::limit($task->description, 100) }}</div>
                                    @endif
                                    
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
                                        
                                        @if($task->column)
                                            <div class="date-item">
                                                <i class="fas fa-columns"></i>
                                                {{ $task->column->name }}
                                            </div>
                                        @else
                                            <div class="date-item">
                                                <i class="fas fa-columns"></i>
                                                <span class="text-muted">Колонка удалена</span>
                                            </div>
                                        @endif
                                        
                                        <div class="date-item">
                                            <i class="fas fa-archive"></i>
                                            {{ $task->archived_at->diffForHumans() }}
                                        </div>
                                        
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
                                    <div class="task-actions" onclick="event.stopPropagation();">
                                        <button class="task-action-btn view-btn" onclick="openTask({{ $task->id }})" title="Открыть задачу">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="task-action-btn archive-btn" onclick="unarchiveTask({{ $task->id }})" title="Разархивировать задачу">
                                            <i class="fas fa-undo"></i>
                                        </button>
                                    </div>
                                    
                                    <!-- Архивная метка -->
                                    <div class="archive-badge">
                                        <i class="fas fa-archive"></i>
                                        Архив
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-archive" style="font-size: 4rem; color: #6c757d; margin-bottom: 1rem;"></i>
                        <h5 class="text-muted">Архив пуст</h5>
                        <p class="text-muted">В этом пространстве пока нет архивированных задач</p>
                        <a href="{{ route('spaces.show', [$organization, $space]) }}" class="btn ">
                            <i class="fas fa-arrow-left me-1"></i>
                            Вернуться к пространству
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
.archive-container {
    padding: 2rem;
}

.archive-title {
    color: #343a40;
    font-size: 1.75rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.back-arrow-link {
    color: #6c757d;
    text-decoration: none;
    margin-right: 1rem;
    font-size: 1.2rem;
    transition: color 0.2s;
}

.back-arrow-link:hover {
    color: #343a40;
}

/* Основные стили для карточек задач (как на канбан доске) */
.task-card {
    background-color: var(--sketch-white, #ffffff);
    border: 1px solid #e0e0e0;
    border-radius: 9px;
    padding: 16px;
    min-height: 180px;
    cursor: pointer;
    transition: all 0.2s ease;
    position: relative;
    user-select: none;
    margin-bottom: 1rem;
}

.task-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    cursor: pointer;
}

.task-card:focus {
    outline: 2px solid #007bff;
    outline-offset: 2px;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.task-card:active {
    transform: translateY(-1px);
}

.task-card.completed {
    background-color: #f8f9fa;
    opacity: 0.8;
}

.task-card.completed .task-title {
    text-decoration: line-through;
}

/* Архивные карточки с особой меткой */
.archived-task-card {
    border-left: 4px solid #6c757d;
    background: linear-gradient(145deg, #ffffff 0%, #f8f9fa 100%);
}

.task-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 10px;
}

.task-title {
    font-size: 16px;
    font-weight: bold;
    color: var(--sketch-black, #333);
    flex: 1;
    margin-right: 10px;
}

.task-description {
    font-size: 14px;
    color: var(--sketch-dark-gray, #666);
    margin-bottom: 15px;
    line-height: 1.4;
}

/* Стили для status-square */
.status-square {
    width: 16px;
    height: 16px;
    border-radius: 3px;
    cursor: pointer;
    transition: all 0.2s;
    border: 1px solid rgba(0, 0, 0, 0.1);
    position: relative;
    flex-shrink: 0;
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

/* Стили для мета-информации задачи */
.task-meta {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-top: 8px;
    font-size: 11px;
    color: var(--sketch-dark-gray, #666);
}

/* Стили для дат */
.task-dates {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.date-item {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 11px;
}

.date-item i {
    width: 12px;
    font-size: 10px;
}

/* Стили для аватара пользователя */
.task-assignee {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    margin-top: 4px;
}

.user-avatar {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid #fff;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
}

.user-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar-placeholder {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    font-size: 10px;
    font-weight: bold;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Стили для действий с задачей */
.task-actions {
    display: flex;
    gap: 8px;
    opacity: 0;
    transition: opacity 0.2s ease;
    position: absolute;
    top: 8px;
    right: 8px;
}

.task-card:hover .task-actions {
    opacity: 1;
}

.task-action-btn {
    background: none;
    border: none;
    padding: 6px;
    cursor: pointer;
    border-radius: 4px;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--sketch-dark-gray, #666);
    width: 28px;
    height: 28px;
}

.task-action-btn:hover {
    background-color: var(--sketch-medium-gray, #e9ecef);
}

.archive-btn:hover {
    color: #28a745;
    background-color: rgba(40, 167, 69, 0.1);
}

.view-btn:hover {
    color: #007bff;
    background-color: rgba(0, 123, 255, 0.1);
}

/* Архивная метка */
.archive-badge {
    position: absolute;
    bottom: 8px;
    right: 8px;
    background: linear-gradient(135deg, #6c757d, #495057);
    color: white;
    font-size: 9px;
    padding: 2px 6px;
    border-radius: 3px;
    display: flex;
    align-items: center;
    gap: 3px;
    opacity: 0.8;
}

.archive-badge i {
    font-size: 8px;
}

/* Стили для боковой панели */
.sidebar {
    background-color: #f8f9fa;
    border-right: 1px solid #dee2e6;
    min-height: 100vh;
    padding: 1rem;
}

.sidebar-title {
    color: #343a40;
    font-weight: 600;
    margin-bottom: 1rem;
    font-size: 1rem;
}

.organization-item .active {
    background-color: var(--sketch-light-gray, #f8f9fa) !important;
    font-weight: bold;
}

.main-content {
    padding: 0;
}

/* Адаптивность */
@media (max-width: 768px) {
    .archive-container {
        padding: 1rem;
    }
    
    .task-card {
        min-height: 150px;
        padding: 12px;
    }
    
    .task-title {
        font-size: 14px;
    }
    
    .task-actions {
        position: static;
        opacity: 1;
        justify-content: flex-end;
        margin-top: 10px;
    }
}
</style>

<script>
function openTask(taskId, event) {
    // Предотвращаем действие по умолчанию, если клик произошел на кнопке действия
    if (event && event.target.closest('.task-actions')) {
        return;
    }
    
    // Переходим к просмотру задачи
    window.location.href = `/tasks/${taskId}`;
}

function handleCardKeydown(event, taskId) {
    // Обработка нажатия Enter или Space для доступности
    if (event.key === 'Enter' || event.key === ' ') {
        event.preventDefault();
        openTask(taskId, event);
    }
}

function unarchiveTask(taskId) {
    if (confirm('Вы уверены, что хотите разархивировать эту задачу?')) {
        fetch(`/tasks/${taskId}/unarchive`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Показываем уведомление
                showNotification('Задача успешно разархивирована', 'success');
                // Перезагружаем страницу через небольшую задержку
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                showNotification(data.message || 'Произошла ошибка', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Произошла ошибка при разархивировании задачи', 'error');
        });
    }
}

function showNotification(message, type) {
    // Создаем элемент уведомления
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed`;
    notification.style.cssText = `
        top: 20px;
        right: 20px;
        z-index: 9999;
        min-width: 300px;
    `;
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    // Автоматически удаляем уведомление через 5 секунд
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}
</script>
@endsection
