<?php

use function Livewire\Volt\{state, mount, uses};
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;

uses(WithPagination::class);

state(['unreadCount' => 0]);

mount(function () {
    $this->loadUnreadCount();
});

$loadUnreadCount = function () {
    $this->unreadCount = Auth::user()->unreadNotifications()->count();
};

$getNotificationsProperty = function () {
    return Auth::user()->notifications()->latest()->paginate(10);
};

$markAsRead = function ($notificationId) {
    $notification = Auth::user()->notifications()->findOrFail($notificationId);
    $notification->markAsRead();
    $this->loadUnreadCount();
};

$markAllAsRead = function () {
    Auth::user()->unreadNotifications->markAsRead();
    $this->loadUnreadCount();
}; ?>

<div class="max-w-4xl mx-auto p-4 sm:p-6 lg:p-8">
    <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-emerald-100 dark:border-emerald-800/50">
        <div class="border-b border-emerald-100 dark:border-emerald-800/50 p-4 sm:px-6">
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold text-emerald-900 dark:text-emerald-100">Notifications</h2>
                @if($unreadCount > 0)
                    <button wire:click="markAllAsRead" class="text-sm font-medium text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 dark:hover:text-emerald-300">
                        Mark all as read
                    </button>
                @endif
            </div>
        </div>

        <div class="divide-y divide-emerald-100 dark:divide-emerald-800/50">
            @forelse($this->notifications as $notification)
                <div class="p-4 sm:px-6 {{ !$notification->read_at ? 'bg-emerald-50 dark:bg-emerald-900/30' : '' }}">
                    @if($notification->type === 'App\\Notifications\\AccessRequestNotification')
                        <div class="flex gap-4">
                            <div class="flex-shrink-0">
                                <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100 dark:bg-emerald-900/50">
                                    <svg class="h-5 w-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" />
                                    </svg>
                                </span>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm text-emerald-900 dark:text-emerald-100">
                                    <span class="font-medium">{{ $notification->data['requester_name'] }}</span> requested access to 
                                    <span class="font-medium">{{ $notification->data['list_name'] }}</span>
                                </p>
                                @if(isset($notification->data['message']))
                                    <p class="mt-1 text-sm text-emerald-700 dark:text-emerald-300">
                                        "{{ $notification->data['message'] }}"
                                    </p>
                                @endif
                                <div class="mt-2 flex items-center justify-between">
                                    <p class="text-xs text-emerald-600 dark:text-emerald-400">
                                        {{ $notification->created_at->diffForHumans() }}
                                    </p>
                                    <div class="flex items-center gap-4">
                                        @if(!$notification->read_at)
                                            <button wire:click="markAsRead('{{ $notification->id }}')" class="text-sm font-medium text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 dark:hover:text-emerald-300">
                                                Mark as read
                                            </button>
                                        @endif
                                        <a href="{{ route('lists.access', $notification->data['list_id']) }}" class="text-sm font-medium text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 dark:hover:text-emerald-300" wire:navigate>
                                            Review request →
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @elseif($notification->type === 'App\\Notifications\\AccessResponseNotification')
                        <div class="flex gap-4">
                            <div class="flex-shrink-0">
                                <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg {{ $notification->data['status'] === 'approved' ? 'bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400' : 'bg-red-100 dark:bg-red-900/50 text-red-600 dark:text-red-400' }}">
                                    @if($notification->data['status'] === 'approved')
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                        </svg>
                                    @else
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    @endif
                                </span>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm text-emerald-900 dark:text-emerald-100">
                                    <span class="font-medium">{{ $notification->data['owner_name'] }}</span>
                                    {{ $notification->data['status'] === 'approved' ? 'approved' : 'rejected' }} your request to access
                                    <span class="font-medium">{{ $notification->data['list_name'] }}</span>
                                </p>
                                <div class="mt-2 flex items-center justify-between">
                                    <p class="text-xs text-emerald-600 dark:text-emerald-400">
                                        {{ $notification->created_at->diffForHumans() }}
                                    </p>
                                    <div class="flex items-center gap-4">
                                        @if(!$notification->read_at)
                                            <button wire:click="markAsRead('{{ $notification->id }}')" class="text-sm font-medium text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 dark:hover:text-emerald-300">
                                                Mark as read
                                            </button>
                                        @endif
                                        @if($notification->data['status'] === 'approved')
                                            <a href="{{ route('lists.show', $notification->data['list_id']) }}" class="text-sm font-medium text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 dark:hover:text-emerald-300" wire:navigate>
                                                View list →
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @empty
                <div class="p-4 sm:px-6 text-center">
                    <div class="py-12">
                        <svg class="mx-auto h-12 w-12 text-emerald-400" fill="none" stroke="currentColor" stroke-width="1">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                        </svg>
                        <p class="mt-2 text-sm text-emerald-700 dark:text-emerald-300">No notifications yet</p>
                    </div>
                </div>
            @endforelse
        </div>

        @if($this->notifications->hasPages())
            <div class="border-t border-emerald-100 dark:border-emerald-800/50 px-4 py-3 sm:px-6">
                {{ $this->notifications->links() }}
            </div>
        @endif
    </div>
</div>