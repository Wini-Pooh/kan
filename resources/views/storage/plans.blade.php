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
                            <i class="fas fa-credit-card me-2"></i>
                            Тарифные планы
                        </h1>
                    </div>
                </div>

                <!-- Текущее состояние -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card bg-light">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <h5>Текущее использование памяти</h5>
                                        <div class="progress mb-2" style="height: 8px;">
                                            <div class="progress-bar bg-primary" role="progressbar" 
                                                 style="width: {{ $storageStats['usage_percent'] }}%"></div>
                                        </div>
                                        <small class="text-muted">
                                            {{ $storageStats['formatted_used'] }} из {{ $storageStats['formatted_limit'] }} 
                                            ({{ $storageStats['usage_percent'] }}%)
                                        </small>
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <span class="badge bg-{{ $storageStats['plan_type'] == 'free' ? 'secondary' : 'primary' }} fs-6">
                                            {{ $storageStats['plan_type_display'] }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Тарифные планы -->
                <div class="row">
                    @foreach($plans as $plan)
                        <div class="col-lg-3 col-md-6 mb-4">
                            <div class="card h-100 @if($plan->name == 'test') border-primary @endif">
                                @if($plan->name == 'test')
                                    <div class="card-header bg-primary text-white text-center">
                                        <i class="fas fa-star me-1"></i>
                                        Рекомендуем
                                    </div>
                                @endif
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title text-center">{{ $plan->display_name }}</h5>
                                    
                                    <div class="text-center mb-3">
                                        <div class="h2 text-primary">{{ $plan->formatted_price }}</div>
                                        <small class="text-muted">за {{ $plan->duration_days }} дней</small>
                                    </div>

                                    <div class="text-center mb-3">
                                        <span class="badge bg-success fs-6">
                                            {{ $plan->storage_gb }} ГБ памяти
                                        </span>
                                    </div>

                                    @if($plan->description)
                                        <p class="text-muted text-center">{{ $plan->description }}</p>
                                    @endif

                                    <!-- Особенности плана -->
                                    @if($plan->features)
                                        <ul class="list-unstyled mb-4">
                                            @foreach($plan->features as $feature)
                                                <li class="mb-1">
                                                    <i class="fas fa-check text-success me-2"></i>
                                                    <small>{{ $feature }}</small>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif

                                    <!-- Кнопка покупки -->
                                    <div class="mt-auto text-center">
                                        @if($activeSubscription && $activeSubscription->subscriptionPlan->id == $plan->id)
                                            <button class="btn btn-outline-success" disabled>
                                                <i class="fas fa-check me-2"></i>
                                                Активный план
                                            </button>
                                        @else
                                            <button type="button" 
                                                    class="btn @if($plan->name == 'test') btn-primary @else btn-outline-primary @endif"
                                                    onclick="subscribeToPlan({{ $plan->id }}, '{{ $plan->display_name }}', {{ $plan->price }})">
                                                Выбрать план
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Информация о бесплатном плане -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card border-secondary">
                            <div class="card-header bg-secondary text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-gift me-2"></i>
                                    Бесплатный план
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6>Что включено:</h6>
                                        <ul class="list-unstyled">
                                            <li><i class="fas fa-check text-success me-2"></i>50 МБ памяти</li>
                                            <li><i class="fas fa-check text-success me-2"></i>Неограниченное количество пространств</li>
                                            <li><i class="fas fa-check text-success me-2"></i>Базовые функции канбан доски</li>
                                            <li><i class="fas fa-check text-success me-2"></i>Совместная работа в команде</li>
                                        </ul>
                                    </div>
                                    <div class="col-md-6">
                                        <h6>Ограничения:</h6>
                                        <ul class="list-unstyled">
                                            <li><i class="fas fa-times text-danger me-2"></i>Ограниченный объем загружаемых файлов</li>
                                            <li><i class="fas fa-times text-danger me-2"></i>Базовая поддержка</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- FAQ -->
                <div class="row mt-4">
                    <div class="col-12">
                        <h4>Часто задаваемые вопросы</h4>
                        <div class="accordion" id="faqAccordion">
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                        Что происходит с моими данными при отмене подписки?
                                    </button>
                                </h2>
                                <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        При отмене подписки ваши данные сохраняются, но лимит памяти возвращается к базовому (50 МБ). 
                                        Если вы используете больше памяти чем позволяет бесплатный план, вам нужно будет удалить лишние файлы или продлить подписку.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                        Можно ли комбинировать несколько планов?
                                    </button>
                                </h2>
                                <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        Да, вы можете покупать дополнительную память блоками. Настраиваемые планы позволяют докупать память по 1 ГБ, 5 ГБ или 10 ГБ.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                        Как считается использование памяти?
                                    </button>
                                </h2>
                                <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        <ul>
                                            <li>Создание пространства: +5 МБ</li>
                                            <li>Создание задачи: +0.1 МБ + размер контента</li>
                                            <li>Загрузка файлов: размер файла</li>
                                            <li>Архивация не освобождает память, только удаление</li>
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
</div>

<!-- Модальное окно оплаты -->
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Оформление подписки</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="paymentForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Выбранный план:</label>
                        <div id="selectedPlan" class="fw-bold"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Стоимость:</label>
                        <div id="selectedPrice" class="h5 text-primary"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Способ оплаты:</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_method" value="card" id="card" checked>
                            <label class="form-check-label" for="card">
                                <i class="fas fa-credit-card me-2"></i>Банковская карта
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_method" value="yandex" id="yandex">
                            <label class="form-check-label" for="yandex">
                                <i class="fab fa-yandex me-2"></i>ЮMoney
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_method" value="paypal" id="paypal">
                            <label class="form-check-label" for="paypal">
                                <i class="fab fa-paypal me-2"></i>PayPal
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-credit-card me-2"></i>
                        Оплатить
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function subscribeToPlan(planId, planName, price) {
    document.getElementById('selectedPlan').textContent = planName;
    document.getElementById('selectedPrice').textContent = price + ' RUB';
    document.getElementById('paymentForm').action = '{{ route("storage.subscribe", "") }}/' + planId;
    
    new bootstrap.Modal(document.getElementById('paymentModal')).show();
}
</script>
@endsection
