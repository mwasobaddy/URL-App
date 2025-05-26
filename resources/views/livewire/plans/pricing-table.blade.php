<?php

use function Livewire\Volt\{state, mount, computed};
use App\Models\Plan;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('Subscription Plans')]

state([
    'interval' => 'monthly',
    'plans' => [],
]);

mount(function () {
    $this->loadPlans();
});

$loadPlans = function () {
    $this->plans = Plan::where('is_active', true)
        ->orderBy('monthly_price')
        ->get();
};

$toggleInterval = function () {
    $this->interval = $this->interval === 'monthly' ? 'yearly' : 'monthly';
};

?>

<div>
    <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <!-- Interval Toggle -->
        <div class="sm:flex sm:flex-col sm:align-center mb-12">
            <div class="relative self-center mt-6 rounded-lg p-0.5 flex sm:mt-8 bg-emerald-100 dark:bg-emerald-900">
                <button type="button" @class([
                    'relative w-1/2 rounded-md py-2 text-sm font-medium whitespace-nowrap focus:outline-none focus:z-10 sm:w-auto sm:px-8',
                    'bg-white dark:bg-zinc-800 text-emerald-700 dark:text-emerald-300 shadow-sm' => $interval === 'monthly',
                    'text-emerald-800 dark:text-emerald-200' => $interval !== 'monthly',
                ]) wire:click="$set('interval', 'monthly')">
                    Monthly billing
                </button>
                <button type="button" @class([
                    'relative w-1/2 rounded-md py-2 text-sm font-medium whitespace-nowrap focus:outline-none focus:z-10 sm:w-auto sm:px-8',
                    'bg-white dark:bg-zinc-800 text-emerald-700 dark:text-emerald-300 shadow-sm' => $interval === 'yearly',
                    'text-emerald-800 dark:text-emerald-200' => $interval !== 'yearly',
                ]) wire:click="$set('interval', 'yearly')">
                    Yearly billing
                </button>
            </div>
        </div>

        <!-- Plans Grid -->
        <div class="space-y-12 lg:grid lg:grid-cols-3 lg:gap-x-8 lg:space-y-0">
            @foreach ($plans as $plan)
            <div @class([
                'relative p-8 bg-white dark:bg-zinc-800 border rounded-2xl shadow-sm flex flex-col',
                'border-emerald-500 dark:border-emerald-400' => $plan->is_featured,
                'border-emerald-100 dark:border-emerald-900/50' => !$plan->is_featured,
            ])>
                @if ($plan->is_featured)
                <div class="absolute -top-4 left-1/2 transform -translate-x-1/2">
                    <span class="inline-flex rounded-full bg-emerald-500 dark:bg-emerald-400 px-4 py-1 text-sm font-semibold text-white dark:text-emerald-900">
                        Most Popular
                    </span>
                </div>
                @endif

                <div class="mb-4">
                    <h3 class="text-xl font-semibold text-emerald-900 dark:text-emerald-100">{{ $plan->name }}</h3>
                    <p class="mt-4 text-sm text-emerald-700 dark:text-emerald-300">{{ $plan->description }}</p>
                </div>

                <div class="mb-8">
                    <p class="flex items-baseline text-emerald-900 dark:text-emerald-100">
                        <span class="text-4xl font-bold tracking-tight">${{ number_format($interval === 'yearly' ? $plan->yearly_price : $plan->monthly_price, 2) }}</span>
                        <span class="ml-1 text-xl font-semibold">/{{ $interval === 'yearly' ? 'year' : 'month' }}</span>
                    </p>
                    @if ($interval === 'yearly' && $plan->getYearlySavingsPercentage() > 0)
                    <p class="mt-2 text-sm text-emerald-700 dark:text-emerald-300">
                        Save {{ $plan->getYearlySavingsPercentage() }}% with yearly billing
                    </p>
                    @endif
                </div>

                <ul role="list" class="mt-6 space-y-4 flex-1">
                    @foreach ($plan->features as $feature)
                    <li class="flex">
                        <svg class="flex-shrink-0 w-5 h-5 text-emerald-500 dark:text-emerald-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        <span class="ml-3 text-sm text-emerald-700 dark:text-emerald-300">{{ $feature }}</span>
                    </li>
                    @endforeach
                </ul>

                @auth
                    @if ($plan->monthly_price === 0)
                        <a href="{{ route('dashboard') }}" class="mt-8 block w-full bg-emerald-50 dark:bg-emerald-900/30 py-2 text-sm font-semibold text-emerald-700 dark:text-emerald-300 text-center rounded-md hover:bg-emerald-100 dark:hover:bg-emerald-900/50">
                            Current Plan
                        </a>
                    @else
                        <a href="{{ route('subscribe', ['plan' => $plan->id, 'interval' => $interval]) }}" class="mt-8 block w-full bg-emerald-600 dark:bg-emerald-500 py-2 text-sm font-semibold text-white text-center rounded-md hover:bg-emerald-700 dark:hover:bg-emerald-600">
                            Subscribe
                        </a>
                    @endif
                @else
                    <a href="{{ route('register') }}" class="mt-8 block w-full bg-emerald-600 dark:bg-emerald-500 py-2 text-sm font-semibold text-white text-center rounded-md hover:bg-emerald-700 dark:hover:bg-emerald-600">
                        Get started
                    </a>
                @endauth
            </div>
            @endforeach
        </div>
    </div>
</div>
