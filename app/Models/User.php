<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\Role;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'phone', 'password', 'role', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * Mirrors the DB column default. Without this, a freshly-created model
     * instance has role=null in memory until reloaded, even though the DB
     * row itself gets 'customer' — any code reading ->role right after
     * create() (e.g. the post-registration redirect) would see null.
     */
    protected $attributes = [
        'role' => 'customer',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => Role::class,
            'is_active' => 'boolean',
        ];
    }

    public function customerProfile(): HasOne
    {
        return $this->hasOne(CustomerProfile::class);
    }

    public function installationJobsAsCustomer(): HasMany
    {
        return $this->hasMany(InstallationJob::class, 'customer_id');
    }

    public function installationJobsAsStaff(): HasMany
    {
        return $this->hasMany(InstallationJob::class, 'assigned_staff_id');
    }

    public function jobTeamAssignments(): HasMany
    {
        return $this->hasMany(JobTeamAssignment::class);
    }

    public function leadsAssigned(): HasMany
    {
        return $this->hasMany(Lead::class, 'assigned_to');
    }

    public function blogPosts(): HasMany
    {
        return $this->hasMany(BlogPost::class, 'author_id');
    }

    public function isAdmin(): bool
    {
        return $this->role === Role::Admin;
    }

    public function isStaff(): bool
    {
        return $this->role === Role::Staff;
    }

    public function isTechnician(): bool
    {
        return $this->role === Role::Technician;
    }

    public function isCustomer(): bool
    {
        return $this->role === Role::Customer;
    }
}
