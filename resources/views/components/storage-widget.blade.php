@if(Auth::check())
    @php
        $user = Auth::user();
        $usagePercent = $user->storage_usage_percent ?? 0;
        $isWarning = $usagePercent > 80;
        $isCritical = $usagePercent > 95;
    @endphp
    
    <div class="storage-widget mb-3">
        <div class="card border-{{ $isCritical ? 'danger' : ($isWarning ? 'warning' : 'secondary') }}">
            <div class="card-header bg-{{ $isCritical ? 'danger' : ($isWarning ? 'warning' : 'light') }} text-{{ $isCritical || $isWarning ? 'white' : 'dark' }}">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="fw-bold">
                        <i class="fas fa-hdd me-1"></i>
                        Память
                    </span>
                    <span class="badge bg-{{ $isCritical ? 'light text-danger' : ($isWarning ? 'light text-warning' : 'secondary') }}">
                        {{ round($usagePercent) }}%
                    </span>
                </div>
            </div>
            <div class="card-body p-2">
                <div class="progress mb-2" style="height: 6px;">
                    <div class="progress-bar bg-{{ $isCritical ? 'danger' : ($isWarning ? 'warning' : 'primary') }}" 
                         role="progressbar" 
                         style="width: {{ $usagePercent }}%">
                    </div>
                </div>
                <div class="d-flex justify-content-between">
                    <small class="text-muted">
                        {{ $user->formatted_storage_usage ?? '0 МБ' }}
                    </small>
                    <small class="text-muted">
                        {{ $user->formatted_storage_limit ?? '50 МБ' }}
                    </small>
                </div>
                @if($isCritical)
                    <div class="mt-2">
                        <small class="text-danger">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            Критически мало места!
                        </small>
                        <div class="mt-1">
                            <a href="{{ route('memory.plans') }}" class="btn btn-danger btn-sm">
                                Увеличить лимит
                            </a>
                        </div>
                    </div>
                @elseif($isWarning)
                    <div class="mt-2">
                        <small class="text-warning">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            Заканчивается место
                        </small>
                        <div class="mt-1">
                            <a href="{{ route('memory.index') }}" class="btn btn-warning btn-sm">
                                Управление
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <style>
    .storage-widget .card {
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
    }
    
    .storage-widget .card:hover {
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        transform: translateY(-1px);
    }
    
    .storage-widget .progress {
        background-color: rgba(0,0,0,0.1);
    }
    
    .storage-widget .btn-sm {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }
    </style>
@endif
