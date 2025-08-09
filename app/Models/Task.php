<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'content',
        'assignee',
        'assignee_id',
        'assigned_to',
        'start_date',
        'due_date',
        'estimated_time',
        'priority',
        'status',
        'position',
        'space_id',
        'column_id',
        'created_by',
        'completed_at',
        'archived_at',
        'archived_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'due_date' => 'date',
        'completed_at' => 'datetime',
        'archived_at' => 'datetime',
    ];

    /**
     * Пространство, к которому принадлежит задача
     */
    public function space()
    {
        return $this->belongsTo(Space::class);
    }

    /**
     * Пользователь, создавший задачу
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Пользователь, назначенный исполнителем задачи
     */
    public function assignee()
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    /**
     * Пользователь, назначенный исполнителем задачи (новое поле)
     */
    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Пользователь, архивировавший задачу
     */
    public function archiver()
    {
        return $this->belongsTo(User::class, 'archived_by');
    }

    /**
     * Колонка, в которой находится задача
     */
    public function column()
    {
        return $this->belongsTo(Column::class);
    }

    /**
     * Проверка, завершена ли задача
     */
    public function isCompleted()
    {
        return $this->status === 'done';
    }

    /**
     * Проверка, архивирована ли задача
     */
    public function isArchived()
    {
        return !is_null($this->archived_at);
    }

    /**
     * Получение цвета приоритета
     */
    public function getPriorityColorAttribute()
    {
        return match($this->priority) {
            'low' => '#28a745',
            'medium' => '#fd7e14', 
            'high' => '#dc3545',
            'urgent' => '#ff6b35',
            'critical' => '#8b0000',
            'blocked' => '#6c757d',
            default => '#6c757d'
        };
    }

    /**
     * Получение названия приоритета
     */
    public function getPriorityLabelAttribute()
    {
        return match($this->priority) {
            'low' => 'Низкий',
            'medium' => 'Средний',
            'high' => 'Высокий',
            'urgent' => 'Срочный',
            'critical' => 'Критический',
            'blocked' => 'Заблокирован',
            default => 'Не указан'
        };
    }

    /**
     * Получение названия статуса
     */
    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'todo' => 'К выполнению',
            'progress' => 'В процессе',
            'done' => 'Выполнено',
            default => 'Неизвестно'
        };
    }

    /**
     * Scope для фильтрации по статусу
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope для сортировки по позиции
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('position')->orderBy('created_at', 'desc');
    }

    /**
     * Scope для фильтрации архивированных задач
     */
    public function scopeArchived($query)
    {
        return $query->whereNotNull('archived_at');
    }

    /**
     * Scope для фильтрации неархивированных задач
     */
    public function scopeNotArchived($query)
    {
        return $query->whereNull('archived_at');
    }
}
