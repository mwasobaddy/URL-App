<?php

use function Livewire\Volt\{state, uses};
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;

uses(WithPagination::class);

state(['unreadCount' => 0]);

$getNotificationsProperty = function () {
    return Auth::user()->notifications()->latest()->paginate(10);
};

$markAsRead = function ($notificationId) {
    $notification = Auth::user()->notifications()->findOrFail($notificationId);
    $notification->markAsRead();
    $this->unreadCount = Auth::user()->unreadNotifications()->count();
};

$markAllAsRead = function () {
    Auth::user()->unreadNotifications->markAsRead();
    $this->unreadCount = 0;
}; ?>

<!-- Main container with glass morphism effect -->
<div class="max-w-4xl mx-auto backdrop-blur-sm bg-white/90 dark:bg-zinc-800/90 shadow-xl rounded-3xl p-6 lg:p-8 mt-8 border border-gray-100/40 dark:border-neutral-700/50">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-8">
        <div class="relative">
            <h2 class="text-3xl md:text-4xl font-extrabold tracking-tight">
                <span class="bg-clip-text text-transparent bg-gradient-to-br from-emerald-500 to-teal-400">
                    Notifications
                </span>
            </h2>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 max-w-md">
                Stay updated with your URL lists and collaborator activities
            </p>
            <!-- Decorative element -->
            <div class="absolute -bottom-3 left-0 h-1 w-16 bg-gradient-to-r from-emerald-500 to-teal-400 rounded-full"></div>
        </div>
        
        @if($unreadCount > 0)
            <div class="mt-4 sm:mt-0">
                <button 
                    wire:click="markAllAsRead" 
                    class="inline-flex items-center px-4 py-2 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 rounded-xl text-sm font-medium hover:bg-emerald-200 dark:hover:bg-emerald-900/50 transition-all duration-200"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Mark all as read
                </button>
            </div>
        @endif
    </div>

    <!-- Notifications List -->
    <div class="space-y-4">
        @forelse($this->notifications as $notification)
            <div class="relative bg-white dark:bg-zinc-800/50 rounded-xl border border-gray-100 dark:border-neutral-700/50 overflow-hidden transition-all duration-200 hover:border-emerald-200 dark:hover:border-emerald-700/50 {{ !$notification->read_at ? 'ring-2 ring-emerald-500/10' : '' }}">
                <div class="p-4 sm:p-6">
                    @if($notification->type === 'App\\Notifications\\AccessRequestNotification')
                        <div class="flex gap-4">
                            <div class="flex-shrink-0">
                                <span class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-100 dark:bg-emerald-900/50">
                                    <svg class="h-6 w-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" />
                                    </svg>
                                </span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between">
                                    <p class="text-base font-medium text-gray-900 dark:text-white">
                                        Access Request
                                    </p>
                                    <div class="ml-4 flex-shrink-0">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ !$notification->read_at ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300' : 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300' }}">
                                            {{ $notification->read_at ? 'Read' : 'New' }}
                                        </span>
                                    </div>
                                </div>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                                    <span class="font-medium text-emerald-600 dark:text-emerald-400">{{ $notification->data['requester_name'] }}</span> 
                                    requested access to 
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $notification->data['list_name'] }}</span>
                                </p>
                                @if(isset($notification->data['message']))
                                    <div class="mt-2 text-sm text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-black/20 rounded-lg p-3 border border-gray-100 dark:border-gray-800">
                                        "{{ $notification->data['message'] }}"
                                    </div>
                                @endif
                                <div class="mt-3 flex items-center justify-between">
                                    <div class="flex items-center space-x-4">
                                        <span class="inline-flex items-center text-xs text-gray-500 dark:text-gray-400">
                                            <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            {{ $notification->created_at->diffForHumans() }}
                                        </span>
                                        @if(!$notification->read_at)
                                            <button 
                                                wire:click="markAsRead('{{ $notification->id }}')"
                                                class="inline-flex items-center text-xs font-medium text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300"
                                            >
                                                <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                Mark as read
                                            </button>
                                        @endif
                                    </div>
                                    <a 
                                        href="{{ route('lists.access', $notification->data['list_id']) }}" 
                                        class="inline-flex items-center px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-900/30 dark:hover:bg-emerald-900/50 text-emerald-700 dark:text-emerald-300 rounded-lg text-sm font-medium transition-colors duration-200"
                                        wire:navigate
                                    >
                                        Review request
                                        <svg class="ml-2 h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @elseif($notification->type === 'App\\Notifications\\AccessResponseNotification')
                        <div class="flex gap-4">
                            <div class="flex-shrink-0">
                                <span class="inline-flex h-12 w-12 items-center justify-center rounded-xl {{ $notification->data['status'] === 'approved' ? 'bg-emerald-100 dark:bg-emerald-900/50' : 'bg-red-100 dark:bg-red-900/50' }}">
                                    @if($notification->data['status'] === 'approved')
                                        <svg class="h-6 w-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    @else
                                        <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    @endif
                                </span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between">
                                    <p class="text-base font-medium text-gray-900 dark:text-white">
                                        Access Response
                                    </p>
                                    <div class="ml-4 flex-shrink-0">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ !$notification->read_at ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300' : 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300' }}">
                                            {{ $notification->read_at ? 'Read' : 'New' }}
                                        </span>
                                    </div>
                                </div>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                                    <span class="font-medium text-emerald-600 dark:text-emerald-400">{{ $notification->data['owner_name'] }}</span>
                                    {{ $notification->data['status'] === 'approved' ? 'approved' : 'rejected' }} your request to access
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $notification->data['list_name'] }}</span>
                                </p>
                                <div class="mt-3 flex items-center justify-between">
                                    <div class="flex items-center space-x-4">
                                        <span class="inline-flex items-center text-xs text-gray-500 dark:text-gray-400">
                                            <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            {{ $notification->created_at->diffForHumans() }}
                                        </span>
                                        @if(!$notification->read_at)
                                            <button 
                                                wire:click="markAsRead('{{ $notification->id }}')"
                                                class="inline-flex items-center text-xs font-medium text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300"
                                            >
                                                <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                Mark as read
                                            </button>
                                        @endif
                                    </div>
                                    @if($notification->data['status'] === 'approved')
                                        <a 
                                            href="{{ route('lists.show', $notification->data['list_id']) }}" 
                                            class="inline-flex items-center px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-900/30 dark:hover:bg-emerald-900/50 text-emerald-700 dark:text-emerald-300 rounded-lg text-sm font-medium transition-colors duration-200"
                                            wire:navigate
                                        >
                                            View list
                                            <svg class="ml-2 h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                            </svg>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="text-center py-12">
                <div class="w-20 h-20 mx-auto bg-gray-50 dark:bg-gray-800/50 rounded-full flex items-center justify-center mb-4">
                    <svg class="h-10 w-10 text-gray-300 dark:text-gray-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-1">No notifications</h3>
                <p class="text-gray-500 dark:text-gray-400">You're all caught up! Check back later for new notifications.</p>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($this->notifications->hasPages())
        <div class="mt-6">
            <div class="bg-white/70 dark:bg-zinc-800/70 backdrop-blur-sm rounded-xl py-3 px-4 shadow-sm border border-gray-100/50 dark:border-neutral-700/50">
                {{ $this->notifications->links(data: ['scrollTo' => false]) }}
            </div>
        </div>
    @endif
</div>