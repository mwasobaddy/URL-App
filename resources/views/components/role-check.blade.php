<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Computed;
use App\Services\RoleCheckService;

new class extends Component {
    public string $role = '';
    public array $roles = [];
    public string $permission = '';
    public bool $show = false;
    
    #[Computed]
    public function shouldShow()
    {
        $roleService = new RoleCheckService();
        
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
    }
}

?>

@if($this->shouldShow)
    {{ $slot }}
@endif
