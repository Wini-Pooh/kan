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
                        <h1 class="h3 mb-4">
                            <i class="fas fa-hdd me-2"></i>
                            Управление памятью
                        </h1>
                    </div>
                </div>

                <!-- Общая статистика -->
                <div class="row mb-4">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-chart-pie me-2"></i>
                                    Использование памяти
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Использовано</label>
                                            <div class="h4 text-primary">{{ $storageStats['formatted_used'] }}</div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Лимит</label>
                                            <div class="h4 text-secondary">{{ $storageStats['formatted_limit'] }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Доступно</label>
                                            <div class="h4 text-success">
                                                @if($storageStats['available_mb'] >= 1024)
                                                    {{ round($storageStats['available_mb'] / 1024, 2) }} ГБ
                                                @else
                                                    {{ round($storageStats['available_mb'], 2) }} МБ
                                                @endif
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Текущий план</label>
                                            <div class="h5">
                                                <span class="badge bg-{{ $storageStats['plan_type'] == 'free' ? 'secondary' : ($storageStats['plan_type'] == 'test' ? 'info' : 'primary') }}">
                                                    {{ $storageStats['plan_type_display'] }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Прогресс бар -->
                                <div class="mt-4">
                                    <label class="form-label">Использование: {{ $storageStats['usage_percent'] }}%</label>
                                    <div class="progress" style="height: 10px;">
                                        <div class="progress-bar 
                                            @if($storageStats['usage_percent'] < 70) bg-success
                                            @elseif($storageStats['usage_percent'] < 90) bg-warning  
                                            @else bg-danger
                                            @endif" 
                                            role="progressbar" 
                                            style="width: {{ $storageStats['usage_percent'] }}%">
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
                                    <i class="fas fa-crown me-2"></i>
                                    Подписка
                                </h5>
                            </div>
                            <div class="card-body">
                                @if($activeSubscription)
                                    <div class="text-center">
                                        <h6 class="text-success">{{ $activeSubscription->subscriptionPlan->display_name }}</h6>
                                        <p class="text-muted">
                                            Истекает через {{ $activeSubscription->days_remaining }} дней
                                        </p>
                                        <form method="POST" action="{{ route('storage.unsubscribe', $activeSubscription) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm" 
                                                    onclick="return confirm('Вы уверены, что хотите отменить подписку?')">
                                                Отменить подписку
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <div class="text-center">
                                        <p class="text-muted">Нет активной подписки</p>
                                        <a href="{{ route('storage.plans') }}" class="btn btn-primary">
                                            Выбрать тарифный план
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Рекомендации по оптимизации -->
                @if(count($optimizationTips) > 0)
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-lightbulb me-2"></i>
                                    Рекомендации
                                </h5>
                            </div>
                            <div class="card-body">
                                @foreach($optimizationTips as $tip)
                                    <div class="alert alert-{{ $tip['type'] == 'warning' ? 'warning' : ($tip['type'] == 'danger' ? 'danger' : 'info') }}" role="alert">
                                        <i class="fas fa-{{ $tip['type'] == 'warning' ? 'exclamation-triangle' : ($tip['type'] == 'danger' ? 'exclamation-circle' : 'info-circle') }} me-2"></i>
                                        {{ $tip['message'] }}
                                    </div>
                                @endforeach
                                
                                <!-- Кнопка очистки архива -->
                                <button type="button" class="btn btn-outline-warning" onclick="cleanupArchived()">
                                    <i class="fas fa-broom me-2"></i>
                                    Очистить архивированные задачи
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Детальная статистика -->
                <div class="row">
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-chart-bar me-2"></i>
                                    Использование по типам (30 дней)
                                </h5>
                            </div>
                            <div class="card-body">
                                @if($detailedStats['usage_by_type']->count() > 0)
                                    @foreach($detailedStats['usage_by_type'] as $usage)
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="text-capitalize">
                                                @switch($usage->entity_type)
                                                    @case('space')
                                                        <i class="fas fa-folder me-1"></i>Пространства
                                                        @break
                                                    @case('task')
                                                        <i class="fas fa-tasks me-1"></i>Задачи
                                                        @break
                                                    @case('file')
                                                        <i class="fas fa-file me-1"></i>Файлы
                                                        @break
                                                    @default
                                                        {{ ucfirst($usage->entity_type) }}
                                                @endswitch
                                            </span>
                                            <span class="badge bg-primary">
                                                {{ round($usage->total_usage, 2) }} МБ
                                            </span>
                                        </div>
                                    @endforeach
                                @else
                                    <p class="text-muted">Нет данных за последние 30 дней</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-history me-2"></i>
                                    Последние операции
                                </h5>
                            </div>
                            <div class="card-body">
                                @if($detailedStats['recent_logs']->count() > 0)
                                    <div class="list-group list-group-flush">
                                        @foreach($detailedStats['recent_logs']->take(10) as $log)
                                            <div class="list-group-item px-0">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div class="flex-grow-1">
                                                        <small class="text-muted">{{ $log->created_at->format('d.m.Y H:i') }}</small>
                                                        <div class="fw-bold">{{ $log->description }}</div>
                                                        <small class="text-muted">
                                                            @if($log->storage_used_mb > 0)
                                                                <span class="text-danger">+{{ round($log->storage_used_mb, 3) }} МБ</span>
                                                            @elseif($log->storage_used_mb < 0)
                                                                <span class="text-success">{{ round($log->storage_used_mb, 3) }} МБ</span>
                                                            @else
                                                                <span class="text-muted">0 МБ</span>
                                                            @endif
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-muted">Нет записей</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function cleanupArchived() {
    if (!confirm('Удалить все архивированные задачи старше 30 дней? Это действие нельзя отменить.')) {
        return;
    }
    
    fetch('{{ route("storage.cleanup") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            days: 30
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('Ошибка: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Произошла ошибка при очистке');
    });
}
</script>
@endsection
