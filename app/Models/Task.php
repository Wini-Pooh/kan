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
    ];

    protected $casts = [
        'start_date' => 'date',
        'due_date' => 'date',
        'completed_at' => 'datetime',
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
     * Получение цвета приоритета
     */
    public function getPriorityColorAttribute()
    {
        return match($this->priority) {
            'high' => '#dc3545',
            'medium' => '#fd7e14',
            'low' => '#28a745',
            default => '#6c757d'
        };
    }

    /**
     * Получение названия приоритета
     */
    public function getPriorityLabelAttribute()
    {
        return match($this->priority) {
            'high' => 'Высокий',
            'medium' => 'Средний',
            'low' => 'Низкий',
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
}
