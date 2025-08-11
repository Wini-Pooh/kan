<div class="col-md-3 col-lg-2 sidebar-container">
    <div class="sidebar sketch-border sketch-shadow" style="height: 100vh; position: fixed; width: inherit; background-color: var(--sketch-white); overflow-y: auto;">
        <div class="p-3">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <button type="button" class="btn-close d-md-none" aria-label="Закрыть" onclick="toggleSidebar()"></button>
            </div>
            <div class="mb-4">
                <h6 class="mb-3" style="text-transform: uppercase; letter-spacing: 1px; font-weight: bold; color: var(--sketch-dark-gray);">Комнаты</h6>
                <div class="organizations-container">
                    <div class="list-group list-group-flush">
                        @forelse($userOrganizations as $org)
                            <div class="list-group-item border-0 px-0 mb-2">
                                <div class="d-flex align-items-center">
                                    <div class="org-avatar me-2">
                                        @if($org->owner && $org->owner->photo)
                                            <img src="{{ asset('storage/photos/' . $org->owner->photo) }}" 
                                                 alt="Аватар {{ $org->name }}" 
                                                 class="org-avatar-img">
                                        @else
                                            <div class="org-avatar-initials">
                                                {{ substr($org->name, 0, 2) }}
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex-grow-1">
                                        <a href="{{ route('organizations.show', $org) }}" class="text-decoration-none text-dark">
                                            <div style="font-weight: bold; font-size: 0.9rem;">{{ $org->name }}</div>
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
            </div>
            <hr style="border: var(--sketch-border-thin) !important; margin: 1.5rem 0;">
            <div class="list-group list-group-flush">
                <a href="{{ route('home') }}" class="list-group-item list-group-item-action border-0 mb-1 {{ request()->routeIs('home') ? 'active-organization' : '' }}" 
                   style="border-radius: var(--sketch-radius-small) !important; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; color: {{ request()->routeIs('home') ? 'var(--sketch-primary)' : 'var(--sketch-black)' }} !important; text-decoration: none !important; {{ request()->routeIs('home') ? 'background-color: var(--sketch-light-gray);' : '' }}"
                   onmouseover="this.style.backgroundColor='var(--sketch-light-gray)'; this.style.transform='translateX(5px)'"
                   onmouseout="this.style.backgroundColor='{{ request()->routeIs('home') ? 'var(--sketch-light-gray)' : 'transparent' }}'; this.style.transform='translateX(0)'">
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
                <a href="{{ route('profile.show') }}" class="list-group-item list-group-item-action border-0 mb-1 {{ request()->routeIs('profile.show') ? 'active-organization' : '' }}" 
                   style="border-radius: var(--sketch-radius-small) !important; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; color: {{ request()->routeIs('profile.show') ? 'var(--sketch-primary)' : 'var(--sketch-black)' }} !important; text-decoration: none !important; {{ request()->routeIs('profile.show') ? 'background-color: var(--sketch-light-gray);' : '' }}"
                   onmouseover="this.style.backgroundColor='var(--sketch-light-gray)'; this.style.transform='translateX(5px)'"
                   onmouseout="this.style.backgroundColor='{{ request()->routeIs('profile.show') ? 'var(--sketch-light-gray)' : 'transparent' }}'; this.style.transform='translateX(0)'">
                    <i class="fas fa-user me-2" style="width: 20px;"></i>
                    Профиль
                </a>
                <a href="{{ route('memory.index') }}" class="list-group-item list-group-item-action border-0 mb-1 {{ request()->routeIs('storage.*') ? 'active-organization' : '' }}" 
                   style="border-radius: var(--sketch-radius-small) !important; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; color: {{ request()->routeIs('storage.*') ? 'var(--sketch-primary)' : 'var(--sketch-black)' }} !important; text-decoration: none !important; {{ request()->routeIs('storage.*') ? 'background-color: var(--sketch-light-gray);' : '' }}"
                   onmouseover="this.style.backgroundColor='var(--sketch-light-gray)'; this.style.transform='translateX(5px)'"
                   onmouseout="this.style.backgroundColor='{{ request()->routeIs('storage.*') ? 'var(--sketch-light-gray)' : 'transparent' }}'; this.style.transform='translateX(0)'">
                    <i class="fas fa-hdd me-2" style="width: 20px;"></i>
                    Память
                    @if(Auth::user() && Auth::user()->storage_usage_percent > 80)
                        <span class="badge bg-{{ Auth::user()->storage_usage_percent > 95 ? 'danger' : 'warning' }} ms-2">
                            {{ round(Auth::user()->storage_usage_percent) }}%
                        </span>
                    @endif
                </a>
            </div>
            <hr style="border: var(--sketch-border-thin) !important; margin: 1.5rem 0;">
            @include('components.storage-widget')
        </div>
    </div>
</div>
<style>
.sidebar {
    scrollbar-width: thin;
    scrollbar-color: var(--sketch-light-gray) transparent;
}

.sidebar::-webkit-scrollbar {
    width: 6px;
}

.sidebar::-webkit-scrollbar-track {
    background: transparent;
}

.sidebar::-webkit-scrollbar-thumb {
    background-color: var(--sketch-light-gray);
    border-radius: 3px;
    transition: background-color 0.3s ease;
}

.sidebar::-webkit-scrollbar-thumb:hover {
    background-color: var(--sketch-dark-gray);
}

/* Стили для контейнера организаций */
.organizations-container {
    max-height: 300px;
    overflow-y: auto;
    overflow-x: hidden;
    scrollbar-width: thin;
    scrollbar-color: var(--sketch-light-gray) transparent;
    padding-right: 4px;
    margin-right: -4px;
}

.organizations-container::-webkit-scrollbar {
    width: 4px;
}

.organizations-container::-webkit-scrollbar-track {
    background: transparent;
}

.organizations-container::-webkit-scrollbar-thumb {
    background-color: var(--sketch-light-gray);
    border-radius: 2px;
    transition: background-color 0.3s ease;
}

.organizations-container::-webkit-scrollbar-thumb:hover {
    background-color: var(--sketch-dark-gray);
}

.org-avatar {
    width: 32px;
    height: 32px;
    flex-shrink: 0;
}

.org-avatar-img {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid var(--sketch-primary);
    transition: all 0.3s ease;
}

.org-avatar-img:hover {
    transform: scale(1.1);
    border-color: var(--sketch-secondary);
}

.org-avatar-initials {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    background-color: var(--sketch-black) !important;
    color: var(--sketch-white) !important;
    font-size: 0.8rem;
    font-weight: bold;
    display: flex;
    align-items: center;
    justify-content: center;
    text-transform: uppercase;
    transition: all 0.3s ease;
}

.org-avatar-initials:hover {
    background-color: var(--sketch-primary) !important;
    transform: scale(1.1);
}

.active-organization {
    background-color: var(--sketch-light-gray) !important;
    color: var(--sketch-primary) !important;
}

.list-group-item-action:hover {
    background-color: var(--sketch-light-gray) !important;
    transform: translateX(5px);
    transition: all 0.3s ease;
}
</style>
