<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UrlList extends Model
{
    protected $fillable = ['user_id', 'name', 'custom_url', 'published', 'allow_access_requests'];

    protected $casts = ['allow_access_requests' => 'boolean'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function urls(): HasMany
    {
        return $this->hasMany(Url::class);
    }
    
    /**
     * Get the collaborators for this URL list
     */
    public function collaborators(): HasMany
    {
        return $this->hasMany(ListCollaborator::class);
    }
    
    /**
     * Get the access requests for this URL list
     */
    public function accessRequests(): HasMany
    {
        return $this->hasMany(AccessRequest::class);
    }
    
    /**
     * Check if a user is a collaborator on this list
     */
    public function isCollaborator(int $userId): bool
    {
        return $this->collaborators()
            ->where('user_id', $userId)
            ->exists();
    }
    
    /**
     * Check if a user has a pending access request for this list
     */
    public function hasPendingAccessRequest(int $userId): bool
    {
        return $this->accessRequests()
            ->where('requester_id', $userId)
            ->where('status', 'pending')
            ->exists();
    }
}
