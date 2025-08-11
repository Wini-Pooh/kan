@extends('layouts.app')

@section('content')
<div class="container-fluid p-0">
    <div class="row g-0">
        @include('layouts.sidebar')

        <!-- Основной контент -->
        <div class="col-md-9 col-lg-10 offset-md-3 offset-lg-2">
            <div class="container-fluid p-4">
                <div class="row">
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h1 class="h3">
                                <i class="fas fa-flask me-2"></i>
                                Демонстрация системы управления памятью
                            </h1>
                            <button class="btn btn-outline-secondary" onclick="resetDemo()">
                                <i class="fas fa-undo me-2"></i>
                                Сбросить демо
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Текущая статистика -->
                <div class="row mb-4">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-chart-pie me-2"></i>
                                    Текущее использование памяти
                                </h5>
                            </div>
                            <div class="card-body">
                                <div id="storageStats">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="text-center">
                                                <div class="h4 text-primary" id="usedStorage">{{ $stats['formatted_used'] }}</div>
                                                <small class="text-muted">Использовано</small>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="text-center">
                                                <div class="h4 text-secondary" id="totalStorage">{{ $stats['formatted_limit'] }}</div>
                                                <small class="text-muted">Лимит</small>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="text-center">
                                                <div class="h4 text-success" id="availableStorage">
                                                    @if($stats['available_mb'] >= 1024)
                                                        {{ round($stats['available_mb'] / 1024, 2) }} ГБ
                                                    @else
                                                        {{ round($stats['available_mb'], 2) }} МБ
                                                    @endif
                                                </div>
                                                <small class="text-muted">Доступно</small>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-4">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Использование:</span>
                                            <span id="usagePercent">{{ $stats['usage_percent'] }}%</span>
                                        </div>
                                        <div class="progress" style="height: 15px;">
                                            <div class="progress-bar" id="progressBar"
                                                 role="progressbar" 
                                                 style="width: {{ $stats['usage_percent'] }}%">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Информация
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <strong>Текущий план:</strong>
                                    <span class="badge bg-{{ $stats['plan_type'] == 'free' ? 'secondary' : 'primary' }}">
                                        {{ $stats['plan_type_display'] }}
                                    </span>
                                </div>
                                <div class="mb-3">
                                    <strong>Базовый лимит:</strong> 50 МБ
                                </div>
                                <div class="mb-3">
                                    <strong>Размеры операций:</strong>
                                    <ul class="small mt-2 mb-0">
                                        <li>Создание пространства: +5 МБ</li>
                                        <li>Создание задачи: +0.1 МБ</li>
                                        <li>Загрузка файла: размер файла</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Демонстрационные действия -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-play me-2"></i>
                                    Попробуйте различные операции
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    @foreach($demoActions as $action)
                                        <div class="col-md-4 mb-3">
                                            <div class="card border-{{ $action['available'] ? 'success' : 'danger' }}">
                                                <div class="card-body text-center">
                                                    <h6 class="card-title">{{ $action['name'] }}</h6>
                                                    <p class="text-muted small">
                                                        Потребует: {{ $action['size_mb'] }} МБ
                                                    </p>
                                                    @if($action['available'])
                                                        <button class="btn btn-success btn-sm" 
                                                                onclick="simulateAction('{{ $action['action'] }}', {{ $action['size_mb'] }})">
                                                            <i class="fas fa-check me-1"></i>
                                                            Выполнить
                                                        </button>
                                                    @else
                                                        <button class="btn btn-danger btn-sm" disabled>
                                                            <i class="fas fa-times me-1"></i>
                                                            Недостаточно места
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Последние операции -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-history me-2"></i>
                                    Последние операции
                                </h5>
                            </div>
                            <div class="card-body">
                                <div id="recentLogs">
                                    @if($recentLogs && $recentLogs->count() > 0)
                                        <div class="list-group">
                                            @foreach($recentLogs as $log)
                                                <div class="list-group-item">
                                                    <div class="d-flex justify-content-between align-items-start">
                                                        <div>
                                                            <h6 class="mb-1">{{ $log->description }}</h6>
                                                            <p class="mb-1 text-muted small">{{ $log->action }}</p>
                                                            <small class="text-muted">{{ $log->created_at->format('d.m.Y H:i:s') }}</small>
                                                        </div>
                                                        <span class="badge bg-{{ $log->storage_used_mb > 0 ? 'danger' : 'success' }}">
                                                            {{ $log->storage_used_mb > 0 ? '+' : '' }}{{ round($log->storage_used_mb, 3) }} МБ
                                                        </span>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-muted text-center">Нет операций</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function simulateAction(action, sizeMb) {
    const button = event.target;
    const originalText = button.innerHTML;
    
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Выполняется...';
    
    fetch('{{ route("storage.simulate") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            action: action,
            size_mb: sizeMb
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Обновляем статистику
            updateStorageStats(data.stats);
            
            // Показываем уведомление
            showNotification(data.message, 'success');
            
            // Перезагружаем страницу через секунду для обновления доступности действий
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            showNotification(data.message, 'error');
            button.disabled = false;
            button.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Произошла ошибка', 'error');
        button.disabled = false;
        button.innerHTML = originalText;
    });
}

function resetDemo() {
    if (!confirm('Вы уверены, что хотите сбросить все демо данные?')) {
        return;
    }
    
    fetch('{{ route("storage.reset-demo") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Произошла ошибка', 'error');
    });
}

function updateStorageStats(stats) {
    document.getElementById('usedStorage').textContent = stats.formatted_used;
    document.getElementById('usagePercent').textContent = stats.usage_percent + '%';
    
    const progressBar = document.getElementById('progressBar');
    progressBar.style.width = stats.usage_percent + '%';
    
    // Меняем цвет в зависимости от использования
    progressBar.className = 'progress-bar';
    if (stats.usage_percent > 95) {
        progressBar.classList.add('bg-danger');
    } else if (stats.usage_percent > 80) {
        progressBar.classList.add('bg-warning');
    } else {
        progressBar.classList.add('bg-primary');
    }
    
    // Обновляем доступную память
    const availableMb = stats.available_mb;
    const availableText = availableMb >= 1024 
        ? (availableMb / 1024).toFixed(2) + ' ГБ'
        : availableMb.toFixed(2) + ' МБ';
    document.getElementById('availableStorage').textContent = availableText;
}

function showNotification(message, type) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const iconClass = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    
    const notification = document.createElement('div');
    notification.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        <i class="fas ${iconClass} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    // Автоматически удаляем через 5 секунд
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}
</script>
@endsection
