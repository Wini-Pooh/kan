<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Organization extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'avatar',
        'owner_id',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($organization) {
            if (empty($organization->slug)) {
                $organization->slug = Str::slug($organization->name);
            }
        });
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'organization_members')
                    ->withPivot('role')
                    ->withTimestamps();
    }

    public function spaces()
    {
        return $this->hasMany(Space::class);
    }

    public function getAvatarAttribute($value)
    {
        return $value ?: strtoupper(substr($this->name, 0, 2));
    }
}
