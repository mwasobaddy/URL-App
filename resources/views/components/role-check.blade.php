<?php

use function Livewire\Volt\{state, computed};
use App\Services\RoleCheckService;

state([
    'role' => '',
    'roles' => [],
    'permission' => '',
    'show' => false,
]);

$roleService = new RoleCheckService();

$shouldShow = computed(function () use ($roleService) {
    if ($this->permission) {
        return $roleService->hasPermission($this->permission);
    }
    
    if ($this->role) {
        return $roleService->hasRole($this->role);
    }
    
    if (!empty($this->roles)) {
        return $roleService->hasAnyRole($this->roles);
    }
    
    return false;
});

?>

@if($shouldShow)
    {{ $slot }}
@endif
