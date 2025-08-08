@extends('layouts.app')

@section('content')
<div class="container-fluid p-0">
    <div class="row g-0">
        <!-- Боковая панель -->
        <div class="col-md-3 col-lg-2">
            <div class="sidebar sketch-border sketch-shadow" style="min-height: 100vh; position: fixed; width: inherit; background-color: var(--sketch-white);">
                <div class="p-3">
                    <h4 class="mb-4" style="text-transform: uppercase; letter-spacing: 2px; font-weight: bold;">Меню</h4>
                    
                    <!-- Организации пользователя -->
                    <div class="mb-4">
                        <h6 class="mb-3" style="text-transform: uppercase; letter-spacing: 1px; font-weight: bold; color: var(--sketch-dark-gray);">Комнаты</h6>
                        
                        <div class="list-group list-group-flush">
                            @forelse($userOrganizations as $org)
                                <div class="list-group-item border-0 px-0 mb-2 {{ $org->id === $organization->id ? 'active-organization' : '' }}">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center me-2" 
                                             style="width: 32px; height: 32px; background-color: {{ $org->id === $organization->id ? 'var(--sketch-primary)' : 'var(--sketch-black)' }} !important; color: var(--sketch-white) !important; font-size: 0.8rem; font-weight: bold;">
                                            {{ substr($org->name, 0, 2) }}
                                        </div>
                                        <div class="flex-grow-1">
                                            <a href="{{ route('organizations.show', $org) }}" class="text-decoration-none text-dark">
                                                <div style="font-weight: bold; font-size: 0.9rem; {{ $org->id === $organization->id ? 'color: var(--sketch-primary) !important;' : '' }}">{{ $org->name }}</div>
                                                <small style="color: var(--sketch-dark-gray) !important;">{{ ucfirst($org->pivot->role) }}</small>
                                            </a>
                                        </div>
                                    </div>
                                    
                                    @if($org->spaces->count() > 0)
                                        <div class="ms-4 mt-2">
                                            @foreach($org->spaces as $space)
                                                <div class="mb-1">
                                                    <a href="{{ route('spaces.show', [$org, $space]) }}" class="text-decoration-none text-dark d-flex align-items-center">
                                                        <i class="fas fa-cube me-2" style="font-size: 0.8rem;"></i>
                                                        <span style="font-size: 0.85rem;">{{ $space->name }}</span>
                                                    </a>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <div class="list-group-item border-0 px-0">
                                    <p class="text-muted mb-0">Нет доступных организаций</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                    
                    <!-- Разделитель -->
                    <hr style="border: var(--sketch-border-thin) !important; margin: 1.5rem 0;">
                    
                    <!-- Дополнительные пункты меню -->
                    <div class="list-group list-group-flush">
                        <a href="{{ route('home') }}" class="list-group-item list-group-item-action border-0 mb-1" 
                           style="border-radius: var(--sketch-radius-small) !important; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; color: var(--sketch-black) !important; text-decoration: none !important;"
                           onmouseover="this.style.backgroundColor='var(--sketch-light-gray)'; this.style.transform='translateX(5px)'"
                           onmouseout="this.style.backgroundColor='transparent'; this.style.transform='translateX(0)'">
                            <i class="fas fa-home me-2" style="width: 20px;"></i>
                            Главная
                        </a>
                        <a href="#" class="list-group-item list-group-item-action border-0 mb-1" 
                           style="border-radius: var(--sketch-radius-small) !important; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; color: var(--sketch-black) !important; text-decoration: none !important;"
                           onmouseover="this.style.backgroundColor='var(--sketch-light-gray)'; this.style.transform='translateX(5px)'"
                           onmouseout="this.style.backgroundColor='transparent'; this.style.transform='translateX(0)'"
                           data-bs-toggle="modal" data-bs-target="#createOrganizationModal">
                            <i class="fas fa-building me-2" style="width: 20px;"></i>
                            Создать комнату
                        </a>
                        <a href="#" class="list-group-item list-group-item-action border-0 mb-1" 
                           style="border-radius: var(--sketch-radius-small) !important; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; color: var(--sketch-black) !important; text-decoration: none !important;"
                           onmouseover="this.style.backgroundColor='var(--sketch-light-gray)'; this.style.transform='translateX(5px)'"
                           onmouseout="this.style.backgroundColor='transparent'; this.style.transform='translateX(0)'">
                            <i class="fas fa-user me-2" style="width: 20px;"></i>
                            Профиль
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Основной контент -->
        <div class="col-md-9 col-lg-10 offset-md-3 offset-lg-2">
            <div class="main-content p-4">
                @if (session('success'))
                    <div class="alert alert-success mb-4" role="alert">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger mb-4" role="alert">
                        {{ session('error') }}
                    </div>
                @endif

                <!-- Заголовок страницы -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 style="text-transform: uppercase; letter-spacing: 2px; font-weight: bold;">{{ $organization->name }}</h1>
                        <p class="text-muted mb-0">{{ $organization->description }}</p>
                    </div>
                    <div>
                        @if($organization->owner_id === Auth::id())
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createSpaceModal">
                                <i class="fas fa-plus me-2"></i>Создать пространство
                            </button>
                        @endif
                    </div>
                </div>

                <!-- Сетка карточек пространств -->
                <div class="row g-4">
                    <!-- Карточка создания нового пространства -->
                    @if($organization->owner_id === Auth::id())
                    <div class="col-lg-4 col-md-6">
                        <div class="card h-100 border-2 border-dashed" style="min-height: 260px; cursor: pointer; transition: all 0.3s ease;" 
                             data-bs-toggle="modal" data-bs-target="#createSpaceModal"
                             onmouseover="this.style.backgroundColor='#f8f9fa'" 
                             onmouseout="this.style.backgroundColor='white'">
                            <div class="card-body d-flex align-items-center justify-content-center">
                                <div class="text-center">
                                    <i class="fas fa-plus" style="font-size: 3rem; color: #6c757d; margin-bottom: 1rem;"></i>
                                    <h5 class="text-muted">Создать пространство</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Карточки пространств организации -->
                    @forelse($spaces as $space)
                    <div class="col-lg-4 col-md-6">
                        <div class="card h-100 position-relative space-card" style="min-height: 200px; transition: transform 0.3s ease; cursor: pointer;" 
                             data-space-url="{{ route('spaces.show', [$organization, $space]) }}"
                             onmouseover="this.style.transform='translateY(-5px)'" 
                             onmouseout="this.style.transform='translateY(0)'">
                            <div class="card-body d-flex flex-column">
                                <!-- Название пространства слева сверху -->
                                <div class="mb-auto d-flex justify-content-between">
                                    <h5 class="card-title fw-bold">{{ $space->name }}</h5>
                                    <div class="d-flex flex-column align-items-end">
                                        <span class="badge" style="background-color: var(--sketch-black) !important; color: var(--sketch-white) !important; font-size: 0.7rem;">
                                            {{ $space->members->count() }}
                                        </span>
                                    </div>
                                </div>
                                
                                @if($space->description)
                                    <p class="card-text text-muted mb-3">{{ Str::limit($space->description, 100) }}</p>
                                @endif
                                
                                <!-- Действия для пространства -->
                                <div class="align-self-end">
                                    @if($organization->owner_id === Auth::id())
                                        <!-- Меню действий для владельца организации -->
                                        <div class="custom-dropdown">
                                            <button class="custom-dropdown-toggle" type="button" onclick="toggleCustomDropdown({{ $space->id }})">
                                                <i class="fas fa-ellipsis-h"></i>
                                            </button>
                                            <div class="custom-dropdown-menu" id="customDropdown{{ $space->id }}">
                                                <div class="custom-dropdown-content">
                                                    <div class="custom-dropdown-header">Участники пространства</div>
                                                    <div class="mb-3">
                                                        @forelse($space->members->take(5) as $member)
                                                        <div class="member-item">
                                                            <div class="member-info">
                                                                <div class="member-avatar">
                                                                    {{ substr($member->name, 0, 1) }}
                                                                </div>
                                                                <div class="member-details">
                                                                    <div class="member-name">{{ $member->name }}</div>
                                                                    <div class="member-role">
                                                                        {{ ucfirst($member->pivot->access_level) }}
                                                                        @if($member->pivot->status === 'pending')
                                                                            <span class="badge bg-warning ms-1">Ожидает одобрения</span>
                                                                        @elseif($member->pivot->status === 'banned')
                                                                            <span class="badge bg-danger ms-1">Заблокирован</span>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            @if(($organization->owner_id === Auth::id()) || ($space->members()->where('user_id', Auth::id())->first() && $space->members()->where('user_id', Auth::id())->first()->pivot->role === 'admin'))
                                                                @php
                                                                    $isOwner = $organization->owner_id === Auth::id();
                                                                    $isAdmin = !$isOwner && $space->members()->where('user_id', Auth::id())->first() && $space->members()->where('user_id', Auth::id())->first()->pivot->role === 'admin';
                                                                @endphp
                                                                @if($member->id !== Auth::id())
                                                            <div class="member-actions">
                                                                @if($member->pivot->status === 'pending')
                                                                    <button class="member-action-btn text-success" onclick="approveMember({{ $space->id }}, {{ $member->id }})" title="Одобрить пользователя">
                                                                        <i class="fas fa-check"></i>
                                                                    </button>
                                                                    <button class="member-action-btn text-danger" onclick="banMember({{ $space->id }}, {{ $member->id }})" title="Заблокировать пользователя">
                                                                        <i class="fas fa-ban"></i>
                                                                    </button>
                                                                @else
                                                                    <button class="member-action-btn text-secondary" onclick="toggleUserAccess({{ $space->id }}, {{ $member->id }})" title="Изменить права доступа">
                                                                        <i class="fas fa-edit"></i>
                                                                    </button>
                                                                    <button class="member-action-btn text-danger" onclick="removeUser({{ $space->id }}, {{ $member->id }})" title="Удалить пользователя">
                                                                        <i class="fas fa-times"></i>
                                                                    </button>
                                                                @endif
                                                            </div>
                                                                @endif
                                                            @endif
                                                        </div>
                                                        @empty
                                                            <div class="text-muted text-center py-2" style="font-size: 0.8rem;">
                                                                Участников пока нет
                                                            </div>
                                                        @endforelse
                                                        
                                                        @if($space->members->count() > 5)
                                                            <div class="text-center mt-2">
                                                                <small class="text-muted">И еще {{ $space->members->count() - 5 }} участников...</small>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    
                                                    @if(($organization->owner_id === Auth::id()) || ($space->members()->where('user_id', Auth::id())->first() && $space->members()->where('user_id', Auth::id())->first()->pivot->role === 'admin'))
                                                    <div class="invite-section">
                                                        <label class="invite-label">Ссылка для приглашения:</label>
                                                        <div class="invite-input-group">
                                                            <input type="text" class="invite-input" id="inviteLink{{ $space->id }}" readonly placeholder="Ссылка будет создана автоматически">
                                                            <button class="invite-btn" type="button" onclick="generateAndCopyInviteLink({{ $space->id }})" id="copyBtn{{ $space->id }}">
                                                                <i class="fas fa-copy me-1"></i>Скопировать ссылку
                                                            </button>
                                                        </div>
                                                    </div>
                                                    @endif
                                                </div>
                                                <div class="custom-dropdown-divider"></div>
                                               
                                               
                                            </div>
                                        </div>
                                    @else
                                        <!-- Для обычных участников - карточка полностью кликабельна -->
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                        @if($organization->owner_id !== Auth::id())
                        <div class="col-12">
                            <div class="text-center py-5">
                                <i class="fas fa-inbox" style="font-size: 4rem; color: #6c757d; margin-bottom: 1rem;"></i>
                                <h5 class="text-muted">В этой организации пока нет доступных пространств</h5>
                                <p class="text-muted">Обратитесь к администратору для получения доступа к пространствам</p>
                            </div>
                        </div>
                        @endif
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно создания пространства -->
@if($organization->owner_id === Auth::id())
<div class="modal fade" id="createSpaceModal" tabindex="-1" aria-labelledby="createSpaceModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createSpaceModalLabel">Создать новое пространство</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
            </div>
            <form method="POST" action="{{ route('spaces.store') }}">
                @csrf
                <input type="hidden" name="organization_id" value="{{ $organization->id }}">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="space_name" class="form-label">Название пространства</label>
                        <input type="text" class="form-control" id="space_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="space_description" class="form-label">Описание (необязательно)</label>
                        <textarea class="form-control" id="space_description" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-primary">Создать пространство</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<!-- Модальное окно создания организации -->
<div class="modal fade" id="createOrganizationModal" tabindex="-1" aria-labelledby="createOrganizationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createOrganizationModalLabel">Создать новую организацию</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
            </div>
            <form method="POST" action="{{ route('organizations.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="organization_name" class="form-label">Название организации</label>
                        <input type="text" class="form-control" id="organization_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="organization_description" class="form-label">Описание (необязательно)</label>
                        <textarea class="form-control" id="organization_description" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-success">Создать комнату</button>
                </div>
            </form>
        </div>
    </div>
</div>



<script>
// Обработчик кликов по карточкам пространств
document.addEventListener('DOMContentLoaded', function() {
    // Добавляем обработчики кликов для карточек пространств
    const spaceCards = document.querySelectorAll('.space-card');
    spaceCards.forEach(function(card) {
        card.addEventListener('click', function(e) {
            // Проверяем, что клик был не по dropdown меню или его элементам
            if (!e.target.closest('.custom-dropdown') && 
                !e.target.closest('.custom-dropdown-toggle') && 
                !e.target.closest('.custom-dropdown-menu') &&
                !e.target.closest('.btn')) {
                const spaceUrl = this.getAttribute('data-space-url');
                if (spaceUrl) {
                    window.location.href = spaceUrl;
                }
            }
        });
    });
});

// Функция для управления самописным dropdown
function toggleCustomDropdown(spaceId) {
    const dropdown = document.getElementById(`customDropdown${spaceId}`);
    const allDropdowns = document.querySelectorAll('.custom-dropdown-menu');
    
    // Закрываем все другие dropdown
    allDropdowns.forEach(function(menu) {
        if (menu.id !== `customDropdown${spaceId}`) {
            menu.classList.remove('show');
        }
    });
    
    // Переключаем текущий dropdown
    dropdown.classList.toggle('show');
}

// Закрытие dropdown при клике вне его
document.addEventListener('click', function(event) {
    const target = event.target;
    const isDropdownButton = target.closest('.custom-dropdown-toggle');
    const isDropdownMenu = target.closest('.custom-dropdown-menu');
    
    if (!isDropdownButton && !isDropdownMenu) {
        document.querySelectorAll('.custom-dropdown-menu').forEach(function(menu) {
            menu.classList.remove('show');
        });
    }
});

// Предотвращаем закрытие dropdown при клике внутри меню
document.addEventListener('click', function(event) {
    if (event.target.closest('.custom-dropdown-menu')) {
        event.stopPropagation();
    }
});

// Предотвращаем всплытие события при клике на dropdown элементы
document.querySelectorAll('.custom-dropdown').forEach(function(dropdown) {
    dropdown.addEventListener('click', function(e) {
        e.stopPropagation();
    });
});

function editSpace(spaceId) {
    console.log('Редактировать пространство с ID:', spaceId);
    alert('Редактирование пространства #' + spaceId + ' - функция будет реализована позже');
}

function deleteSpace(spaceId) {
    console.log('Удалить пространство с ID:', spaceId);
    if (confirm('Вы уверены, что хотите удалить это пространство? Это действие необратимо.')) {
        alert('Удаление пространства #' + spaceId + ' - функция будет реализована позже');
        // Здесь будет AJAX запрос на удаление
    }
}

// Генерация и копирование ссылки приглашения
function generateAndCopyInviteLink(spaceId) {
    const copyBtn = document.getElementById(`copyBtn${spaceId}`);
    const inviteLinkInput = document.getElementById(`inviteLink${spaceId}`);
    
    copyBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Создание...';
    copyBtn.disabled = true;
    
    fetch(`/spaces/${spaceId}/invite`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.invite_url) {
            inviteLinkInput.value = data.invite_url;
            
            // Копируем ссылку в буфер обмена
            navigator.clipboard.writeText(data.invite_url).then(() => {
                copyBtn.innerHTML = '<i class="fas fa-check me-1"></i>Скопировано!';
                copyBtn.className = 'btn btn-success';
                
                setTimeout(() => {
                    copyBtn.innerHTML = '<i class="fas fa-copy me-1"></i>Скопировать ссылку';
                    copyBtn.className = 'btn btn-primary';
                    copyBtn.disabled = false;
                }, 2000);
            }).catch(() => {
                // Fallback для старых браузеров
                inviteLinkInput.select();
                document.execCommand('copy');
                copyBtn.innerHTML = '<i class="fas fa-check me-1"></i>Скопировано!';
                copyBtn.className = 'btn btn-success';
                
                setTimeout(() => {
                    copyBtn.innerHTML = '<i class="fas fa-copy me-1"></i>Скопировать ссылку';
                    copyBtn.className = 'btn btn-primary';
                    copyBtn.disabled = false;
                }, 2000);
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        copyBtn.innerHTML = '<i class="fas fa-exclamation-triangle me-1"></i>Ошибка';
        copyBtn.className = 'btn btn-danger';
        copyBtn.disabled = false;
        
        setTimeout(() => {
            copyBtn.innerHTML = '<i class="fas fa-copy me-1"></i>Скопировать ссылку';
            copyBtn.className = 'btn btn-primary';
        }, 3000);
    });
}

// Удаление пользователя из пространства
function removeUser(spaceId, userId) {
    if (confirm('Удалить пользователя из пространства?')) {
        fetch(`/spaces/${spaceId}/members/${userId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Ошибка при удалении пользователя');
        });
    }
}

// Изменение уровня доступа пользователя
function toggleUserAccess(spaceId, userId) {
    const accessOptions = [
        'viewer - Только просмотр',
        'editor - Просмотр и редактирование', 
        'admin - Полный доступ'
    ];
    
    let optionsText = 'Выберите уровень доступа для пользователя:\n\n';
    accessOptions.forEach((option, index) => {
        optionsText += (index + 1) + '. ' + option + '\n';
    });
    
    const choice = prompt(optionsText + '\nВведите номер (1-3):');
    
    if (choice && choice >= 1 && choice <= 3) {
        const accessLevels = ['viewer', 'editor', 'admin'];
        const selectedAccess = accessLevels[choice - 1];
        
        fetch(`/spaces/${spaceId}/members/${userId}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ 
                access_level: selectedAccess 
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Ошибка при изменении уровня доступа');
        });
    }
}

// Одобрение пользователя
function approveMember(spaceId, userId) {
    if (confirm('Одобрить участника и дать ему доступ к пространству?')) {
        fetch(`/spaces/${spaceId}/members/${userId}/approve`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Ошибка при одобрении пользователя');
        });
    }
}

// Блокировка пользователя
function banMember(spaceId, userId) {
    if (confirm('Заблокировать пользователя? Он не сможет получить доступ к пространству.')) {
        fetch(`/spaces/${spaceId}/members/${userId}/ban`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Ошибка при блокировке пользователя');
        });
    }
}
</script>

@endsection
