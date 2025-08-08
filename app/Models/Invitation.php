<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Invitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'token',
        'type',
        'space_id',
        'organization_id',
        'invited_by',
        'invitee_phone',
        'invitee_email',
        'status',
        'expires_at',
        'accepted_by',
        'accepted_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'accepted_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($invitation) {
            if (empty($invitation->token)) {
                $invitation->token = Str::random(32);
            }
            if (empty($invitation->expires_at)) {
                $invitation->expires_at = now()->addDays(7);
            }
        });
    }

    public function space()
    {
        return $this->belongsTo(Space::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function inviter()
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function acceptedBy()
    {
        return $this->belongsTo(User::class, 'accepted_by');
    }

    public function getInviteLinkAttribute()
    {
        return url('/join/' . $this->token);
    }

    public function isExpired()
    {
        return $this->expires_at->isPast();
    }
}
