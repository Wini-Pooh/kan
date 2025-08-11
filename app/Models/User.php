<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'middle_name',
        'age',
        'gender',
        'photo',
        'company_name',
        'position',
        'phone',
        'email',
        'password',
        'storage_limit_mb',
        'storage_used_mb',
        'plan_type',
        'plan_expires_at',
        'additional_storage_mb',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'storage_used_mb' => 'decimal:2',
        'plan_expires_at' => 'datetime',
    ];

    /**
     * Find user by phone number for authentication
     */
    public function findForPassport($phone) {
        return $this->where('phone', $phone)->first();
    }

    /**
     * Organizations where user is member
     */
    public function organizations()
    {
        return $this->belongsToMany(Organization::class, 'organization_members')
                    ->withPivot('role')
                    ->withTimestamps();
    }

    /**
     * Organizations owned by user
     */
    public function ownedOrganizations()
    {
        return $this->hasMany(Organization::class, 'owner_id');
    }

    /**
     * Spaces where user is member
     */
    public function spaces()
    {
        return $this->belongsToMany(Space::class, 'space_members')
                    ->withPivot('role', 'access_level', 'status')
                    ->withTimestamps();
    }

    /**
     * Spaces created by user
     */
    public function createdSpaces()
    {
        return $this->hasMany(Space::class, 'created_by');
    }

    /**
     * Invitations sent by user
     */
    public function sentInvitations()
    {
        return $this->hasMany(Invitation::class, 'invited_by');
    }

    /**
     * Get the username field for authentication
     */
    public function username()
    {
        return 'phone';
    }

    /**
     * Подписки пользователя
     */
    public function subscriptions()
    {
        return $this->hasMany(UserSubscription::class);
    }

    /**
     * Активная подписка пользователя
     */
    public function activeSubscription()
    {
        return $this->hasOne(UserSubscription::class)
                    ->where('status', 'active')
                    ->where('expires_at', '>', now())
                    ->latest('expires_at');
    }

    /**
     * Логи использования памяти
     */
    public function storageUsageLogs()
    {
        return $this->hasMany(StorageUsageLog::class);
    }

    /**
     * Получить общий лимит памяти (базовый + дополнительный)
     */
    public function getTotalStorageLimitAttribute()
    {
        return $this->storage_limit_mb + $this->additional_storage_mb;
    }

    /**
     * Получить доступную память
     */
    public function getAvailableStorageAttribute()
    {
        return $this->total_storage_limit - $this->storage_used_mb;
    }

    /**
     * Получить процент использования памяти
     */
    public function getStorageUsagePercentAttribute()
    {
        if ($this->total_storage_limit == 0) {
            return 0;
        }
        
        return round(($this->storage_used_mb / $this->total_storage_limit) * 100, 2);
    }

    /**
     * Проверить, достаточно ли памяти для операции
     */
    public function hasEnoughStorage($requiredMb)
    {
        return $this->available_storage >= $requiredMb;
    }

    /**
     * Увеличить использование памяти
     */
    public function increaseStorageUsage($mb, $action, $entityType, $entityId = null, $description = null, $metadata = null)
    {
        $oldUsage = $this->storage_used_mb;
        $newUsage = $oldUsage + $mb;
        
        // Проверяем лимит
        if ($newUsage > $this->total_storage_limit) {
            throw new \Exception('Превышен лимит памяти. Доступно: ' . $this->available_storage . ' МБ, требуется: ' . $mb . ' МБ');
        }
        
        // Обновляем использование памяти
        $this->update(['storage_used_mb' => $newUsage]);
        
        // Логируем операцию
        $this->storageUsageLogs()->create([
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'storage_used_mb' => $mb,
            'storage_before_mb' => $oldUsage,
            'storage_after_mb' => $newUsage,
            'description' => $description,
            'metadata' => $metadata,
        ]);
        
        return $this;
    }

    /**
     * Уменьшить использование памяти
     */
    public function decreaseStorageUsage($mb, $action, $entityType, $entityId = null, $description = null, $metadata = null)
    {
        $oldUsage = $this->storage_used_mb;
        $newUsage = max(0, $oldUsage - $mb);
        
        // Обновляем использование памяти
        $this->update(['storage_used_mb' => $newUsage]);
        
        // Логируем операцию
        $this->storageUsageLogs()->create([
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'storage_used_mb' => -$mb,
            'storage_before_mb' => $oldUsage,
            'storage_after_mb' => $newUsage,
            'description' => $description,
            'metadata' => $metadata,
        ]);
        
        return $this;
    }

    /**
     * Получить форматированный размер использования памяти
     */
    public function getFormattedStorageUsageAttribute()
    {
        $usage = (float) $this->storage_used_mb;
        
        if ($usage >= 1024) {
            return round($usage / 1024, 2) . ' ГБ';
        }
        
        return round($usage, 2) . ' МБ';
    }

    /**
     * Получить форматированный лимит памяти
     */
    public function getFormattedStorageLimitAttribute()
    {
        if ($this->total_storage_limit >= 1024) {
            return round($this->total_storage_limit / 1024, 2) . ' ГБ';
        }
        
        return $this->total_storage_limit . ' МБ';
    }

    /**
     * Проверить, есть ли активная подписка
     */
    public function hasActiveSubscription()
    {
        return $this->activeSubscription()->exists();
    }

    /**
     * Получить тип плана пользователя
     */
    public function getPlanTypeDisplayAttribute()
    {
        $planTypes = [
            'free' => 'Бесплатный',
            'test' => 'Тестовый',
            'custom' => 'Настраиваемый',
        ];
        
        return $planTypes[$this->plan_type] ?? 'Неизвестный';
    }
}
