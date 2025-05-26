<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Computed;
use App\Services\RoleCheckService;

new class extends Component
{
    public string $feature = '';
    public string $requiredRole = '';
    public bool $showUpgradePrompt = true;
    
    private RoleCheckService $roleService;
    
    public function mount()
    {
        $this->roleService = new RoleCheckService();
    }
    
    #[Computed]
    public function hasAccess()
    {
        return $this->roleService->hasRole($this->requiredRole) || 
               $this->roleService->hasPermission("access.{$this->feature}");
    }
    
    #[Computed]
    public function currentRole()
    {
        return $this->roleService->getHighestRole();
    }
}
?>

<div>
    @if($this->hasAccess)
        {{ $slot }}
    @else
        <div class="relative">
            <div class="filter blur-sm pointer-events-none">
                {{ $slot }}
            </div>
            
            @if($showUpgradePrompt)
                <div class="absolute inset-0 flex items-center justify-center bg-gray-900/20 dark:bg-gray-800/40 backdrop-blur-sm rounded-lg">
                    <div class="text-center p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                            Premium Feature
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-gray-300 mb-4">
                            This feature requires {{ ucfirst($requiredRole) }} access.
                            @if($this->currentRole === 'free')
                                Upgrade your plan to unlock this feature.
                            @endif
                        </p>
                        
                        @if($this->currentRole === 'free')
                            <a href="{{ route('plans') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500">
                                View Plans
                            </a>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    @endif
</div>
