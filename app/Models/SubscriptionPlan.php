<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'storage_mb',
        'price',
        'currency',
        'duration_days',
        'is_active',
        'is_recurring',
        'features',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'is_recurring' => 'boolean',
        'features' => 'array',
    ];

    /**
     * Получить активные планы
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Получить план по имени
     */
    public function scopeByName($query, $name)
    {
        return $query->where('name', $name);
    }

    /**
     * Подписки пользователей на этот план
     */
    public function subscriptions()
    {
        return $this->hasMany(UserSubscription::class);
    }

    /**
     * Получить форматированную цену
     */
    public function getFormattedPriceAttribute()
    {
        return number_format($this->price, 2) . ' ' . $this->currency;
    }

    /**
     * Получить размер хранилища в ГБ
     */
    public function getStorageGbAttribute()
    {
        return round($this->storage_mb / 1024, 2);
    }
}
