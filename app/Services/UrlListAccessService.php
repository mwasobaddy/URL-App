<?php

namespace App\Services;

use App\Models\UrlList;
use App\Models\User;

class UrlListAccessService
{
    /**
     * Determine if a user can view a URL list
     */
    public function canView(User $user, UrlList $urlList): bool
    {
        // List owner can always view their list
        if ($urlList->user_id === $user->id) {
            return true;
        }
        
        // Collaborators can view the list
        if ($urlList->isCollaborator($user->id)) {
            return true;
        }
        
        // If the list is published, everyone can view it
        if ($urlList->published) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Determine if a user can edit a URL list
     */
    public function canEdit(User $user, UrlList $urlList): bool
    {
        // List owner can always edit their list
        if ($urlList->user_id === $user->id) {
            return true;
        }
        
        // Collaborators can edit the list
        if ($urlList->isCollaborator($user->id)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Determine if a user can delete a URL list
     */
    public function canDelete(User $user, UrlList $urlList): bool
    {
        // Only the list owner can delete their list
        return $urlList->user_id === $user->id;
    }
    
    /**
     * Determine if a user can manage access for a URL list
     */
    public function canManageAccess(User $user, UrlList $urlList): bool
    {
        // Only the list owner can manage access
        return $urlList->user_id === $user->id;
    }
}