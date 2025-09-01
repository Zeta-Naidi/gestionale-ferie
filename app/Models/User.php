<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'manager_id',
        'is_manager',
        'role',
        'organizational_unit_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_manager' => 'boolean',
        ];
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function managedLeaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class, 'manager_id');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function subordinates(): HasMany
    {
        return $this->hasMany(User::class, 'manager_id');
    }

    public function organizationalUnit(): BelongsTo
    {
        return $this->belongsTo(OrganizationalUnit::class, 'organizational_unit_id');
    }

    // Role constants
    const ROLE_ADMIN_IT = 'ADMIN_IT';
    const ROLE_HR = 'HR';
    const ROLE_MANAGER = 'MANAGER';
    const ROLE_EMPLOYEE = 'EMPLOYEE';

    // Role check methods
    public function isAdminIT(): bool
    {
        return $this->role === self::ROLE_ADMIN_IT;
    }

    public function isHR(): bool
    {
        return $this->role === self::ROLE_HR;
    }

    public function isManager(): bool
    {
        return $this->role === self::ROLE_MANAGER;
    }

    public function isEmployee(): bool
    {
        return $this->role === self::ROLE_EMPLOYEE;
    }

    public function canApproveLeaveRequests(): bool
    {
        return $this->isHR() || $this->isManager();
    }

    public function canCreateLeaveRequests(): bool
    {
        return !$this->isAdminIT();
    }
}
