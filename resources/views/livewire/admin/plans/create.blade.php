<?php

use function Livewire\Volt\{state};
use App\Models\Plan;
use Illuminate\Support\Str;

state([
    'name' => '',
    'description' => '',
    'monthlyPrice' => 0,
    'yearlyPrice' => 0,
    'maxLists' => -1,
    'maxUrlsPerList' => -1,
    'maxTeamMembers' => -1,
    'features' => [],
    'isActive' => true,
    'isFeatured' => false,
    'feature' => '',
]);

$addFeature = function () {
    if (!empty($this->feature)) {
        $this->features[] = $this->feature;
        $this->feature = '';
    }
};

$removeFeature = function ($index) {
    unset($this->features[$index]);
    $this->features = array_values($this->features);
};

$createPlan = function () {
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
};

?>

<div>
    <form wire:submit="createPlan" class="max-w-3xl mx-auto">
        <div class="space-y-8 divide-y divide-gray-200 dark:divide-zinc-700">
            <div class="space-y-6 sm:space-y-5">
                <div>
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                        Create Plan
                    </h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                        Add a new subscription plan with features and pricing.
                    </p>
                </div>

                <!-- Basic Information -->
                <div class="space-y-6 sm:space-y-5">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Plan Name
                        </label>
                        <div class="mt-1">
                            <input
                                type="text"
                                id="name"
                                wire:model="name"
                                class="shadow-sm focus:ring-emerald-500 focus:border-emerald-500 block w-full sm:text-sm border-gray-300 rounded-md dark:bg-zinc-800 dark:border-zinc-700"
                            />
                        </div>
                        @error('name')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Description
                        </label>
                        <div class="mt-1">
                            <textarea
                                id="description"
                                wire:model="description"
                                rows="3"
                                class="shadow-sm focus:ring-emerald-500 focus:border-emerald-500 block w-full sm:text-sm border-gray-300 rounded-md dark:bg-zinc-800 dark:border-zinc-700"
                            ></textarea>
                        </div>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Pricing -->
                    <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2">
                        <div>
                            <label for="monthly_price" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Monthly Price ($)
                            </label>
                            <div class="mt-1">
                                <input
                                    type="number"
                                    id="monthly_price"
                                    wire:model="monthlyPrice"
                                    step="0.01"
                                    class="shadow-sm focus:ring-emerald-500 focus:border-emerald-500 block w-full sm:text-sm border-gray-300 rounded-md dark:bg-zinc-800 dark:border-zinc-700"
                                />
                            </div>
                            @error('monthlyPrice')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="yearly_price" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Yearly Price ($)
                            </label>
                            <div class="mt-1">
                                <input
                                    type="number"
                                    id="yearly_price"
                                    wire:model="yearlyPrice"
                                    step="0.01"
                                    class="shadow-sm focus:ring-emerald-500 focus:border-emerald-500 block w-full sm:text-sm border-gray-300 rounded-md dark:bg-zinc-800 dark:border-zinc-700"
                                />
                            </div>
                            @error('yearlyPrice')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Limits -->
                    <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-3">
                        <div>
                            <label for="max_lists" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Max Lists
                            </label>
                            <div class="mt-1">
                                <input
                                    type="number"
                                    id="max_lists"
                                    wire:model="maxLists"
                                    class="shadow-sm focus:ring-emerald-500 focus:border-emerald-500 block w-full sm:text-sm border-gray-300 rounded-md dark:bg-zinc-800 dark:border-zinc-700"
                                />
                            </div>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Use -1 for unlimited</p>
                            @error('maxLists')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="max_urls_per_list" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Max URLs Per List
                            </label>
                            <div class="mt-1">
                                <input
                                    type="number"
                                    id="max_urls_per_list"
                                    wire:model="maxUrlsPerList"
                                    class="shadow-sm focus:ring-emerald-500 focus:border-emerald-500 block w-full sm:text-sm border-gray-300 rounded-md dark:bg-zinc-800 dark:border-zinc-700"
                                />
                            </div>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Use -1 for unlimited</p>
                            @error('maxUrlsPerList')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="max_team_members" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Max Team Members
                            </label>
                            <div class="mt-1">
                                <input
                                    type="number"
                                    id="max_team_members"
                                    wire:model="maxTeamMembers"
                                    class="shadow-sm focus:ring-emerald-500 focus:border-emerald-500 block w-full sm:text-sm border-gray-300 rounded-md dark:bg-zinc-800 dark:border-zinc-700"
                                />
                            </div>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Use -1 for unlimited</p>
                            @error('maxTeamMembers')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Features -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Features
                        </label>
                        <div class="mt-1">
                            <div class="flex space-x-2">
                                <input
                                    type="text"
                                    wire:model="feature"
                                    wire:keydown.enter.prevent="addFeature"
                                    class="shadow-sm focus:ring-emerald-500 focus:border-emerald-500 block w-full sm:text-sm border-gray-300 rounded-md dark:bg-zinc-800 dark:border-zinc-700"
                                    placeholder="Add a feature..."
                                />
                                <button
                                    type="button"
                                    wire:click="addFeature"
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500"
                                >
                                    Add
                                </button>
                            </div>

                            <div class="mt-2 space-y-2">
                                @foreach($features as $index => $feat)
                                    <div class="flex items-center justify-between bg-gray-50 dark:bg-zinc-900/50 p-2 rounded">
                                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ $feat }}</span>
                                        <button
                                            type="button"
                                            wire:click="removeFeature({{ $index }})"
                                            class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                        >
                                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                            @error('features')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2">
                        <div class="flex items-center">
                            <input
                                type="checkbox"
                                id="is_active"
                                wire:model="isActive"
                                class="h-4 w-4 text-emerald-600 focus:ring-emerald-500 border-gray-300 rounded dark:bg-zinc-800 dark:border-zinc-700"
                            />
                            <label for="is_active" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                                Active
                            </label>
                        </div>

                        <div class="flex items-center">
                            <input
                                type="checkbox"
                                id="is_featured"
                                wire:model="isFeatured"
                                class="h-4 w-4 text-emerald-600 focus:ring-emerald-500 border-gray-300 rounded dark:bg-zinc-800 dark:border-zinc-700"
                            />
                            <label for="is_featured" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                                Featured
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-5">
                <div class="flex justify-end space-x-3">
                    <a
                        href="{{ route('admin.plans.index') }}"
                        class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 dark:bg-zinc-800 dark:border-zinc-700 dark:text-gray-300"
                    >
                        Cancel
                    </a>
                    <button
                        type="submit"
                        class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500"
                    >
                        Create Plan
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
