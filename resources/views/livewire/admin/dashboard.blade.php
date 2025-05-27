<?php

use Livewire\Volt\Component;
use App\Models\User;
use App\Models\UrlList;
use App\Models\Subscription;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

new #[Layout('components.layouts.app')] #[Title('Manage Subscription')] class extends Component {
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
<!-- Hero Section with Enhanced Glass Morphism -->
<div class="max-w-7xl mx-auto backdrop-blur-sm bg-white/80 dark:bg-neutral-800/80 shadow-xl rounded-3xl p-6 lg:p-8 mt-8 border border-gray-100/40 dark:border-neutral-700/50 transition-all duration-300 relative overflow-hidden">
    <!-- Decorative Background Elements -->
    <div class="absolute top-0 right-0 w-96 h-96 bg-gradient-to-bl from-blue-400/10 to-transparent rounded-full blur-3xl -z-10"></div>
    <div class="absolute bottom-0 left-0 w-80 h-80 bg-gradient-to-tr from-purple-400/10 to-transparent rounded-full blur-3xl -z-10"></div>
    
    <!-- Header Section -->
    <div class="relative mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight">
                    <span class="bg-clip-text text-transparent bg-gradient-to-r from-blue-600 via-purple-600 to-emerald-600">
                        Admin Dashboard
                    </span>
                </h1>
                <p class="mt-2 text-gray-600 dark:text-gray-300 max-w-2xl">
                    Monitor platform activity, manage users, and track subscription metrics in real-time.
                </p>
                <!-- Animated decorative element -->
                <div class="absolute -bottom-3 left-0 h-1 w-20 bg-gradient-to-r from-blue-500 via-purple-500 to-emerald-500 rounded-full animate-pulse"></div>
            </div>
            
            <!-- Quick Actions Dropdown -->
            <div class="relative">
                <button class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-500 to-purple-500 hover:from-blue-600 hover:to-purple-600 text-white rounded-xl text-sm font-medium transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />
                    </svg>
                    Quick Actions
                </button>
            </div>
        </div>
    </div>

    <!-- Enhanced Stats Overview with Modern Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Total Users Card -->
        <div class="group relative overflow-hidden bg-white/70 dark:bg-neutral-700/30 rounded-2xl p-6 border border-gray-300/60 dark:border-neutral-700/40 backdrop-blur-sm transition-all duration-300 hover:shadow-xl hover:scale-105">
            <!-- Card Background Gradient -->
            <div class="absolute inset-0 bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 opacity-80 group-hover:opacity-100 transition-opacity duration-300"></div>
            <div class="absolute -bottom-6 -right-6 w-32 h-32 bg-blue-200 dark:bg-blue-800/30 rounded-full transform group-hover:scale-110 transition-transform duration-500"></div>
            
            <div class="relative">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 rounded-xl bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-.5a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                        </svg>
                    </div>
                    <div class="text-sm font-medium text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/30 px-3 py-1 rounded-full">
                        +12% this month
                    </div>
                </div>
                
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-1">Total Users</h3>
                    <p class="text-4xl font-bold text-blue-600 dark:text-blue-400 mb-2">
                        {{ number_format($totalUsers) }}
                    </p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Registered members</p>
                </div>
            </div>
        </div>

        <!-- Total Lists Card -->
        <div class="group relative overflow-hidden bg-white/70 dark:bg-neutral-700/30 rounded-2xl p-6 border border-gray-300/60 dark:border-neutral-700/40 backdrop-blur-sm transition-all duration-300 hover:shadow-xl hover:scale-105">
            <!-- Card Background Gradient -->
            <div class="absolute inset-0 bg-gradient-to-br from-emerald-50 to-emerald-100 dark:from-emerald-900/20 dark:to-emerald-800/20 opacity-80 group-hover:opacity-100 transition-opacity duration-300"></div>
            <div class="absolute -bottom-6 -right-6 w-32 h-32 bg-emerald-200 dark:bg-emerald-800/30 rounded-full transform group-hover:scale-110 transition-transform duration-500"></div>
            
            <div class="relative">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 rounded-xl bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <div class="text-sm font-medium text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/30 px-3 py-1 rounded-full">
                        +8% this week
                    </div>
                </div>
                
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-1">Total Lists</h3>
                    <p class="text-4xl font-bold text-emerald-600 dark:text-emerald-400 mb-2">
                        {{ number_format($totalLists) }}
                    </p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">URL collections</p>
                </div>
            </div>
        </div>

        <!-- Active Subscriptions Card -->
        <div class="group relative overflow-hidden bg-white/70 dark:bg-neutral-700/30 rounded-2xl p-6 border border-gray-300/60 dark:border-neutral-700/40 backdrop-blur-sm transition-all duration-300 hover:shadow-xl hover:scale-105">
            <!-- Card Background Gradient -->
            <div class="absolute inset-0 bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20 opacity-80 group-hover:opacity-100 transition-opacity duration-300"></div>
            <div class="absolute -bottom-6 -right-6 w-32 h-32 bg-purple-200 dark:bg-purple-800/30 rounded-full transform group-hover:scale-110 transition-transform duration-500"></div>
            
            <div class="relative">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 rounded-xl bg-purple-100 dark:bg-purple-900/50 text-purple-600 dark:text-purple-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                    </div>
                    <div class="text-sm font-medium text-purple-600 dark:text-purple-400 bg-purple-50 dark:bg-purple-900/30 px-3 py-1 rounded-full">
                        +15% growth
                    </div>
                </div>
                
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-1">Active Subscriptions</h3>
                    <p class="text-4xl font-bold text-purple-600 dark:text-purple-400 mb-2">
                        {{ number_format($totalSubscriptions) }}
                    </p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Premium accounts</p>
                </div>
            </div>
        </div>
    </div>
    <!-- Recent Activity Section with Enhanced Design -->
    <div class="max-w-7xl mx-auto my-8 backdrop-blur-sm bg-white/80 dark:bg-neutral-800/80 shadow-xl rounded-3xl p-6 lg:p-8 border border-gray-100/40 dark:border-neutral-700/50 transition-all duration-300 relative overflow-hidden">
        <!-- Decorative elements -->
        <div class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-bl from-pink-400/10 to-transparent rounded-full blur-3xl -z-10"></div>
        <div class="absolute bottom-0 left-0 w-80 h-80 bg-gradient-to-tr from-yellow-400/10 to-transparent rounded-full blur-3xl -z-10"></div>

        <div class="relative mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl md:text-3xl font-bold tracking-tight text-gray-800 dark:text-gray-200">
                        Recent Activity
                    </h2>
                    <p class="mt-2 text-gray-600 dark:text-gray-300">
                        Latest platform activities and user interactions
                    </p>
                    <div class="absolute -bottom-3 left-0 h-1 w-16 bg-gradient-to-r from-blue-500 to-purple-500 rounded-full animate-pulse"></div>
                </div>
                
                <!-- Filter Buttons -->
                <div class="flex space-x-2">
                    <button class="px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-300 bg-white/60 dark:bg-neutral-700/40 rounded-lg hover:bg-gray-50 dark:hover:bg-neutral-600/60 transition-colors duration-200">
                        Today
                    </button>
                    <button class="px-4 py-2 text-sm font-medium text-white bg-blue-500 rounded-lg hover:bg-blue-600 transition-colors duration-200">
                        This Week
                    </button>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Recent Users Section -->
            <div class="bg-white/60 dark:bg-neutral-700/30 rounded-2xl p-6 border border-gray-300/60 dark:border-neutral-700/40 backdrop-blur-sm transition-all duration-300 hover:shadow-lg">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 flex items-center">
                        <div class="p-2 rounded-lg bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-300 mr-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z" />
                            </svg>
                        </div>
                        Recent Users
                    </h3>
                    <a href="#" class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 font-medium flex items-center transition-colors duration-200">
                        View All
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                    </a>
                </div>
                
                <div class="space-y-4">
                    @forelse ($recentUsers as $user)
                        <div class="group flex-col flex items-start justify-between p-4 bg-white/70 dark:bg-neutral-800/50 rounded-xl hover:bg-gray-50 dark:hover:bg-neutral-700/70 transition-all duration-200 hover:shadow-md">
                            <div class="flex items-center space-x-3">
                                <div class="relative">
                                    <div class="flex-shrink-0 h-12 w-12 rounded-full bg-gradient-to-br from-blue-500 to-purple-500 text-white flex items-center justify-center text-sm font-medium shadow-lg">
                                        {{ substr($user->name, 0, 1) }}
                                    </div>
                                    <!-- Online status indicator -->
                                    <div class="absolute -bottom-1 -right-1 h-4 w-4 bg-green-400 border-2 border-white dark:border-neutral-800 rounded-full"></div>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-800 dark:text-gray-200 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors duration-200">
                                        {{ $user->name }}
                                    </p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $user->email }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span class="text-xs px-3 py-1 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-full font-medium">
                                    {{ $user->created_at->diffForHumans() }}
                                </span>
                                <button class="p-2 text-gray-400 hover:text-blue-500 dark:text-gray-500 dark:hover:text-blue-400 opacity-0 group-hover:opacity-100 transition-all duration-200 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/30">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                        <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8">
                            <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-.5a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                                </svg>
                            </div>
                            <p class="text-gray-500 dark:text-gray-400">No recent users</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Recent Lists Section -->
            <div class="bg-white/60 dark:bg-neutral-700/30 rounded-2xl p-6 border border-gray-300/60 dark:border-neutral-700/40 backdrop-blur-sm transition-all duration-300 hover:shadow-lg">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 flex items-center">
                        <div class="p-2 rounded-lg bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-300 mr-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z" />
                            </svg>
                        </div>
                        Recent Lists
                    </h3>
                    <a href="#" class="text-sm text-emerald-600 dark:text-emerald-400 hover:text-emerald-800 font-medium flex items-center transition-colors duration-200">
                        View All
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                    </a>
                </div>
                
                <div class="space-y-4">
                    @forelse ($recentLists as $list)
                        <div class="group flex items-center justify-between p-4 bg-white/70 dark:bg-neutral-800/50 rounded-xl hover:bg-gray-50 dark:hover:bg-neutral-700/70 transition-all duration-200 hover:shadow-md">
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0 h-12 w-12 rounded-full bg-gradient-to-br from-emerald-500 to-teal-500 text-white flex items-center justify-center text-sm font-medium shadow-lg">
                                    {{ substr($list->name, 0, 1) }}
                                </div>
                                <div>
                                    <p class="font-medium text-gray-800 dark:text-gray-200 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors duration-200">
                                        {{ Str::limit($list->name, 25) }}
                                    </p>
                                    <div class="flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-gray-400 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                                        </svg>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ $list->user->name }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span class="text-xs px-3 py-1 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 rounded-full font-medium">
                                    {{ $list->created_at->diffForHumans() }}
                                </span>
                                <button class="p-2 text-gray-400 hover:text-emerald-500 dark:text-gray-500 dark:hover:text-emerald-400 opacity-0 group-hover:opacity-100 transition-all duration-200 rounded-lg hover:bg-emerald-50 dark:hover:bg-emerald-900/30">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                        <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8">
                            <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                            <p class="text-gray-500 dark:text-gray-400">No recent lists</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Recent Subscriptions Section -->
            <div class="bg-white/60 dark:bg-neutral-700/30 rounded-2xl p-6 border border-gray-300/60 dark:border-neutral-700/40 backdrop-blur-sm transition-all duration-300 hover:shadow-lg">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 flex items-center">
                        <div class="p-2 rounded-lg bg-purple-100 dark:bg-purple-900/50 text-purple-600 dark:text-purple-300 mr-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z" />
                                <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        Recent Subscriptions
                    </h3>
                    <a href="#" class="text-sm text-purple-600 dark:text-purple-400 hover:text-purple-800 font-medium flex items-center transition-colors duration-200">
                        View All
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                    </a>
                </div>
                
                <div class="space-y-4">
                    @forelse ($recentSubscriptions as $subscription)
                        <div class="group flex items-center justify-between p-4 bg-white/70 dark:bg-neutral-800/50 rounded-xl hover:bg-gray-50 dark:hover:bg-neutral-700/70 transition-all duration-200 hover:shadow-md">
                            <div class="flex items-center space-x-3">
                                <div class="relative">
                                    <div class="flex-shrink-0 h-12 w-12 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 text-white flex items-center justify-center text-sm font-medium shadow-lg">
                                        {{ substr($subscription->user->name, 0, 1) }}
                                    </div>
                                    <!-- Premium badge -->
                                    <div class="absolute -bottom-1 -right-1 h-4 w-4 bg-yellow-400 border-2 border-white dark:border-neutral-800 rounded-full flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-2 w-2 text-white" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                    </div>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-800 dark:text-gray-200 group-hover:text-purple-600 dark:group-hover:text-purple-400 transition-colors duration-200">
                                        {{ $subscription->user->name }}
                                    </p>
                                    <div class="flex items-center">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400">
                                            {{ $subscription->plan_name }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span class="text-xs px-3 py-1 bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 rounded-full font-medium">
                                    {{ $subscription->created_at->diffForHumans() }}
                                </span>
                                <button class="p-2 text-gray-400 hover:text-purple-500 dark:text-gray-500 dark:hover:text-purple-400 opacity-0 group-hover:opacity-100 transition-all duration-200 rounded-lg hover:bg-purple-50 dark:hover:bg-purple-900/30">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                        <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8">
                            <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                </svg>
                            </div>
                            <p class="text-gray-500 dark:text-gray-400">No recent subscriptions</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

