@extends('layouts.app')

@section('content')
   
<div class="container-fluid">
      
    <div class="row ">
     
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Тарифные планы</h4>
                    <a href="{{ route('memory.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Назад
                    </a>
                </div>
                <div class="card-body">
                    <!-- Текущий план -->
                    <div class="alert alert-info mb-4">
                        <h6><i class="fas fa-info-circle me-2"></i>Текущий план</h6>
                        <p class="mb-1">
                            <strong>Тип:</strong> {{ ucfirst($user->plan_type ?? 'free') }}<br>
                            <strong>Память:</strong> {{ ($user->storage_limit_mb ?? 50) + ($user->additional_storage_mb ?? 0) }} МБ<br>
                            <strong>Использовано:</strong> {{ $user->storage_used_mb ?? 0 }} МБ
                        </p>
                    </div>

                    <!-- Доступные планы -->
                    <div class="row">
                        @foreach($plans as $plan)
                        <div class="col-md-4 mb-4">
                            <div class="card h-100 border-2 {{ $plan['name'] === ($user->plan_type ?? 'free') ? 'border-primary' : '' }}">
                                @if($plan['name'] === ($user->plan_type ?? 'free'))
                                <div class="card-header bg-primary text-white text-center">
                                    <i class="fas fa-check-circle me-1"></i> Текущий план
                                </div>
                                @endif
                                <div class="card-body text-center">
                                    <h5 class="card-title">{{ $plan['display_name'] }}</h5>
                                    <p class="card-text text-muted">{{ $plan['description'] }}</p>
                                    
                                    <div class="mb-3">
                                        <h3 class="text-primary">{{ $plan['price'] }} ₽</h3>
                                        <small class="text-muted">/ месяц</small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <div class="badge bg-success fs-6 mb-2">
                                            {{ $plan['storage_mb'] >= 1024 ? round($plan['storage_mb'] / 1024, 1) . ' ГБ' : $plan['storage_mb'] . ' МБ' }}
                                        </div>
                                    </div>
                                    
                                    <ul class="list-unstyled text-start">
                                        @foreach($plan['features'] as $feature)
                                        <li class="mb-1">
                                            <i class="fas fa-check text-success me-2"></i>
                                            {{ $feature }}
                                        </li>
                                        @endforeach
                                    </ul>
                                </div>
                                <div class="card-footer text-center">
                                    @if($plan['name'] === ($user->plan_type ?? 'free'))
                                    <button class="btn btn-secondary" disabled>
                                        Активный план
                                    </button>
                                    @elseif($plan['name'] === 'free')
                                    <button class="btn btn-outline-secondary" disabled>
                                        Бесплатный план
                                    </button>
                                    @else
                                    <button class="btn btn-primary btn-purchase-plan" 
                                            data-plan-name="{{ $plan['name'] }}" 
                                            data-plan-display="{{ $plan['display_name'] }}" 
                                            data-plan-price="{{ $plan['price'] }}">
                                        Выбрать план
                                    </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <!-- Информация -->
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6>💡 Как работает система</h6>
                                    <ul class="small mb-0">
                                        <li>Базовый план: 50 МБ бесплатно</li>
                                        <li>+5 МБ за каждое созданное пространство</li>
                                        <li>+10 МБ за каждую комнату в пространстве</li>
                                        <li>Дополнительные планы увеличивают лимит</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6>🔒 Гарантии</h6>
                                    <ul class="small mb-0">
                                        <li>Данные защищены и резервируются</li>
                                        <li>Можно отменить подписку в любой момент</li>
                                        <li>Техническая поддержка 24/7</li>
                                        <li>99.9% времени работы сервиса</li>
                                    </ul>
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
document.addEventListener('DOMContentLoaded', function() {
    // Добавляем обработчики событий для всех кнопок покупки
    document.querySelectorAll('.btn-purchase-plan').forEach(button => {
        button.addEventListener('click', function() {
            const planName = this.getAttribute('data-plan-name');
            const planDisplayName = this.getAttribute('data-plan-display');
            const planPrice = this.getAttribute('data-plan-price');
            
            subscribeToPlan(planName, planDisplayName, planPrice);
        });
    });
});

function subscribeToPlan(planName, planDisplayName, price) {
    if (confirm(`Вы хотите подписаться на план "${planDisplayName}" за ${price} ₽/месяц?`)) {
        // Показываем индикатор загрузки
        const button = document.querySelector(`[data-plan-name="${planName}"]`);
        const originalText = button.textContent;
        button.textContent = 'Обработка...';
        button.disabled = true;
        
        // Отправляем AJAX запрос для покупки плана
        fetch('/memory/plans/purchase', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                plan_name: planName
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                // Перезагружаем страницу для обновления данных
                window.location.reload();
            } else {
                alert('Ошибка: ' + data.message);
                // Восстанавливаем кнопку
                button.textContent = originalText;
                button.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Произошла ошибка при покупке плана');
            // Восстанавливаем кнопку
            button.textContent = originalText;
            button.disabled = false;
        });
    }
}
</script>
@endsection
