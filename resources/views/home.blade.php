@extends('layouts.app')

@section('content')
<div class="container-fluid p-0">
    <!-- Мобильная кнопка для переключения сайдбара -->
    <button class="sidebar-toggle d-md-none" type="button" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>
    
    <!-- Фон для затемнения при открытом сайдбаре -->
    <div class="sidebar-backdrop"></div>
    
    <div class="row g-0">
        @include('layouts.sidebar')
                 
        <!-- Основной контент -->
        <div class="col-md-9 col-lg-10 offset-md-3 offset-lg-2">
            <div class="main-content p-4">
                @if (session('status'))
                    <div class="alert alert-success mb-4" role="alert">
                        {{ session('status') }}
                    </div>
                @endif

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
                    <h1 style="text-transform: uppercase; letter-spacing: 2px; font-weight: bold;">Пространства</h1>
                    <div>
                        <button class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#createOrganizationModal">
                            <i class="fas fa-plus me-2"></i>Создать комнату
                        </button>
                        @if($ownedOrganizations->count() > 0)
                            <button class="btn " data-bs-toggle="modal" data-bs-target="#createSpaceModal">
                                <i class="fas fa-plus me-2"></i>Создать пространство
                            </button>
                        @endif
                    </div>
                </div>

                <!-- Сетка карточек пространств -->
                <div class="row g-4">
                    <!-- Карточка создания нового пространства -->
                    @if($ownedOrganizations->count() > 0)
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

                    <!-- Карточки пространств пользователя -->
                    @forelse($userSpaces as $space)
                    <div class="col-lg-4 col-md-6">
                        <div class="card h-100 position-relative space-card" style="min-height: 200px; transition: transform 0.3s ease; cursor: pointer;" 
                             data-space-url="{{ route('spaces.show', [$space->organization, $space]) }}"
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
                                
                                <!-- Меню с тремя точками справа внизу -->
                                <div class="align-self-end">
                                    <div class="dropdown">
                                        <button class="btn btn-link p-0 dropdown-toggle" type="button" id="dropdownMenuButton{{ $space->id }}" data-bs-toggle="dropdown" aria-expanded="false" style="border: none !important; box-shadow: none !important; background: none !important; outline: none !important;">
                                            <i class="fas fa-ellipsis-h" style="font-size: 1.2rem; color: #6c757d;"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="dropdownMenuButton{{ $space->id }}" style="min-width: 280px; background-color: white !important; border: 2px solid black !important; border-radius: 8px; z-index: 10000 !important;">
                                            <li class="px-3 py-2">
                                                <h6 class="dropdown-header px-0 mb-2" style="color: black !important; font-weight: bold;">Участники пространства</h6>
                                                <div class="mb-3">
                                                    @foreach($space->members->take(5) as $member)
                                                    <div class="d-flex align-items-center justify-content-between mb-2 p-2" style="border-radius: 8px; transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='#f8f9fa'" onmouseout="this.style.backgroundColor='transparent'">
                                                        <div class="d-flex align-items-center">
                                                            <div class="rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px; background-color: #007bff; color: white; font-size: 0.8rem; font-weight: bold;">
                                                                {{ substr($member->name, 0, 1) }}
                                                            </div>
                                                            <div>
                                                                <div style="font-size: 0.9rem; font-weight: 500;">{{ $member->name }}</div>
                                                                <div style="font-size: 0.7rem; color: #6c757d;">
                                                                    {{ ucfirst($member->pivot->access_level) }}
                                                                    @if($member->pivot->status === 'pending')
                                                                        <span class="badge bg-warning ms-1">Ожидает одобрения</span>
                                                                    @elseif($member->pivot->status === 'banned')
                                                                        <span class="badge bg-danger ms-1">Заблокирован</span>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                        @if($space->members()->where('user_id', Auth::id())->first()->pivot->role === 'admin' && $member->id !== Auth::id())
                                                        <div class="d-flex align-items-center">
                                                            @if($member->pivot->status === 'pending')
                                                                <a href="javascript:void(0)" onclick="approveMember({{ $space->id }}, {{ $member->id }})" class="text-success me-2" title="Одобрить пользователя" style="font-size: 0.8rem;">
                                                                    <i class="fas fa-check"></i>
                                                                </a>
                                                                <a href="javascript:void(0)" onclick="banMember({{ $space->id }}, {{ $member->id }})" class="text-danger" title="Заблокировать пользователя" style="font-size: 0.8rem;">
                                                                    <i class="fas fa-ban"></i>
                                                                </a>
                                                            @else
                                                                <a href="javascript:void(0)" onclick="toggleUserAccess({{ $space->id }}, {{ $member->id }})" class="text-secondary me-2" title="Изменить права доступа" style="font-size: 0.8rem;">
                                                                    <i class="fas fa-edit"></i>
                                                                </a>
                                                                <a href="javascript:void(0)" onclick="removeUser({{ $space->id }}, {{ $member->id }})" class="text-danger" title="Удалить пользователя" style="font-size: 0.8rem;">
                                                                    <i class="fas fa-times"></i>
                                                                </a>
                                                            @endif
                                                        </div>
                                                        @endif
                                                    </div>
                                                    @endforeach
                                                    
                                                    @if($space->members->count() > 5)
                                                        <div class="text-center mt-2">
                                                            <small class="text-muted">И еще {{ $space->members->count() - 5 }} участников...</small>
                                                        </div>
                                                    @endif
                                                </div>
                                                
                                                @if($space->members()->where('user_id', Auth::id())->first()->pivot->role === 'admin')
                                                <div class="border-top pt-2">
                                                    <div class="mb-2">
                                                        <label class="form-label" style="font-size: 0.8rem; font-weight: bold; color: black;">Ссылка для приглашения:</label>
                                                        <div class="input-group">
                                                            <input type="text" class="form-control" id="inviteLink{{ $space->id }}" readonly style="font-size: 0.8rem;" placeholder="Ссылка будет создана автоматически">
                                                            <button class="btn " type="button" onclick="generateAndCopyInviteLink({{ $space->id }})" style="font-size: 0.8rem;" id="copyBtn{{ $space->id }}">
                                                                <i class="fas fa-copy me-1"></i>Скопировать ссылку
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endif
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                        <div class="col-12">
                            <div class="text-center py-5">
                                <i class="fas fa-cube" style="font-size: 4rem; color: #6c757d; margin-bottom: 1rem;"></i>
                                <h5 class="text-muted">У вас пока нет пространств</h5>
                                <p class="text-muted">
                                    @if($ownedOrganizations->count() > 0)
                                        Создайте свое первое пространство для работы с командой
                                    @else
                                        Создайте организацию, чтобы начать создавать пространства
                                    @endif
                                </p>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно создания пространства -->
@if($ownedOrganizations->count() > 0)
<div class="modal fade" id="createSpaceModal" tabindex="-1" aria-labelledby="createSpaceModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('spaces.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="createSpaceModalLabel">Создать новое пространство</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="spaceName" class="form-label">Название пространства</label>
                        <input type="text" class="form-control" id="spaceName" name="name" required maxlength="100">
                    </div>
                    <div class="mb-3">
                        <label for="spaceDescription" class="form-label">Описание (необязательно)</label>
                        <textarea class="form-control" id="spaceDescription" name="description" rows="3" maxlength="500"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="organizationId" class="form-label">Организация</label>
                        <select class="form-select" id="organizationId" name="organization_id" required>
                            <option value="">Выберите вашу организацию</option>
                            @foreach($ownedOrganizations as $organization)
                                <option value="{{ $organization->id }}">{{ $organization->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn ">Создать пространство</button>
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
            <form method="POST" action="{{ route('organizations.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="createOrganizationModalLabel">Создать новую организацию</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
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
                    <button type="submit" class="btn btn-success">Создать комнату</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* Стили для dropdown */
.dropdown-menu {
    z-index: 10000 !important;
    background-color: white !important;
    border: 2px solid black !important;
    border-radius: 8px !important;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    display: none !important;
}

.dropdown-menu.show {
    z-index: 10000 !important;
    background-color: white !important;
    border: 2px solid black !important;
    display: block !important;
    position: absolute !important;
    top: 100% !important;
    right: 0 !important;
    left: auto !important;
}

/* Убеждаемся что родительский элемент имеет правильный position */
.dropdown {
    position: relative !important;
}

/* Карточка должна иметь overflow visible */
.card {
    overflow: visible !important;
}

/* Hover эффекты для кнопки dropdown */
.dropdown button:hover {
    background-color: rgba(0, 0, 0, 0.05) !important;
    border-radius: 4px;
}

/* Дополнительные стили для видимости */
.dropdown-toggle::after {
    display: none !important;
}

/* Убираем любые ограничения по overflow */
.col-lg-4, .col-md-6 {
    overflow: visible !important;
}

.row {
    overflow: visible !important;
}

.main-content {
    overflow: visible !important;
}

/* Стили для ссылок действий */
.action-link {
    transition: opacity 0.2s ease, transform 0.1s ease;
    text-decoration: none !important;
}

.action-link:hover {
    opacity: 0.7;
    transform: scale(1.1);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Добавляем обработчики кликов для карточек пространств
    const spaceCards = document.querySelectorAll('.space-card');
    spaceCards.forEach(function(card) {
        card.addEventListener('click', function(e) {
            // Проверяем, что клик был не по dropdown меню или его элементам
            if (!e.target.closest('.dropdown') && 
                !e.target.closest('.dropdown-toggle') && 
                !e.target.closest('.dropdown-menu') &&
                !e.target.closest('.btn')) {
                const spaceUrl = this.getAttribute('data-space-url');
                if (spaceUrl) {
                    window.location.href = spaceUrl;
                }
            }
        });
    });

    // Проверяем что Bootstrap загружен
    if (typeof bootstrap === 'undefined') {
        console.error('Bootstrap не загружен!');
        return;
    }
    
    // Инициализируем все dropdown элементы с настройками
    var dropdownElementList = [].slice.call(document.querySelectorAll('[data-bs-toggle="dropdown"]'));
    var dropdownList = dropdownElementList.map(function (dropdownToggleEl) {
        return new bootstrap.Dropdown(dropdownToggleEl, {
            boundary: 'viewport',
            display: 'dynamic',
            offset: [0, 2],
            popperConfig: {
                placement: 'bottom-end',
                modifiers: [
                    {
                        name: 'preventOverflow',
                        options: {
                            boundary: 'viewport',
                        },
                    },
                    {
                        name: 'flip',
                        options: {
                            fallbackPlacements: ['bottom-start', 'top-end', 'top-start'],
                        },
                    },
                ]
            }
        });
    });
    
    console.log('Dropdown элементы инициализированы:', dropdownList.length);
    
    // Добавляем обработчики событий для отладки
    dropdownElementList.forEach(function(element, index) {
        element.addEventListener('show.bs.dropdown', function(e) {
            console.log('Dropdown показывается:', index);
            // Убеждаемся что родительские элементы имеют overflow: visible
            let parent = element.closest('.card');
            if (parent) {
                parent.style.overflow = 'visible';
            }
        });
        
        element.addEventListener('shown.bs.dropdown', function(e) {
            console.log('Dropdown показан:', index);
            const dropdownMenu = element.nextElementSibling;
            if (dropdownMenu) {
                dropdownMenu.style.display = 'block';
                dropdownMenu.style.position = 'absolute';
                dropdownMenu.style.zIndex = '10000';
                console.log('Dropdown menu стили применены');
            }
        });
        
        element.addEventListener('hide.bs.dropdown', function() {
            console.log('Dropdown скрывается:', index);
        });
        
        // Добавляем обработчик клика для принудительного переключения
        element.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Клик по dropdown кнопке');
            
            const dropdownMenu = this.nextElementSibling;
            if (dropdownMenu) {
                const isShown = dropdownMenu.classList.contains('show');
                
                // Закрываем все остальные dropdown
                document.querySelectorAll('.dropdown-menu.show').forEach(function(menu) {
                    menu.classList.remove('show');
                });
                
                if (!isShown) {
                    dropdownMenu.classList.add('show');
                    dropdownMenu.style.display = 'block';
                    dropdownMenu.style.position = 'absolute';
                    dropdownMenu.style.top = '100%';
                    dropdownMenu.style.right = '0';
                    dropdownMenu.style.left = 'auto';
                    dropdownMenu.style.zIndex = '10000';
                    console.log('Dropdown показан принудительно');
                }
            }
        });
    });
    
    // Закрытие dropdown при клике вне его
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.dropdown')) {
            document.querySelectorAll('.dropdown-menu.show').forEach(function(menu) {
                menu.classList.remove('show');
                menu.style.display = 'none';
            });
        }
    });
    
    // Предотвращаем всплытие события при клике на dropdown элементы
    document.querySelectorAll('.dropdown').forEach(function(dropdown) {
        dropdown.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    });
});

// Генерация ссылки приглашения и копирование
function generateAndCopyInviteLink(spaceId) {
    const btn = document.getElementById(`copyBtn${spaceId}`);
    const input = document.getElementById(`inviteLink${spaceId}`);
    
    // Показываем состояние загрузки
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Создание ссылки...';
    btn.disabled = true;
    
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
            input.value = data.invite_url;
            
            // Копируем в буфер обмена с проверкой поддержки
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(data.invite_url).then(function() {
                    // Показываем успешное копирование
                    btn.innerHTML = '<i class="fas fa-check me-1"></i>Скопировано!';
                    btn.classList.remove('');
                    btn.classList.add('btn-success');
                    
                    setTimeout(() => {
                        btn.innerHTML = '<i class="fas fa-copy me-1"></i>Скопировать ссылку';
                        btn.classList.remove('btn-success');
                        btn.classList.add('');
                        btn.disabled = false;
                    }, 3000);
                }).catch(function() {
                    // Если копирование не удалось, используем fallback
                    copyToClipboardFallback(data.invite_url, btn);
                });
            } else {
                // Fallback для старых браузеров
                copyToClipboardFallback(data.invite_url, btn);
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        btn.innerHTML = '<i class="fas fa-copy me-1"></i>Скопировать ссылку';
        btn.disabled = false;
        alert('Ошибка при создании ссылки приглашения');
    });
}

// Генерация ссылки приглашения (оставляем для совместимости)
function generateInviteLink(spaceId) {
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
            document.getElementById(`inviteLink${spaceId}`).value = data.invite_url;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Ошибка при создании ссылки приглашения');
    });
}

// Fallback функция для копирования в буфер обмена
function copyToClipboardFallback(text, btn) {
    // Создаем временный input элемент
    const tempInput = document.createElement('input');
    tempInput.value = text;
    document.body.appendChild(tempInput);
    tempInput.select();
    tempInput.setSelectionRange(0, 99999); // Для мобильных устройств

    try {
        // Пытаемся использовать устаревший execCommand
        const successful = document.execCommand('copy');
        if (successful) {
            if (btn) {
                btn.innerHTML = '<i class="fas fa-check me-1"></i>Скопировано!';
                btn.classList.remove('');
                btn.classList.add('btn-success');
                
                setTimeout(() => {
                    btn.innerHTML = '<i class="fas fa-copy me-1"></i>Скопировать ссылку';
                    btn.classList.remove('btn-success');
                    btn.classList.add('');
                    btn.disabled = false;
                }, 3000);
            }
        } else {
            throw new Error('execCommand failed');
        }
    } catch (err) {
        // Если ничего не работает, показываем alert
        if (btn) {
            btn.innerHTML = '<i class="fas fa-copy me-1"></i>Скопировать ссылку';
            btn.disabled = false;
        }
        alert('Не удалось скопировать автоматически. Ссылка: ' + text);
    } finally {
        document.body.removeChild(tempInput);
    }
}

// Копирование в буфер обмена
function copyToClipboard(elementId) {
    const element = document.getElementById(elementId);
    if (element.value) {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(element.value).then(function() {
                // Показываем уведомление об успешном копировании
                const btn = element.nextElementSibling;
                const originalText = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-check"></i>';
                setTimeout(() => {
                    btn.innerHTML = originalText;
                }, 2000);
            }).catch(function() {
                // Используем fallback
                copyToClipboardFallback(element.value, element.nextElementSibling);
            });
        } else {
            // Используем fallback для старых браузеров
            copyToClipboardFallback(element.value, element.nextElementSibling);
        }
    } else {
        alert('Сначала создайте ссылку для приглашения');
    }
}

// Удаление пользователя
function removeUser(spaceId, userId) {
    if (confirm('Вы уверены, что хотите удалить этого пользователя из пространства?')) {
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
