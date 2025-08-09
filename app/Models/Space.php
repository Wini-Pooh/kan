<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Space extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'organization_id',
        'created_by',
        'visibility',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($space) {
            if (empty($space->slug)) {
                $space->slug = Str::slug($space->name);
            }
        });
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'space_members')
                    ->withPivot('role', 'access_level', 'status')
                    ->withTimestamps();
    }

    public function activeMembers()
    {
        return $this->belongsToMany(User::class, 'space_members')
                    ->withPivot('role', 'access_level', 'status')
                    ->wherePivot('status', 'active')
                    ->withTimestamps();
    }

    public function pendingMembers()
    {
        return $this->belongsToMany(User::class, 'space_members')
                    ->withPivot('role', 'access_level', 'status')
                    ->wherePivot('status', 'pending')
                    ->withTimestamps();
    }

    public function invitations()
    {
        return $this->hasMany(Invitation::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function activeTasks()
    {
        return $this->hasMany(Task::class)->notArchived();
    }

    public function archivedTasks()
    {
        return $this->hasMany(Task::class)->archived();
    }

    public function columns()
    {
        return $this->hasMany(Column::class);
    }

    public function getMembersCountAttribute()
    {
        return $this->members()->count();
    }

    public function getInviteLinkAttribute()
    {
        return url('/join/' . $this->slug);
    }
}
