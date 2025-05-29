<?php

use Livewire\Volt\Component;
use App\Models\Plan;
use Illuminate\Support\Str;

new class extends Component {
    public $name = '';
    public $description = '';
    public $monthlyPrice = 0;
    public $yearlyPrice = 0;
    public $maxLists = -1;
    public $maxUrlsPerList = -1;
    public $maxTeamMembers = -1;
    public $features = [];
    public $isActive = true;
    public $isFeatured = false;
    public $feature = '';

    public function addFeature() {
        if (!empty($this->feature)) {
            $this->features[] = $this->feature;
            $this->feature = '';
        }
    }

    public function removeFeature($index) {
        unset($this->features[$index]);
        $this->features = array_values($this->features);
    }

    public function createPlan() {
        $this->validate([
            'name' => 'required|string|max:255|unique:plans,name',
            'description' => 'required|string|max:1000',
            'monthlyPrice' => 'required|numeric|min:0',
            'yearlyPrice' => 'required|numeric|min:0',
            'maxLists' => 'required|integer|min:-1',
            'maxUrlsPerList' => 'required|integer|min:-1',
            'maxTeamMembers' => 'required|integer|min:-1',
            'features' => 'required|array',
            'isActive' => 'boolean',
            'isFeatured' => 'boolean',
        ]);

        $plan = Plan::create([
            'name' => $this->name,
            'slug' => Str::slug($this->name),
            'description' => $this->description,
            'monthly_price' => $this->monthlyPrice,
            'yearly_price' => $this->yearlyPrice,
            'features' => $this->features,
            'max_lists' => $this->maxLists,
            'max_urls_per_list' => $this->maxUrlsPerList,
            'max_team_members' => $this->maxTeamMembers,
            'is_active' => $this->isActive,
            'is_featured' => $this->isFeatured,
        ]);

        // Create initial version
        $plan->createVersion([
            'version' => '1.0.0',
            'name' => $this->name,
            'description' => $this->description,
            'monthly_price' => $this->monthlyPrice,
            'yearly_price' => $this->yearlyPrice,
            'features' => $this->features,
            'is_active' => true,
            'valid_from' => now(),
        ]);

        session()->flash('success', 'Plan created successfully.');
        return redirect()->route('admin.plans.index');
    }
}

?>

<div class="max-w-7xl mx-auto backdrop-blur-sm bg-white/80 dark:bg-neutral-800/80 shadow-xl rounded-3xl p-6 lg:p-8 mt-8 border border-gray-100/40 dark:border-neutral-700/50 transition-all duration-300 relative overflow-hidden space-y-8">
    <!-- Decorative elements -->
    <div class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-bl from-emerald-400/10 to-transparent rounded-full blur-3xl -z-10"></div>
    <div class="absolute bottom-0 left-0 w-80 h-80 bg-gradient-to-tr from-teal-400/10 to-transparent rounded-full blur-3xl -z-10"></div>
    
    <!-- Header with glass morphism effect -->
    <div class="backdrop-blur-sm bg-white/80 dark:bg-zinc-800/80 shadow-xl rounded-2xl p-6 border border-gray-100/40 dark:border-zinc-700/50 transition-all duration-300 relative overflow-hidden">
        <div class="relative">
            <h2 class="text-2xl md:text-3xl font-bold tracking-tight text-gray-900 dark:text-white">
                <span class="bg-clip-text text-transparent bg-gradient-to-r from-emerald-500 to-teal-400">
                    Create Subscription Plan
                </span>
            </h2>
            <!-- Animated decorative element -->
            <div class="absolute -bottom-2 left-0 h-1 w-16 bg-gradient-to-r from-emerald-500 to-teal-400 rounded-full animate-pulse"></div>
        </div>
        <p class="mt-2 text-gray-600 dark:text-gray-400">
            Define a new subscription plan with features and pricing options for your users.
        </p>
    </div>

    <form wire:submit="createPlan" class="space-y-8">
        <!-- Basic Information Section -->
        <div class="backdrop-blur-sm bg-white/60 dark:bg-zinc-800/60 shadow-lg rounded-2xl p-6 border border-gray-100/40 dark:border-zinc-700/50 transition-all duration-300 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-40 h-40 bg-gradient-to-bl from-emerald-400/5 to-transparent rounded-full blur-2xl -z-10"></div>
            
            <div class="flex items-center space-x-3 mb-5">
                <div class="flex-shrink-0 h-10 w-10 rounded-full bg-gradient-to-br from-emerald-500/80 to-teal-500/80 text-white flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 2a4 4 0 00-4 4v1H5a1 1 0 00-.994.89l-1 9A1 1 0 004 18h12a1 1 0 00.994-1.11l-1-9A1 1 0 0015 7h-1V6a4 4 0 00-4-4zm2 5V6a2 2 0 10-4 0v1h4zm-6 3a1 1 0 112 0 1 1 0 01-2 0zm7-1a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Plan Details</h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="col-span-1 md:col-span-2">
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Plan Name
                    </label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 group-focus-within:text-emerald-500 transition-colors duration-200" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <x-flux::input
                            id="name"
                            wire:model="name"
                            placeholder="e.g. Professional Plan"
                            prefix-icon="heroicon-o-information-circle"
                            class="rounded-xl dark:bg-zinc-800/80 dark:text-white shadow-sm focus:ring-emerald-500 focus:border-emerald-500 transition-all duration-200"
                        />
                        <div class="absolute inset-x-0 bottom-0 h-0.5 bg-gradient-to-r from-emerald-500 to-teal-500 transform scale-x-0 group-focus-within:scale-x-100 transition-transform duration-300 origin-left rounded-full"></div>
                    </div>
                    @error('name')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="col-span-1 md:col-span-2">
                    <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Description
                    </label>
                    <div class="relative group">
                        <textarea
                            id="description"
                            wire:model="description"
                            rows="3"
                            class="block w-full rounded-xl border-gray-300 dark:border-zinc-700 shadow-sm focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm dark:bg-zinc-800/80 dark:text-white transition-all duration-200"
                            placeholder="Describe the benefits of this plan..."
                        ></textarea>
                        <div class="absolute inset-x-0 bottom-0 h-0.5 bg-gradient-to-r from-emerald-500 to-teal-500 transform scale-x-0 group-focus-within:scale-x-100 transition-transform duration-300 origin-left rounded-full"></div>
                    </div>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Pricing Section -->
        <div class="backdrop-blur-sm bg-white/60 dark:bg-zinc-800/60 shadow-lg rounded-2xl p-6 border border-gray-100/40 dark:border-zinc-700/50 transition-all duration-300 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-40 h-40 bg-gradient-to-bl from-blue-400/5 to-transparent rounded-full blur-2xl -z-10"></div>
            
            <div class="flex items-center space-x-3 mb-5">
                <div class="flex-shrink-0 h-10 w-10 rounded-full bg-gradient-to-br from-blue-500/80 to-indigo-500/80 text-white flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z" />
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Pricing</h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="group relative">
                    <label for="monthly_price" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Monthly Price ($)
                    </label>
                    <div class="relative rounded-xl shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 dark:text-gray-400 sm:text-sm">$</span>
                        </div>
                        <input
                            type="number"
                            id="monthly_price"
                            wire:model="monthlyPrice"
                            step="0.01"
                            class="pl-7 block w-full rounded-xl border-gray-300 dark:border-zinc-700 focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm dark:bg-zinc-800/80 dark:text-white transition-all duration-200"
                            placeholder="0.00"
                        />
                        <div class="absolute inset-x-0 bottom-0 h-0.5 bg-gradient-to-r from-blue-500 to-indigo-500 transform scale-x-0 group-focus-within:scale-x-100 transition-transform duration-300 origin-left rounded-full"></div>
                    </div>
                    @error('monthlyPrice')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="group relative">
                    <label for="yearly_price" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Yearly Price ($)
                    </label>
                    <div class="relative rounded-xl shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 dark:text-gray-400 sm:text-sm">$</span>
                        </div>
                        <input
                            type="number"
                            id="yearly_price"
                            wire:model="yearlyPrice"
                            step="0.01"
                            class="pl-7 block w-full rounded-xl border-gray-300 dark:border-zinc-700 focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm dark:bg-zinc-800/80 dark:text-white transition-all duration-200"
                            placeholder="0.00"
                        />
                        <div class="absolute inset-x-0 bottom-0 h-0.5 bg-gradient-to-r from-blue-500 to-indigo-500 transform scale-x-0 group-focus-within:scale-x-100 transition-transform duration-300 origin-left rounded-full"></div>
                    </div>
                    <div class="text-xs text-emerald-600 dark:text-emerald-400 mt-1">
                        <span class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Consider offering a discount for yearly plans
                        </span>
                    </div>
                    @error('yearlyPrice')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Plan Limits Section -->
        <div class="backdrop-blur-sm bg-white/60 dark:bg-zinc-800/60 shadow-lg rounded-2xl p-6 border border-gray-100/40 dark:border-zinc-700/50 transition-all duration-300 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-40 h-40 bg-gradient-to-bl from-purple-400/5 to-transparent rounded-full blur-2xl -z-10"></div>
            
            <div class="flex items-center space-x-3 mb-5">
                <div class="flex-shrink-0 h-10 w-10 rounded-full bg-gradient-to-br from-purple-500/80 to-pink-500/80 text-white flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Plan Limits</h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="group relative">
                    <label for="max_lists" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Max Lists
                    </label>
                    <div class="mt-1 relative rounded-xl shadow-sm">
                        <input
                            type="number"
                            id="max_lists"
                            wire:model="maxLists"
                            class="block w-full rounded-xl border-gray-300 dark:border-zinc-700 focus:ring-purple-500 focus:border-purple-500 sm:text-sm dark:bg-zinc-800/80 dark:text-white transition-all duration-200"
                            placeholder="-1"
                        />
                        <div class="absolute inset-x-0 bottom-0 h-0.5 bg-gradient-to-r from-purple-500 to-pink-500 transform scale-x-0 group-focus-within:scale-x-100 transition-transform duration-300 origin-left rounded-full"></div>
                    </div>
                    <div class="flex items-center mt-1 text-xs text-gray-500 dark:text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1 text-purple-500" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                        </svg>
                        Use -1 for unlimited
                    </div>
                    @error('maxLists')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="group relative">
                    <label for="max_urls_per_list" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Max URLs Per List
                    </label>
                    <div class="mt-1 relative rounded-xl shadow-sm">
                        <input
                            type="number"
                            id="max_urls_per_list"
                            wire:model="maxUrlsPerList"
                            class="block w-full rounded-xl border-gray-300 dark:border-zinc-700 focus:ring-purple-500 focus:border-purple-500 sm:text-sm dark:bg-zinc-800/80 dark:text-white transition-all duration-200"
                            placeholder="-1"
                        />
                        <div class="absolute inset-x-0 bottom-0 h-0.5 bg-gradient-to-r from-purple-500 to-pink-500 transform scale-x-0 group-focus-within:scale-x-100 transition-transform duration-300 origin-left rounded-full"></div>
                    </div>
                    <div class="flex items-center mt-1 text-xs text-gray-500 dark:text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1 text-purple-500" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                        </svg>
                        Use -1 for unlimited
                    </div>
                    @error('maxUrlsPerList')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="group relative">
                    <label for="max_team_members" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Max Team Members
                    </label>
                    <div class="mt-1 relative rounded-xl shadow-sm">
                        <input
                            type="number"
                            id="max_team_members"
                            wire:model="maxTeamMembers"
                            class="block w-full rounded-xl border-gray-300 dark:border-zinc-700 focus:ring-purple-500 focus:border-purple-500 sm:text-sm dark:bg-zinc-800/80 dark:text-white transition-all duration-200"
                            placeholder="-1"
                        />
                        <div class="absolute inset-x-0 bottom-0 h-0.5 bg-gradient-to-r from-purple-500 to-pink-500 transform scale-x-0 group-focus-within:scale-x-100 transition-transform duration-300 origin-left rounded-full"></div>
                    </div>
                    <div class="flex items-center mt-1 text-xs text-gray-500 dark:text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1 text-purple-500" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                        </svg>
                        Use -1 for unlimited
                    </div>
                    @error('maxTeamMembers')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Features Section -->
        <div class="backdrop-blur-sm bg-white/60 dark:bg-zinc-800/60 shadow-lg rounded-2xl p-6 border border-gray-100/40 dark:border-zinc-700/50 transition-all duration-300 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-40 h-40 bg-gradient-to-bl from-amber-400/5 to-transparent rounded-full blur-2xl -z-10"></div>
            
            <div class="flex items-center space-x-3 mb-5">
                <div class="flex-shrink-0 h-10 w-10 rounded-full bg-gradient-to-br from-amber-500/80 to-orange-500/80 text-white flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.214.33-.403.713-.57 1.116-.334.804-.614 1.768-.84 2.734a31.365 31.365 0 00-.613 3.58 2.64 2.64 0 01-.945-1.067c-.328-.68-.398-1.534-.398-2.654A1 1 0 005.05 6.05 6.981 6.981 0 003 11a7 7 0 1011.95-4.95c-.592-.591-.98-.985-1.348-1.467-.363-.476-.724-1.063-1.207-2.03zM12.12 15.12A3 3 0 017 13s.879.5 2.5.5c0-1 .5-4 1.25-4.5.5 1 .786 1.293 1.371 1.879A2.99 2.99 0 0113 13a2.99 2.99 0 01-.879 2.121z" clip-rule="evenodd" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Plan Features</h3>
            </div>

            <div class="space-y-4">
                <div class="group">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Add Features
                    </label>
                    <div class="flex space-x-2">
                        <div class="relative flex-grow">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 group-focus-within:text-amber-500 transition-colors duration-200" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <input
                                type="text"
                                wire:model="feature"
                                wire:keydown.enter.prevent="addFeature"
                                class="pl-10 block w-full rounded-xl border-gray-300 dark:border-zinc-700 shadow-sm focus:ring-amber-500 focus:border-amber-500 sm:text-sm dark:bg-zinc-800/80 dark:text-white transition-all duration-200"
                                placeholder="Add a feature..."
                            />
                            <div class="absolute inset-x-0 bottom-0 h-0.5 bg-gradient-to-r from-amber-500 to-orange-500 transform scale-x-0 group-focus-within:scale-x-100 transition-transform duration-300 origin-left rounded-full"></div>
                        </div>
                        <button
                            type="button"
                            wire:click="addFeature"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-xl shadow-sm text-white bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 transition-all duration-300"
                        >
                            <span class="relative z-10">Add</span>
                            <!-- Shimmer effect -->
                            <span class="absolute top-0 right-full w-12 h-full bg-white/30 transform rotate-12 translate-x-0 transition-transform duration-1000 ease-out group-hover:translate-x-[400%]"></span>
                        </button>
                    </div>
                </div>

                <div class="mt-6">
                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Plan Features</h4>
                    
                    <div class="grid gap-2">
                        @forelse($features as $index => $feat)
                            <div class="group flex items-center justify-between bg-white/40 dark:bg-zinc-900/30 p-3 rounded-xl border border-gray-100/60 dark:border-zinc-700/40 shadow-sm hover:shadow-md transition-all duration-300 transform hover:-translate-y-0.5">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-6 w-6 rounded-full bg-gradient-to-br from-amber-500/20 to-orange-500/20 text-amber-500 flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ $feat }}</span>
                                </div>
                                <button
                                    type="button"
                                    wire:click="removeFeature({{ $index }})"
                                    class="text-gray-400 hover:text-red-600 dark:hover:text-red-400 transition-colors duration-200"
                                >
                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                        @empty
                            <div class="flex flex-col items-center justify-center bg-gray-50/60 dark:bg-zinc-900/30 p-6 rounded-xl border border-dashed border-gray-300 dark:border-zinc-700">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400 dark:text-gray-600 mb-2" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM14 11a1 1 0 011 1v1h1a1 1 0 110 2h-1v1a1 1 0 11-2 0v-1h-1a1 1 0 110-2h1v-1a1 1 0 011-1z" />
                                </svg>
                                <p class="text-sm text-gray-500 dark:text-gray-400">No features added yet</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500">Add features to make your plan more attractive</p>
                            </div>
                        @endforelse
                    </div>
                    @error('features')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Plan Status Section -->
        <div class="backdrop-blur-sm bg-white/60 dark:bg-zinc-800/60 shadow-lg rounded-2xl p-6 border border-gray-100/40 dark:border-zinc-700/50 transition-all duration-300 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-40 h-40 bg-gradient-to-bl from-teal-400/5 to-transparent rounded-full blur-2xl -z-10"></div>
            
            <div class="flex items-center space-x-3 mb-5">
                <div class="flex-shrink-0 h-10 w-10 rounded-full bg-gradient-to-br from-teal-500/80 to-green-500/80 text-white flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Plan Status</h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="relative">
                    <div class="flex items-center space-x-3">
                        <div class="relative inline-block w-12 align-middle select-none transition duration-200 ease-in">
                            <input 
                                type="checkbox" 
                                id="is_active" 
                                wire:model="isActive"
                                class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 border-gray-300 appearance-none cursor-pointer transition-transform duration-300"
                            />
                            <label for="is_active" class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-300 dark:bg-gray-700 cursor-pointer"></label>
                        </div>
                        <label for="is_active" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                            Active
                        </label>
                    </div>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 ml-15">
                        Active plans are visible to customers and available for purchase
                    </p>
                </div>

                <div class="relative">
                    <div class="flex items-center space-x-3">
                        <div class="relative inline-block w-12 align-middle select-none transition duration-200 ease-in">
                            <input 
                                type="checkbox" 
                                id="is_featured" 
                                wire:model="isFeatured"
                                class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 border-gray-300 appearance-none cursor-pointer transition-transform duration-300"
                            />
                            <label for="is_featured" class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-300 dark:bg-gray-700 cursor-pointer"></label>
                        </div>
                        <label for="is_featured" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                            Featured
                        </label>
                    </div>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 ml-15">
                        Featured plans are highlighted on the pricing page
                    </p>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex justify-end space-x-3">
            <a
                href="{{ route('admin.plans.index') }}"
                class="inline-flex items-center px-4 py-2.5 border border-gray-300 shadow-sm text-sm font-medium rounded-xl text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 dark:bg-zinc-800 dark:border-zinc-700 dark:text-gray-300 dark:hover:bg-zinc-700 transition-all duration-200"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-gray-500" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M7.707 14.707a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l2.293 2.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                </svg>
                Cancel
            </a>
            <button
                type="submit"
                class="relative overflow-hidden inline-flex items-center px-4 py-2.5 border border-transparent shadow-sm text-sm font-medium rounded-xl text-white bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-all duration-300 group"
            >
                <span class="relative z-10 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    Create Plan
                </span>
                <!-- Shimmer effect -->
                <span class="absolute top-0 right-full w-12 h-full bg-white/30 transform rotate-12 translate-x-0 transition-transform duration-1000 ease-out group-hover:translate-x-[400%]"></span>
            </button>
        </div>
    </form>

<style>
    /* Toggle button styling */
    .toggle-checkbox:checked {
        transform: translateX(100%);
        border-color: #10B981;
    }
    .toggle-checkbox:checked + .toggle-label {
        background-color: #10B981;
    }
    /* For dark mode */
    .dark .toggle-checkbox:checked + .toggle-label {
        background-color: #059669;
    }
</style>
</div>
