<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Enums\UserRole;

#[Fillable(['name', 'email', 'password', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

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
        ];
    }

    protected $casts = [
        'role' => UserRole::class,
    ];

    public function isAdmin(): bool {
        return $this->role === UserRole::ADMIN;
    }

    public function isStaff(): bool
    {
        return in_array($this->role, [
            UserRole::ADMIN,
            UserRole::MODERATOR,
            UserRole::WORKER,
        ]);
    }

    public function canManageOrders(): bool
    {
        return in_array($this->role, [
            UserRole::ADMIN,
            UserRole::WORKER,
        ]);
    }

    public function deliveryAddresses()
    {
        return $this->hasMany(DeliveryAddress::class);
    }

    public function defaultDeliveryAddress()
    {
        return $this->hasOne(DeliveryAddress::class)->where('is_default', true);
    }
}
