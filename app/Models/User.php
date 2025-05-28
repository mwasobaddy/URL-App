<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
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
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->map(fn (string $name) => Str::of($name)->substr(0, 1))
            ->implode('');
    }
    
    /**
     * Get URL lists owned by the user
     */
    public function urlLists(): HasMany
    {
        return $this->hasMany(UrlList::class);
    }
    
    /**
     * Get the lists that the user collaborates on
     */
    public function collaboratingLists(): HasMany
    {
        return $this->hasMany(ListCollaborator::class);
    }
    
    /**
     * Get access requests made by the user
     */
    public function accessRequests(): HasMany
    {
        return $this->hasMany(AccessRequest::class, 'requester_id');
    }

    /**
     * Get the user's subscription
     */
    public function subscription()
    {
        return $this->hasOne(Subscription::class)->latest();
    }

    /**
     * Get all subscriptions for the user
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }
}
