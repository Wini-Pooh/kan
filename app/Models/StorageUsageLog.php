<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StorageUsageLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'entity_type',
        'entity_id',
        'storage_used_mb',
        'storage_before_mb',
        'storage_after_mb',
        'description',
        'metadata',
    ];

    protected $casts = [
        'storage_used_mb' => 'decimal:2',
        'storage_before_mb' => 'decimal:2',
        'storage_after_mb' => 'decimal:2',
        'metadata' => 'array',
    ];

    /**
     * Пользователь
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Логи по пользователю
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Логи по типу сущности
     */
    public function scopeForEntityType($query, $entityType)
    {
        return $query->where('entity_type', $entityType);
    }

    /**
     * Логи за период
     */
    public function scopeForPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Получить общее использование памяти за период
     */
    public static function getTotalUsageForUser($userId, $startDate = null, $endDate = null)
    {
        $query = static::forUser($userId);
        
        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }
        
        return $query->sum('storage_used_mb');
    }

    /**
     * Получить использование памяти по типам сущностей
     */
    public static function getUsageByEntityType($userId, $startDate = null, $endDate = null)
    {
        $query = static::forUser($userId)
            ->selectRaw('entity_type, SUM(storage_used_mb) as total_usage')
            ->groupBy('entity_type');
            
        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }
        
        return $query->get();
    }
}
