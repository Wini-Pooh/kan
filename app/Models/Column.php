<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Column extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'color',
        'position',
        'is_default',
        'is_hidden',
        'space_id',
        'created_by',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_hidden' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($column) {
            if (empty($column->slug)) {
                $column->slug = Str::slug($column->name);
            }
        });
    }

    /**
     * Пространство, к которому принадлежит колонка
     */
    public function space()
    {
        return $this->belongsTo(Space::class);
    }

    /**
     * Пользователь, создавший колонку
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Задачи в данной колонке
     */
    public function tasks()
    {
        return $this->hasMany(Task::class, 'column_id')->notArchived();
    }

    /**
     * Все задачи в данной колонке (включая архивированные)
     */
    public function allTasks()
    {
        return $this->hasMany(Task::class, 'column_id');
    }

    /**
     * Архивированные задачи в данной колонке
     */
    public function archivedTasks()
    {
        return $this->hasMany(Task::class, 'column_id')->archived();
    }

    /**
     * Scope для сортировки по позиции
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('position')->orderBy('created_at');
    }

    /**
     * Scope для фильтрации только видимых колонок
     */
    public function scopeVisible($query)
    {
        return $query->where('is_hidden', false);
    }
    
    /**
     * Scope для фильтрации скрытых колонок
     */
    public function scopeHidden($query)
    {
        return $query->where('is_hidden', true);
    }

    /**
     * Проверка, можно ли удалить колонку
     */
    public function canBeDeleted()
    {
        return !$this->is_default;
    }

    /**
     * Получение следующей позиции для новой колонки в пространстве
     */
    public static function getNextPosition($spaceId)
    {
        return static::where('space_id', $spaceId)->max('position') + 1;
    }
}
