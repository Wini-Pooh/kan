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
}
