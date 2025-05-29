<?php

use App\Models\User;
use App\Services\SubscriptionService;
use App\Services\UsageTrackingService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Volt\Component;

new class extends Component {
    public User $user;
    
    public function mount(User $user)
    {
        $this->user = $user;
    }
    
    #[Computed]
    public function subscription()
    {
        return $this->user->subscription;
    }
    
    #[Computed]
    public function usageStats()
    {
        try {
            $service = app(UsageTrackingService::class);
            return $service->getTotalUsage($this->user);
        } catch (\Exception $e) {
            \Log::error('Error getting usage stats: ' . $e->getMessage());
            return [
                'lists' => 0,
                'urls' => 0,
                'collaborators' => 0,
            ];
        }
    }
    
    #[Computed]
    public function featureLimits()
    {
        try {
            $service = app(SubscriptionService::class);
            return $service->getFeatureLimits($this->user);
        } catch (\Exception $e) {
            \Log::error('Error getting feature limits: ' . $e->getMessage());
            return [
                'lists' => 0,
                'urls_per_list' => 0,
                'collaborators' => 0,
                'custom_domains' => false,
                'analytics' => false,
            ];
        }
    }
    
    #[Computed]
    public function recentActivity(): Collection
    {
        return \App\Models\ActivityLog::where('user_id', $this->user->id)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();
    }

    public function getUsagePercentage($used, $limit)
    {
        if ($limit === -1) return 0; // Unlimited
        if ($limit === 0) return 100; // No limit set
        return min(100, ($used / $limit) * 100);
    }
};
?>

<div class="max-w-7xl mx-auto backdrop-blur-sm bg-white/80 dark:bg-neutral-800/80 shadow-xl rounded-3xl p-6 lg:p-8 mt-8 border border-gray-100/40 dark:border-neutral-700/50 transition-all duration-300 relative overflow-hidden">
    <!-- Decorative elements -->
    <div class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-bl from-emerald-400/10 to-transparent rounded-full blur-3xl -z-10"></div>
    <div class="absolute bottom-0 left-0 w-80 h-80 bg-gradient-to-tr from-teal-400/10 to-transparent rounded-full blur-3xl -z-10"></div>
    
    <!-- Header with back button -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8 gap-4">
        <div>
            <div class="relative">
                <h2 class="text-2xl md:text-3xl font-bold tracking-tight">
                    <span class="bg-clip-text text-transparent bg-gradient-to-r from-emerald-500 to-teal-400">
                        Customer Details
                    </span>
                </h2>
                <!-- Animated decorative element -->
                <div class="absolute -bottom-2 left-0 h-1 w-16 bg-gradient-to-r from-emerald-500 to-teal-400 rounded-full animate-pulse"></div>
            </div>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                Detailed customer information and activity
            </p>
        </div>
        
        <a 
            href="{{ route('admin.subscriptions.customers') }}" 
            class="group inline-flex items-center px-4 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 bg-white dark:bg-zinc-700/50 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-zinc-600/50 hover:bg-gray-50 dark:hover:bg-zinc-700/80 shadow-sm"
            wire:navigate
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-emerald-500 transition-transform duration-300 group-hover:-translate-x-1" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
            </svg>
            Back to Customers
        </a>
    </div>

    <!-- Customer Profile Header -->
    <div class="backdrop-blur-sm bg-white/80 dark:bg-zinc-800/80 shadow-xl rounded-2xl p-6 border border-gray-100/40 dark:border-zinc-700/50 transition-all duration-300 relative overflow-hidden mb-6">
        <div class="flex flex-col sm:flex-row items-center sm:items-start gap-4">
            <!-- Avatar with gradient background -->
            <div class="h-20 w-20 rounded-2xl bg-gradient-to-br from-emerald-400 to-teal-500 flex items-center justify-center shadow-lg transform transition-all duration-300 hover:scale-105">
                <span class="text-xl font-bold text-white">
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                </span>
            </div>
            
            <!-- User info -->
            <div class="flex-1 text-center sm:text-left">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ $user->name }}</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $user->email }}</p>
                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 mt-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                        </svg>
                        Joined {{ $user->created_at->format('M d, Y') }}
                    </span>
                    
                    @if($user->email_verified_at)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            Verified
                        </span>
                    @endif
                </div>
            </div>
            
            <!-- Subscription button -->
            <div class="mt-3 sm:mt-0">
                @if($this->subscription)
                    <a 
                        href="{{ route('admin.subscriptions.show', $this->subscription) }}" 
                        class="relative overflow-hidden inline-flex items-center px-4 py-2.5 rounded-xl text-sm font-medium bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white transition-all duration-300 shadow-sm hover:shadow group"
                    >
                        <span class="relative z-10 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4z" />
                                <path fill-rule="evenodd" d="M12 8a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4a2 2 0 012-2h6z" clip-rule="evenodd" />
                            </svg>
                            View Subscription
                        </span>
                        <!-- Shimmer effect -->
                        <span class="absolute top-0 right-full w-12 h-full bg-white/30 transform rotate-12 translate-x-0 transition-transform duration-1000 ease-out group-hover:translate-x-[400%]"></span>
                    </a>
                @else
                    <span class="inline-flex items-center px-4 py-2.5 rounded-xl text-sm font-medium bg-gray-100 dark:bg-zinc-700/50 text-gray-700 dark:text-gray-400 transition-all duration-200 shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                        </svg>
                        No Subscription
                    </span>
                @endif
            </div>
        </div>
    </div>

    <!-- Customer Information Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Personal Information -->
        <div class="group backdrop-blur-sm bg-white/60 dark:bg-zinc-800/40 rounded-xl overflow-hidden border border-gray-200/60 dark:border-zinc-700/40 shadow-sm transition-all duration-300 hover:shadow-md">
            <div class="px-5 py-4">
                <div class="flex items-center mb-3">
                    <div class="flex-shrink-0 bg-emerald-50 dark:bg-emerald-900/20 rounded-full p-2 border border-emerald-100 dark:border-emerald-800/20">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-500" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <h4 class="ml-3 text-lg font-semibold text-gray-800 dark:text-gray-200">Personal Information</h4>
                </div>
                
                <dl class="space-y-3 mt-4">
                    <div class="grid grid-cols-2 gap-4">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Name</dt>
                        <dd class="text-sm text-gray-900 dark:text-white font-medium">{{ $user->name }}</dd>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</dt>
                        <dd class="text-sm text-gray-900 dark:text-white font-medium">{{ $user->email }}</dd>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Email verified</dt>
                        <dd class="text-sm">
                            @if($user->email_verified_at)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400">
                                    <svg class="mr-1.5 h-2 w-2 text-green-500" fill="currentColor" viewBox="0 0 8 8">
                                        <circle cx="4" cy="4" r="3" />
                                    </svg>
                                    {{ $user->email_verified_at->format('M d, Y') }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400">
                                    <svg class="mr-1.5 h-2 w-2 text-red-500" fill="currentColor" viewBox="0 0 8 8">
                                        <circle cx="4" cy="4" r="3" />
                                    </svg>
                                    Not verified
                                </span>
                            @endif
                        </dd>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Account created</dt>
                        <dd class="text-sm text-gray-900 dark:text-white font-medium">{{ $user->created_at->format('M d, Y') }}</dd>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Last updated</dt>
                        <dd class="text-sm text-gray-900 dark:text-white font-medium">{{ $user->updated_at->format('M d, Y') }}</dd>
                    </div>
                </dl>
            </div>
            <div class="w-full bg-gradient-to-r from-emerald-500 to-teal-500 h-1 transform origin-left scale-x-0 group-hover:scale-x-100 transition-transform duration-300"></div>
        </div>

        <!-- Subscription Information -->
        <div class="group backdrop-blur-sm bg-white/60 dark:bg-zinc-800/40 rounded-xl overflow-hidden border border-gray-200/60 dark:border-zinc-700/40 shadow-sm transition-all duration-300 hover:shadow-md">
            <div class="px-5 py-4">
                <div class="flex items-center mb-3">
                    <div class="flex-shrink-0 bg-blue-50 dark:bg-blue-900/20 rounded-full p-2 border border-blue-100 dark:border-blue-800/20">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4z" />
                            <path fill-rule="evenodd" d="M12 8a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4a2 2 0 012-2h6z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <h4 class="ml-3 text-lg font-semibold text-gray-800 dark:text-gray-200">Subscription Information</h4>
                </div>
                
                @if($this->subscription)
                    <dl class="space-y-3 mt-4">
                        <div class="grid grid-cols-2 gap-4">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Plan</dt>
                            <dd class="text-sm text-gray-900 dark:text-white font-medium">{{ $this->subscription->plan->name }}</dd>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</dt>
                            <dd class="text-sm">
                                <x-subscription-status :status="$this->subscription->status" />
                            </dd>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Interval</dt>
                            <dd class="text-sm text-gray-900 dark:text-white font-medium">{{ ucfirst($this->subscription->interval) }}</dd>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Current period ends</dt>
                            <dd class="text-sm">
                                @if($this->subscription->current_period_ends_at)
                                    <span class="inline-flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                                        </svg>
                                        {{ $this->subscription->current_period_ends_at->format('M d, Y') }}
                                    </span>
                                @else
                                    <span class="text-gray-400 dark:text-gray-500">N/A</span>
                                @endif
                            </dd>
                        </div>
                        @if($this->subscription->trial_ends_at)
                            <div class="grid grid-cols-2 gap-4">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Trial ends</dt>
                                <dd class="text-sm">
                                    <span class="inline-flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 text-blue-500" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                        </svg>
                                        {{ $this->subscription->trial_ends_at->format('M d, Y') }}
                                        @if($this->subscription->trial_ends_at->isFuture())
                                            <span class="ml-1 text-xs text-blue-500 dark:text-blue-400">({{ $this->subscription->trial_ends_at->diffForHumans() }})</span>
                                        @endif
                                    </span>
                                </dd>
                            </div>
                        @endif
                        @if($this->subscription->cancelled_at)
                            <div class="grid grid-cols-2 gap-4">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Cancelled at</dt>
                                <dd class="text-sm">
                                    <span class="inline-flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                        </svg>
                                        {{ $this->subscription->cancelled_at->format('M d, Y') }}
                                    </span>
                                </dd>
                            </div>
                        @endif
                    </dl>
                @else
                    <div class="flex items-center justify-center h-32 mt-4">
                        <div class="text-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-10 w-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">No active subscription</p>
                        </div>
                    </div>
                @endif
            </div>
            <div class="w-full bg-gradient-to-r from-blue-500 to-sky-500 h-1 transform origin-left scale-x-0 group-hover:scale-x-100 transition-transform duration-300"></div>
        </div>

        <!-- Usage Metrics -->
        <div class="group backdrop-blur-sm bg-white/60 dark:bg-zinc-800/40 rounded-xl overflow-hidden border border-gray-200/60 dark:border-zinc-700/40 shadow-sm transition-all duration-300 hover:shadow-md">
            <div class="px-5 py-4">
                <div class="flex items-center mb-3">
                    <div class="flex-shrink-0 bg-emerald-50 dark:bg-emerald-900/20 rounded-full p-2 border border-emerald-100 dark:border-emerald-800/20">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-500" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M2 10a8 8 0 018-8v8h8a8 8 0 11-16 0z" />
                            <path d="M12 2.252A8.014 8.014 0 0117.748 8H12V2.252z" />
                        </svg>
                    </div>
                    <h4 class="ml-3 text-lg font-semibold text-gray-800 dark:text-gray-200">Usage Metrics</h4>
                </div>
                
                <div class="space-y-5 mt-4">
                    {{-- Lists Usage --}}
                    <div>
                        <div class="flex justify-between text-sm mb-1.5">
                            <span class="font-medium text-gray-700 dark:text-gray-300">URL Lists</span>
                            <span class="font-bold text-emerald-600 dark:text-emerald-400">
                                {{ $this->usageStats['lists'] ?? 0 }} 
                                <span class="text-gray-500 dark:text-gray-400 font-normal">/ {{ $this->featureLimits['lists'] === -1 ? '∞' : $this->featureLimits['lists'] }}</span>
                            </span>
                        </div>
                        <div class="w-full h-2.5 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                            <div 
                                class="h-full bg-gradient-to-r from-emerald-500 to-teal-500 rounded-full transition-all duration-500 ease-in-out" 
                                style="width: {{ $this->getUsagePercentage($this->usageStats['lists'] ?? 0, $this->featureLimits['lists']) }}%"
                            ></div>
                        </div>
                    </div>

                    {{-- URLs per List Usage --}}
                    <div>
                        <div class="flex justify-between text-sm mb-1.5">
                            <span class="font-medium text-gray-700 dark:text-gray-300">URLs per List</span>
                            <span class="font-bold text-emerald-600 dark:text-emerald-400">
                                {{ $this->usageStats['urls_per_list'] ?? 0 }}
                                <span class="text-gray-500 dark:text-gray-400 font-normal">/ {{ $this->featureLimits['urls_per_list'] === -1 ? '∞' : $this->featureLimits['urls_per_list'] }}</span>
                            </span>
                        </div>
                        <div class="w-full h-2.5 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                            <div 
                                class="h-full bg-gradient-to-r from-emerald-500 to-teal-500 rounded-full transition-all duration-500 ease-in-out" 
                                style="width: {{ $this->getUsagePercentage($this->usageStats['urls_per_list'] ?? 0, $this->featureLimits['urls_per_list']) }}%"
                            ></div>
                        </div>
                    </div>

                    {{-- Collaborators Usage --}}
                    <div>
                        <div class="flex justify-between text-sm mb-1.5">
                            <span class="font-medium text-gray-700 dark:text-gray-300">Collaborators</span>
                            <span class="font-bold text-emerald-600 dark:text-emerald-400">
                                {{ $this->usageStats['collaborators'] ?? 0 }}
                                <span class="text-gray-500 dark:text-gray-400 font-normal">/ {{ $this->featureLimits['collaborators'] === -1 ? '∞' : $this->featureLimits['collaborators'] }}</span>
                            </span>
                        </div>
                        <div class="w-full h-2.5 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                            <div 
                                class="h-full bg-gradient-to-r from-emerald-500 to-teal-500 rounded-full transition-all duration-500 ease-in-out" 
                                style="width: {{ $this->getUsagePercentage($this->usageStats['collaborators'] ?? 0, $this->featureLimits['collaborators']) }}%"
                            ></div>
                        </div>
                    </div>

                    {{-- Feature Toggles --}}
                    <div class="grid grid-cols-2 gap-4 mt-6 pt-4 border-t border-gray-200/60 dark:border-zinc-700/40">
                        <div class="flex items-center">
                            @if($this->featureLimits['custom_domains'])
                                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-900/30">
                                    <svg class="h-4 w-4 text-emerald-500" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                            @else
                                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700">
                                    <svg class="h-4 w-4 text-gray-400 dark:text-gray-500" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                            @endif
                            <span class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">Custom Domains</span>
                        </div>
                        <div class="flex items-center">
                            @if($this->featureLimits['analytics'])
                                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-900/30">
                                    <svg class="h-4 w-4 text-emerald-500" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                            @else
                                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700">
                                    <svg class="h-4 w-4 text-gray-400 dark:text-gray-500" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                            @endif
                            <span class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">Analytics</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="w-full bg-gradient-to-r from-emerald-500 to-teal-500 h-1 transform origin-left scale-x-0 group-hover:scale-x-100 transition-transform duration-300"></div>
        </div>

        <!-- Recent Activity -->
        <div class="group backdrop-blur-sm bg-white/60 dark:bg-zinc-800/40 rounded-xl overflow-hidden border border-gray-200/60 dark:border-zinc-700/40 shadow-sm transition-all duration-300 hover:shadow-md">
            <div class="px-5 py-4">
                <div class="flex items-center mb-3">
                    <div class="flex-shrink-0 bg-indigo-50 dark:bg-indigo-900/20 rounded-full p-2 border border-indigo-100 dark:border-indigo-800/20">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-500" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <h4 class="ml-3 text-lg font-semibold text-gray-800 dark:text-gray-200">Recent Activity</h4>
                </div>
                
                <div class="mt-4 max-h-64 overflow-y-auto pr-1 scrollbar-thin scrollbar-thumb-gray-200 dark:scrollbar-thumb-gray-700 scrollbar-track-transparent">
                    @if($this->recentActivity->count() > 0)
                        <div class="relative pl-4 before:absolute before:left-1.5 before:top-0 before:h-full before:w-0.5 before:bg-gradient-to-b before:from-indigo-500 before:to-indigo-300 dark:before:from-indigo-500 dark:before:to-indigo-800/30">
                            @foreach($this->recentActivity as $activity)
                                <div class="relative mb-5 last:mb-0 pl-3 transform transition-all duration-300 hover:-translate-y-1">
                                    <div class="absolute -left-4 mt-1.5 h-3 w-3 rounded-full bg-indigo-500 ring-4 ring-white dark:ring-zinc-800"></div>
                                    <div class="bg-white/60 dark:bg-zinc-700/40 rounded-lg p-3 shadow-sm border border-gray-100/40 dark:border-zinc-600/40">
                                        <p class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ $activity->event }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 flex items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                            </svg>
                                            {{ $activity->created_at->diffForHumans() }}
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center h-32 py-6">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="mt-3 text-sm font-medium text-gray-500 dark:text-gray-400">No recent activity recorded</p>
                        </div>
                    @endif
                </div>
            </div>
            <div class="w-full bg-gradient-to-r from-indigo-500 to-purple-500 h-1 transform origin-left scale-x-0 group-hover:scale-x-100 transition-transform duration-300"></div>
        </div>
    </div>
</div>
