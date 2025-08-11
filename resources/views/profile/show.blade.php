@extends('layouts.app')

@section('content')
<div class="container-fluid p-0">
    <div class="row g-0">
        @include('layouts.sidebar')

        <!-- Основной контент -->
        <div class="col-md-9 col-lg-10 offset-md-3 offset-lg-2">
            <div class="main-content p-4">
                @if (session('success'))
                    <div class="alert alert-success mb-4" role="alert">
                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger mb-4" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Заголовок страницы и профильная информация -->
                <div class="profile-header mb-5">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <div class="profile-avatar-large">
                                @if($user->photo)
                                    <img src="{{ asset('storage/photos/' . $user->photo) }}" 
                                         alt="Фото профиля" 
                                         class="profile-photo">
                                @else
                                    <div class="profile-initials">
                                        {{ substr($user->name, 0, 1) }}
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="col">
                            <h1 class="profile-name">{{ $user->name }}</h1>
                            <p class="profile-details">
                                @if($user->position && $user->company_name)
                                    {{ $user->position }} в {{ $user->company_name }}
                                @elseif($user->position)
                                    {{ $user->position }}
                                @elseif($user->company_name)
                                    {{ $user->company_name }}
                                @else
                                    Участник системы
                                @endif
                            </p>
                            <div class="profile-stats">
                                <span class="stat-item">
                                    <i class="fas fa-building me-1"></i>
                                    {{ $userOrganizations->count() }} организаций
                                </span>
                                <span class="stat-item ms-3">
                                    <i class="fas fa-crown me-1"></i>
                                    {{ $ownedOrganizations->count() }} владею
                                </span>
                                <span class="stat-item ms-3">
                                    <i class="fas fa-calendar me-1"></i>
                                    С {{ $user->created_at->format('M Y') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Табы для навигации -->
                <div class="profile-tabs mb-4">
                    <ul class="nav nav-tabs" id="profileTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="personal-tab" data-bs-toggle="tab" data-bs-target="#personal" type="button" role="tab">
                                <i class="fas fa-user me-2"></i>Личная информация
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact" type="button" role="tab">
                                <i class="fas fa-address-book me-2"></i>Контактные данные
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="work-tab" data-bs-toggle="tab" data-bs-target="#work" type="button" role="tab">
                                <i class="fas fa-briefcase me-2"></i>Рабочая информация
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="security-tab" data-bs-toggle="tab" data-bs-target="#security" type="button" role="tab">
                                <i class="fas fa-shield-alt me-2"></i>Безопасность
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="storage-tab" data-bs-toggle="tab" data-bs-target="#storage" type="button" role="tab">
                                <i class="fas fa-hdd me-2"></i>Управление памятью
                            </button>
                        </li>
                    </ul>
                </div>

                <!-- Содержимое табов -->
                <div class="tab-content" id="profileTabsContent">
                    <!-- Личная информация -->
                    <div class="tab-pane fade show active" id="personal" role="tabpanel">
                        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PATCH')
                            
                            <div class="row">
                                <div class="col-lg-8">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="mb-0"><i class="fas fa-user me-2"></i>Основная информация</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row mb-4">
                                                <div class="col-md-12">
                                                    <label for="photo" class="form-label">Фото профиля</label>
                                                    <div class="photo-upload-section">
                                                        <div class="current-photo">
                                                            @if($user->photo)
                                                                <img src="{{ asset('storage/photos/' . $user->photo) }}" 
                                                                     alt="Текущее фото" 
                                                                     class="current-photo-img">
                                                            @else
                                                                <div class="no-photo">
                                                                    <i class="fas fa-user"></i>
                                                                </div>
                                                            @endif
                                                        </div>
                                                        <div class="photo-upload-controls">
                                                            <input type="file" class="form-control" id="photo" name="photo" accept="image/*">
                                                            <small class="form-text text-muted">JPG, PNG или GIF. Максимум 2МБ.</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row mb-3">
                                                <div class="col-md-6">
                                                    <label for="name" class="form-label">Отображаемое имя *</label>
                                                    <input type="text" class="form-control" id="name" name="name" 
                                                           value="{{ old('name', $user->name) }}" required
                                                           placeholder="Как вас должны называть" maxlength="100">
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="gender" class="form-label">Пол</label>
                                                    <select class="form-control" id="gender" name="gender">
                                                        <option value="">Не указан</option>
                                                        <option value="male" {{ old('gender', $user->gender) === 'male' ? 'selected' : '' }}>Мужской</option>
                                                        <option value="female" {{ old('gender', $user->gender) === 'female' ? 'selected' : '' }}>Женский</option>
                                                        <option value="other" {{ old('gender', $user->gender) === 'other' ? 'selected' : '' }}>Другой</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="row mb-3">
                                                <div class="col-md-4">
                                                    <label for="first_name" class="form-label">Имя</label>
                                                    <input type="text" class="form-control" id="first_name" name="first_name" 
                                                           value="{{ old('first_name', $user->first_name) }}"
                                                           placeholder="Ваше имя" maxlength="50">
                                                </div>
                                                <div class="col-md-4">
                                                    <label for="last_name" class="form-label">Фамилия</label>
                                                    <input type="text" class="form-control" id="last_name" name="last_name" 
                                                           value="{{ old('last_name', $user->last_name) }}"
                                                           placeholder="Ваша фамилия" maxlength="50">
                                                </div>
                                                <div class="col-md-4">
                                                    <label for="middle_name" class="form-label">Отчество</label>
                                                    <input type="text" class="form-control" id="middle_name" name="middle_name" 
                                                           value="{{ old('middle_name', $user->middle_name) }}"
                                                           placeholder="Ваше отчество" maxlength="50">
                                                </div>
                                            </div>

                                            <div class="row mb-3">
                                                <div class="col-md-6">
                                                    <label for="age" class="form-label">Возраст</label>
                                                    <input type="number" class="form-control" id="age" name="age" 
                                                           value="{{ old('age', $user->age) }}" min="1" max="150"
                                                           placeholder="Ваш возраст">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-lg-4">
                                    <div class="card profile-summary">
                                        <div class="card-header">
                                            <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Информация профиля</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="info-item">
                                                <span class="info-label">Статус профиля:</span>
                                                <span class="info-value">
                                                    @if($user->first_name && $user->last_name && $user->phone)
                                                        <span class="badge bg-success">Заполнен</span>
                                                    @else
                                                        <span class="badge bg-warning">Частично заполнен</span>
                                                    @endif
                                                </span>
                                            </div>
                                            <div class="info-item">
                                                <span class="info-label">Последнее обновление:</span>
                                                <span class="info-value">{{ $user->updated_at->format('d.m.Y H:i') }}</span>
                                            </div>
                                            <div class="info-item">
                                                <span class="info-label">ID пользователя:</span>
                                                <span class="info-value">#{{ $user->id }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-actions mt-4">
                                <button type="submit" class="btn  btn-lg">
                                    <i class="fas fa-save me-2"></i>Сохранить личную информацию
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Контактные данные -->
                    <div class="tab-pane fade" id="contact" role="tabpanel">
                        <form action="{{ route('profile.update') }}" method="POST">
                            @csrf
                            @method('PATCH')
                            
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-address-book me-2"></i>Контактная информация</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="phone" class="form-label">Номер телефона *</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                                <input type="text" class="form-control" id="phone" name="phone" 
                                                       value="{{ old('phone', $user->phone) }}" required
                                                       placeholder="+7 (999) 999-99-99" maxlength="18">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="email" class="form-label">Email</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                                <input type="email" class="form-control" id="email" name="email" 
                                                       value="{{ old('email', $user->email) }}"
                                                       placeholder="your@email.com" maxlength="255">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-actions mt-4">
                                <button type="submit" class="btn  btn-lg">
                                    <i class="fas fa-save me-2"></i>Сохранить контактные данные
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Рабочая информация -->
                    <div class="tab-pane fade" id="work" role="tabpanel">
                        <form action="{{ route('profile.update') }}" method="POST">
                            @csrf
                            @method('PATCH')
                            
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-briefcase me-2"></i>Рабочая информация</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="company_name" class="form-label">Наименование компании</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-building"></i></span>
                                                <input type="text" class="form-control" id="company_name" name="company_name" 
                                                       value="{{ old('company_name', $user->company_name) }}"
                                                       placeholder="ООО 'Название компании'">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="position" class="form-label">Должность</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-user-tie"></i></span>
                                                <input type="text" class="form-control" id="position" name="position" 
                                                       value="{{ old('position', $user->position) }}"
                                                       placeholder="Руководитель проекта" maxlength="100">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-actions mt-4">
                                <button type="submit" class="btn  btn-lg">
                                    <i class="fas fa-save me-2"></i>Сохранить рабочую информацию
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Безопасность -->
                    <div class="tab-pane fade" id="security" role="tabpanel">
                        <div class="row">
                            <div class="col-lg-8">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0"><i class="fas fa-key me-2"></i>Изменить пароль</h5>
                                    </div>
                                    <div class="card-body">
                                        <form action="{{ route('profile.password') }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            
                                            <div class="mb-3">
                                                <label for="current_password" class="form-label">Текущий пароль *</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                                    <input type="password" class="form-control" id="current_password" 
                                                           name="current_password" required
                                                           placeholder="Введите текущий пароль" maxlength="50">
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="password" class="form-label">Новый пароль *</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="fas fa-key"></i></span>
                                                    <input type="password" class="form-control" id="password" 
                                                           name="password" required
                                                           placeholder="Введите новый пароль" maxlength="50">
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="password_confirmation" class="form-label">Подтвердите новый пароль *</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="fas fa-key"></i></span>
                                                    <input type="password" class="form-control" id="password_confirmation" 
                                                           name="password_confirmation" required
                                                           placeholder="Повторите новый пароль" maxlength="50">
                                                </div>
                                            </div>

                                            <div class="form-actions">
                                                <button type="submit" class="btn btn-warning btn-lg">
                                                    <i class="fas fa-shield-alt me-2"></i>Изменить пароль
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-lg-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Рекомендации по безопасности</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="security-tip">
                                            <i class="fas fa-lightbulb text-warning me-2"></i>
                                            <span>Используйте пароль длиной не менее 8 символов</span>
                                        </div>
                                        <div class="security-tip">
                                            <i class="fas fa-lightbulb text-warning me-2"></i>
                                            <span>Включите заглавные и строчные буквы</span>
                                        </div>
                                        <div class="security-tip">
                                            <i class="fas fa-lightbulb text-warning me-2"></i>
                                            <span>Добавьте цифры и специальные символы</span>
                                        </div>
                                        <div class="security-tip">
                                            <i class="fas fa-lightbulb text-warning me-2"></i>
                                            <span>Не используйте личную информацию</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Управление памятью -->
                    <div class="tab-pane fade" id="storage" role="tabpanel">
                        <div class="row">
                            <div class="col-lg-8">
                                <!-- Общая статистика памяти -->
                                <div class="card mb-4">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Использование памяти</h5>
                                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="syncStorage()">
                                            <i class="fas fa-sync-alt me-1"></i>Синхронизировать
                                        </button>
                                    </div>
                                    <div class="card-body">
                                        <div class="row mb-3">
                                            <div class="col-md-4">
                                                <div class="stat-card text-center">
                                                    <div class="stat-value" id="totalUsed">{{ $user->formatted_storage_usage }}</div>
                                                    <div class="stat-label">Использовано</div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="stat-card text-center">
                                                    <div class="stat-value">{{ $user->formatted_storage_limit }}</div>
                                                    <div class="stat-label">Лимит</div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="stat-card text-center">
                                                    <div class="stat-value" id="usagePercent">{{ number_format($user->storage_usage_percent, 1) }}%</div>
                                                    <div class="stat-label">Заполнено</div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="progress mb-3" style="height: 20px;">
                                            <div class="progress-bar {{ $user->storage_usage_percent > 80 ? 'bg-warning' : 'bg-primary' }}" 
                                                 role="progressbar" 
                                                 style="width: {{ min($user->storage_usage_percent, 100) }}%"
                                                 id="storageProgressBar">
                                                {{ number_format($user->storage_usage_percent, 1) }}%
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <p class="mb-1"><strong>Текущий план:</strong> {{ $user->plan_type_display }}</p>
                                                <p class="mb-0"><strong>Пространств:</strong> <span id="spacesCount">{{ $user->spaces()->count() }}</span></p>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="d-grid gap-2">
                                                    <a href="{{ route('memory.plans') }}" class="btn btn-success btn-sm">
                                                        <i class="fas fa-upgrade me-1"></i>Увеличить лимит
                                                    </a>
                                                    <button type="button" class="btn btn-info btn-sm" onclick="showSpacesDetails()">
                                                        <i class="fas fa-eye me-1"></i>Детали по пространствам
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Детали по пространствам -->
                                <div class="card mb-4" id="spacesDetailsCard" style="display: none;">
                                    <div class="card-header">
                                        <h6 class="mb-0"><i class="fas fa-layer-group me-2"></i>Использование памяти по пространствам</h6>
                                    </div>
                                    <div class="card-body" id="spacesDetailsContent">
                                        <div class="text-center">
                                            <div class="spinner-border" role="status">
                                                <span class="visually-hidden">Загрузка...</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Оптимизация памяти -->
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0"><i class="fas fa-broom me-2"></i>Оптимизация памяти</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle me-2"></i>
                                            Удаление архивированных задач поможет освободить место
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label for="cleanupDays" class="form-label">Удалить задачи старше (дней):</label>
                                                <select class="form-select" id="cleanupDays">
                                                    <option value="30">30 дней</option>
                                                    <option value="60">60 дней</option>
                                                    <option value="90">90 дней</option>
                                                    <option value="180">180 дней</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6 d-flex align-items-end">
                                                <button type="button" class="btn btn-warning" onclick="cleanupTasks()">
                                                    <i class="fas fa-trash-alt me-1"></i>Очистить
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-lg-4">
                                <!-- Рекомендации -->
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h6 class="mb-0"><i class="fas fa-lightbulb me-2"></i>Рекомендации</h6>
                                    </div>
                                    <div class="card-body" id="optimizationTips">
                                        <div class="text-center">
                                            <div class="spinner-border spinner-border-sm" role="status">
                                                <span class="visually-hidden">Загрузка...</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Статистика за период -->
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0"><i class="fas fa-chart-line me-2"></i>Активность за 30 дней</h6>
                                    </div>
                                    <div class="card-body" id="activityStats">
                                        <div class="text-center">
                                            <div class="spinner-border spinner-border-sm" role="status">
                                                <span class="visually-hidden">Загрузка...</span>
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
    </div>
</div>

<!-- Модальное окно создания организации (копируется из organization.blade.php) -->
<div class="modal fade" id="createOrganizationModal" tabindex="-1" aria-labelledby="createOrganizationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createOrganizationModalLabel">Создать новую организацию</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
            </div>
            <form action="{{ route('organizations.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="organizationName" class="form-label">Название организации</label>
                        <input type="text" class="form-control" id="organizationName" name="name" required maxlength="100">
                    </div>
                    <div class="mb-3">
                        <label for="organizationDescription" class="form-label">Описание (необязательно)</label>
                        <textarea class="form-control" id="organizationDescription" name="description" rows="3" maxlength="500"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn ">Создать организацию</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection


<style>

</style>


@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Инициализация табов Bootstrap
    var triggerTabList = [].slice.call(document.querySelectorAll('#profileTabs button'))
    triggerTabList.forEach(function (triggerEl) {
        var tabTrigger = new bootstrap.Tab(triggerEl)
        
        triggerEl.addEventListener('click', function (event) {
            event.preventDefault()
            tabTrigger.show()
        })
    })
    
    // Сохранение активного таба в localStorage
    const profileTabs = document.querySelectorAll('#profileTabs button');
    const savedTab = localStorage.getItem('activeProfileTab');
    
    if (savedTab) {
        const targetTab = document.querySelector(`#profileTabs button[data-bs-target="${savedTab}"]`);
        if (targetTab) {
            const tab = new bootstrap.Tab(targetTab);
            tab.show();
        }
    }
    
    profileTabs.forEach(tab => {
        tab.addEventListener('shown.bs.tab', function(e) {
            localStorage.setItem('activeProfileTab', e.target.getAttribute('data-bs-target'));
        });
    });
    
    // Предварительный просмотр загружаемого фото с анимацией
    const photoInput = document.getElementById('photo');
    if (photoInput) {
        photoInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Проверяем размер файла (максимум 2МБ)
                if (file.size > 2 * 1024 * 1024) {
                    alert('Размер файла не должен превышать 2МБ');
                    photoInput.value = '';
                    return;
                }
                
                // Проверяем тип файла
                if (!file.type.match('image.*')) {
                    alert('Пожалуйста, выберите изображение');
                    photoInput.value = '';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    const currentPhoto = document.querySelector('.current-photo-img');
                    const noPhoto = document.querySelector('.no-photo');
                    const profilePhoto = document.querySelector('.profile-photo');
                    const profileInitials = document.querySelector('.profile-initials');
                    
                    // Добавляем анимацию смены фото
                    const animatePhotoChange = (element, newSrc) => {
                        element.classList.add('photo-changing');
                        
                        setTimeout(() => {
                            if (element.tagName === 'IMG') {
                                element.src = newSrc;
                            } else {
                                element.outerHTML = `<img src="${newSrc}" alt="Предварительный просмотр" class="current-photo-img photo-changing">`;
                            }
                            
                            setTimeout(() => {
                                const updatedElement = document.querySelector('.photo-changing');
                                if (updatedElement) {
                                    updatedElement.classList.remove('photo-changing');
                                }
                            }, 500);
                        }, 250);
                    };
                    
                    // Обновляем фото в секции загрузки
                    if (currentPhoto) {
                        animatePhotoChange(currentPhoto, e.target.result);
                    } else if (noPhoto) {
                        animatePhotoChange(noPhoto, e.target.result);
                    }
                    
                    // Обновляем главную аватарку в заголовке
                    if (profilePhoto) {
                        animatePhotoChange(profilePhoto, e.target.result);
                    } else if (profileInitials) {
                        profileInitials.outerHTML = `<img src="${e.target.result}" alt="Фото профиля" class="profile-photo photo-changing">`;
                        setTimeout(() => {
                            const newPhoto = document.querySelector('.profile-photo');
                            if (newPhoto) {
                                newPhoto.classList.remove('photo-changing');
                            }
                        }, 500);
                    }
                };
                reader.readAsDataURL(file);
            }
        });
        
        // Добавляем drag & drop для загрузки фото
        const photoUploadSection = document.querySelector('.photo-upload-section');
        if (photoUploadSection) {
            photoUploadSection.addEventListener('dragover', function(e) {
                e.preventDefault();
                e.stopPropagation();
                this.style.borderColor = 'var(--sketch-primary)';
                this.style.backgroundColor = 'rgba(33, 150, 243, 0.05)';
            });
            
            photoUploadSection.addEventListener('dragleave', function(e) {
                e.preventDefault();
                e.stopPropagation();
                this.style.borderColor = 'var(--sketch-primary)';
                this.style.backgroundColor = '';
            });
            
            photoUploadSection.addEventListener('drop', function(e) {
                e.preventDefault();
                e.stopPropagation();
                this.style.borderColor = 'var(--sketch-primary)';
                this.style.backgroundColor = '';
                
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    photoInput.files = files;
                    // Триггерим событие change
                    const event = new Event('change', { bubbles: true });
                    photoInput.dispatchEvent(event);
                }
            });
        }
    }
    
    // Валидация пароля в реальном времени
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('password_confirmation');
    
    if (passwordInput && confirmPasswordInput) {
        function validatePassword() {
            const password = passwordInput.value;
            const confirmPassword = confirmPasswordInput.value;
            
            // Проверка совпадения паролей
            if (password && confirmPassword && password !== confirmPassword) {
                confirmPasswordInput.setCustomValidity('Пароли не совпадают');
                confirmPasswordInput.classList.add('is-invalid');
            } else {
                confirmPasswordInput.setCustomValidity('');
                confirmPasswordInput.classList.remove('is-invalid');
            }
        }
        
        passwordInput.addEventListener('input', validatePassword);
        confirmPasswordInput.addEventListener('input', validatePassword);
    }
    
    // Автосохранение формы (черновик)
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        const inputs = form.querySelectorAll('input[type="text"], input[type="email"], input[type="number"], select, textarea');
        
        inputs.forEach(input => {
            const storageKey = `profile_draft_${input.name}`;
            
            // Загрузка сохраненного значения
            const savedValue = localStorage.getItem(storageKey);
            if (savedValue && !input.value) {
                input.value = savedValue;
            }
            
            // Сохранение при изменении
            input.addEventListener('input', function() {
                localStorage.setItem(storageKey, input.value);
            });
        });
        
        // Очистка черновика при успешной отправке
        form.addEventListener('submit', function() {
            inputs.forEach(input => {
                localStorage.removeItem(`profile_draft_${input.name}`);
            });
        });
    });
    
    // Анимация карточек при скролле
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);
    
    document.querySelectorAll('.card').forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(card);
    });
    
    // Функции для управления памятью
    window.syncStorage = function() {
        const button = event.target.closest('button');
        const originalText = button.innerHTML;
        
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Синхронизация...';
        
        fetch('/api/storage/sync', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Обновляем значения на странице
                document.getElementById('totalUsed').textContent = formatStorageSize(data.new_usage);
                updateProgressBar(data.new_usage);
                
                showNotification(data.message, 'success');
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Произошла ошибка при синхронизации', 'error');
        })
        .finally(() => {
            button.disabled = false;
            button.innerHTML = originalText;
        });
    };
    
    window.showSpacesDetails = function() {
        const card = document.getElementById('spacesDetailsCard');
        const content = document.getElementById('spacesDetailsContent');
        
        if (card.style.display === 'none') {
            card.style.display = 'block';
            
            // Загружаем детали пространств
            fetch('/api/storage/spaces-stats')
            .then(response => response.json())
            .then(data => {
                let html = '';
                
                if (data.spaces_details && data.spaces_details.length > 0) {
                    data.spaces_details.forEach(spaceData => {
                        const space = spaceData.space;
                        const usage = spaceData.usage;
                        
                        html += `
                        <div class="space-detail-card mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0">${space.name}</h6>
                                <span class="badge bg-primary">${usage.total_size_mb} МБ</span>
                            </div>
                            <div class="row small text-muted">
                                <div class="col-6">Задач: ${usage.tasks_count}</div>
                                <div class="col-6">Колонок: ${usage.columns_count}</div>
                                <div class="col-6">Контент: ${usage.content_size_mb} МБ</div>
                                <div class="col-6">Файлы: ${usage.files_size_mb} МБ</div>
                            </div>
                        </div>`;
                    });
                } else {
                    html = '<p class="text-muted">У вас пока нет пространств</p>';
                }
                
                content.innerHTML = html;
            })
            .catch(error => {
                console.error('Error:', error);
                content.innerHTML = '<p class="text-danger">Ошибка загрузки данных</p>';
            });
        } else {
            card.style.display = 'none';
        }
    };
    
    window.cleanupTasks = function() {
        const days = document.getElementById('cleanupDays').value;
        
        if (!confirm(`Вы уверены, что хотите удалить все архивированные задачи старше ${days} дней? Это действие нельзя отменить.`)) {
            return;
        }
        
        const button = event.target;
        const originalText = button.innerHTML;
        
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Очистка...';
        
        fetch('/api/storage/cleanup', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ days: parseInt(days) })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(`${data.message}. Освобождено: ${data.freed_memory_mb} МБ`, 'success');
                
                // Обновляем статистику
                document.getElementById('totalUsed').textContent = formatStorageSize(data.new_usage_mb);
                updateProgressBar(data.new_usage_mb);
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Произошла ошибка при очистке', 'error');
        })
        .finally(() => {
            button.disabled = false;
            button.innerHTML = originalText;
        });
    };
    
    function formatStorageSize(sizeInMB) {
        if (sizeInMB >= 1024) {
            return (sizeInMB / 1024).toFixed(2) + ' ГБ';
        }
        return sizeInMB.toFixed(2) + ' МБ';
    }
    
    function updateProgressBar(usedMB) {
        const limitMB = {{ $user->total_storage_limit }};
        const percent = (usedMB / limitMB) * 100;
        const progressBar = document.getElementById('storageProgressBar');
        
        progressBar.style.width = Math.min(percent, 100) + '%';
        progressBar.textContent = percent.toFixed(1) + '%';
        
        // Меняем цвет в зависимости от заполненности
        progressBar.className = 'progress-bar ' + (percent > 80 ? 'bg-warning' : 'bg-primary');
        
        document.getElementById('usagePercent').textContent = percent.toFixed(1) + '%';
    }
    
    function showNotification(message, type = 'info') {
        const alertClass = type === 'success' ? 'alert-success' : 
                          type === 'error' ? 'alert-danger' : 
                          type === 'warning' ? 'alert-warning' : 'alert-info';
        
        const notification = document.createElement('div');
        notification.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(notification);
        
        // Автоматически убираем через 5 секунд
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 5000);
    }
    
    // Загружаем рекомендации и статистику при открытии вкладки
    document.getElementById('storage-tab').addEventListener('shown.bs.tab', function() {
        // Загружаем рекомендации
        fetch('/api/storage/tips')
        .then(response => response.json())
        .then(data => {
            let html = '';
            if (data.tips && data.tips.length > 0) {
                data.tips.forEach(tip => {
                    const iconClass = tip.type === 'warning' ? 'fa-exclamation-triangle text-warning' :
                                     tip.type === 'danger' ? 'fa-exclamation-circle text-danger' :
                                     'fa-info-circle text-info';
                    
                    html += `
                    <div class="d-flex align-items-start mb-3">
                        <i class="fas ${iconClass} me-2 mt-1"></i>
                        <span class="small">${tip.message}</span>
                    </div>`;
                });
            } else {
                html = '<p class="text-success small"><i class="fas fa-check-circle me-2"></i>Все в порядке! Память используется эффективно.</p>';
            }
            
            document.getElementById('optimizationTips').innerHTML = html;
        })
        .catch(error => {
            document.getElementById('optimizationTips').innerHTML = '<p class="text-danger small">Ошибка загрузки рекомендаций</p>';
        });
    });
});
</script>

<!-- Стили для новой вкладки -->
<style>
.stat-card {
    padding: 1rem;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    background: #f8f9fa;
}

.stat-value {
    font-size: 1.5rem;
    font-weight: bold;
    color: #2c3e50;
}

.stat-label {
    font-size: 0.875rem;
    color: #6c757d;
    margin-top: 0.25rem;
}

.space-detail-card {
    padding: 1rem;
    border: 1px solid #e9ecef;
    border-radius: 6px;
    background: #ffffff;
}

.space-detail-card h6 {
    color: #2c3e50;
}

#storageProgressBar {
    transition: all 0.3s ease;
}

.photo-changing {
    transition: opacity 0.5s ease;
    opacity: 0.7;
}
</style>
@endsection
