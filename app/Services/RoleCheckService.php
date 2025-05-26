<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use App\Models\User;

class RoleCheckService
{
    /**
     * Check if the user has a specific role
     */
    public function hasRole(string $role): bool
    {
        return Auth::check() && Auth::user()->hasRole($role);
    }

    /**
     * Check if the user has any of the given roles
     */
    public function hasAnyRole(array $roles): bool
    {
        return Auth::check() && Auth::user()->hasAnyRole($roles);
    }

    /**
     * Check if the user has all of the given roles
     */
    public function hasAllRoles(array $roles): bool
    {
        return Auth::check() && Auth::user()->hasAllRoles($roles);
    }

    /**
     * Check if user has permission for a feature
     */
    public function hasPermission(string $permission): bool
    {
        return Auth::check() && Auth::user()->hasPermissionTo($permission);
    }

    /**
     * Get the user's highest role
     */
    public function getHighestRole(): ?string
    {
        if (!Auth::check()) {
            return null;
        }

        $roleHierarchy = ['admin', 'premium', 'free'];
        $userRoles = Auth::user()->roles->pluck('name')->toArray();

        foreach ($roleHierarchy as $role) {
            if (in_array($role, $userRoles)) {
                return $role;
            }
        }

        return null;
    }
}
