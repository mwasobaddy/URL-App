<?php

use Livewire\Volt\Component;
use App\Models\User;
use App\Models\UrlList;
use App\Models\Subscription;

new class extends Component {
    public function with(): array 
    {
        return [
            'totalUsers' => User::count(),
            'totalLists' => UrlList::count(),
            'totalSubscriptions' => Subscription::count(),
            'recentUsers' => User::latest()->take(5)->get(),
            'recentLists' => UrlList::with('user')->latest()->take(5)->get(),
            'recentSubscriptions' => Subscription::with('user')->latest()->take(5)->get(),
        ];
    }
}; ?>

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900 dark:text-gray-100">
                <h1 class="text-2xl font-semibold mb-6">Admin Dashboard</h1>
                
                <!-- Stats Overview -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div class="bg-blue-100 dark:bg-blue-800 p-6 rounded-lg">
                        <h3 class="text-lg font-semibold mb-2">Total Users</h3>
                        <p class="text-3xl">{{ $totalUsers }}</p>
                    </div>
                    <div class="bg-green-100 dark:bg-green-800 p-6 rounded-lg">
                        <h3 class="text-lg font-semibold mb-2">Total Lists</h3>
                        <p class="text-3xl">{{ $totalLists }}</p>
                    </div>
                    <div class="bg-purple-100 dark:bg-purple-800 p-6 rounded-lg">
                        <h3 class="text-lg font-semibold mb-2">Active Subscriptions</h3>
                        <p class="text-3xl">{{ $totalSubscriptions }}</p>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Recent Users -->
                    <div class="bg-white dark:bg-gray-700 p-6 rounded-lg shadow">
                        <h3 class="text-lg font-semibold mb-4">Recent Users</h3>
                        <div class="space-y-4">
                            @foreach($recentUsers as $user)
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-medium">{{ $user->name }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $user->email }}</p>
                                </div>
                                <span class="text-xs text-gray-500">{{ $user->created_at->diffForHumans() }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Recent Lists -->
                    <div class="bg-white dark:bg-gray-700 p-6 rounded-lg shadow">
                        <h3 class="text-lg font-semibold mb-4">Recent Lists</h3>
                        <div class="space-y-4">
                            @foreach($recentLists as $list)
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-medium">{{ $list->name }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">by {{ $list->user->name }}</p>
                                </div>
                                <span class="text-xs text-gray-500">{{ $list->created_at->diffForHumans() }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Recent Subscriptions -->
                    <div class="bg-white dark:bg-gray-700 p-6 rounded-lg shadow">
                        <h3 class="text-lg font-semibold mb-4">Recent Subscriptions</h3>
                        <div class="space-y-4">
                            @foreach($recentSubscriptions as $subscription)
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-medium">{{ $subscription->user->name }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $subscription->plan_name }}</p>
                                </div>
                                <span class="text-xs text-gray-500">{{ $subscription->created_at->diffForHumans() }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
