<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'phone', 'password', 'role', 
        'google_id', 'vkontakte_id', 'avatar_url'
    ];

    protected $hidden = ['password', 'remember_token'];

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
            'role' => UserRole::class,
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function deliveryAddresses(): HasMany
    {
        return $this->hasMany(DeliveryAddress::class);
    }

    public function defaultDeliveryAddress(): HasOne
    {
        return $this->hasOne(DeliveryAddress::class)->where('is_default', true);
    }

    public function isAdmin(): bool {
        return $this->role === UserRole::ADMIN;
    }
    
    public function isWorker(): bool {
        return $this->role === UserRole::WORKER;
    }
    
    public function isModerator(): bool {
        return $this->role === UserRole::MODERATOR;
    }

    public function isCustomer(): bool {
        return $this->role === UserRole::CUSTOMER;
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
}
