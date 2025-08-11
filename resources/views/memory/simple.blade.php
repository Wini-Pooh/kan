@extends('layouts.app')

@section('content')

<div class="container-fluid">
         
    <div class="row ">  @include('layouts.sidebar')
        <div class="col-md-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Управление памятью</h4>
                    <div class="badge badge-info">{{ ucfirst($user->plan_type ?? 'free') }}</div>
                </div>
                <div class="card-body">
                    <!-- Статистика использования -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card border-primary">
                                <div class="card-body text-center">
                                    <h5 class="card-title text-primary">Использовано</h5>
                                    <h4>{{ $user->storage_used_mb ?? 0 }} МБ</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-success">
                                <div class="card-body text-center">
                                    <h5 class="card-title text-success">Лимит</h5>
                                    <h4>{{ ($user->storage_limit_mb ?? 50) + ($user->additional_storage_mb ?? 0) }} МБ</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-info">
                                <div class="card-body text-center">
                                    <h5 class="card-title text-info">Доступно</h5>
                                    @php
                                        $total = ($user->storage_limit_mb ?? 50) + ($user->additional_storage_mb ?? 0);
                                        $used = $user->storage_used_mb ?? 0;
                                        $available = $total - $used;
                                    @endphp
                                    <h4>{{ $available }} МБ</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-warning">
                                <div class="card-body text-center">
                                    <h5 class="card-title text-warning">Использование</h5>
                                    @php
                                        $percent = $total > 0 ? round(($used / $total) * 100, 1) : 0;
                                    @endphp
                                    <h4>{{ $percent }}%</h4>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Прогресс бар -->
                    <div class="mb-4">
                        <label class="form-label"><strong>Заполнение памяти:</strong></label>
                        <div class="progress" style="height: 25px;">
                            @php
                                $barClass = $percent < 50 ? 'bg-success' : ($percent < 80 ? 'bg-warning' : 'bg-danger');
                            @endphp
                            <div class="progress-bar {{ $barClass }}" role="progressbar" 
                                 style="width: {{ $percent }}%" 
                                 aria-valuenow="{{ $percent }}" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                                {{ $percent }}%
                            </div>
                        </div>
                    </div>

                    <!-- Информация о плане -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">Текущий план</h6>
                                    <p class="card-text">
                                        <strong>Тип:</strong> {{ ucfirst($user->plan_type ?? 'free') }}<br>
                                        <strong>Базовая память:</strong> {{ $user->storage_limit_mb ?? 50 }} МБ<br>
                                        <strong>Дополнительная память:</strong> {{ $user->additional_storage_mb ?? 0 }} МБ<br>
                                        <strong>Общий лимит:</strong> {{ $total }} МБ
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">Действия</h6>
                                    <div class="d-grid gap-2">
                                        <a href="{{ route('memory.plans') }}" class="btn btn-primary">
                                            Изменить план
                                        </a>
                                        <a href="{{ route('memory.demo') }}" class="btn btn-success">
                                            Демонстрация системы
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($percent > 80)
                    <div class="alert alert-warning mt-4">
                        <strong>Предупреждение!</strong> Вы использовали более 80% доступной памяти. 
                        Рекомендуем <a href="{{ route('memory.plans') }}">обновить план</a> или очистить ненужные файлы.
                    </div>
                    @endif

                    @if($percent >= 100)
                    <div class="alert alert-danger mt-4">
                        <strong>Лимит исчерпан!</strong> Вы не можете загружать новые файлы. 
                        <a href="{{ route('memory.plans') }}">Обновите план</a> для продолжения работы.
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
