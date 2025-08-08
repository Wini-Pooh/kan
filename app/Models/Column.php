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
        'space_id',
        'created_by',
    ];

    protected $casts = [
        'is_default' => 'boolean',
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
        return $this->hasMany(Task::class, 'column_id');
    }

    /**
     * Scope для сортировки по позиции
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('position')->orderBy('created_at');
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
