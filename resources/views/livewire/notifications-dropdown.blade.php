<?php

use function Livewire\Volt\{state, mount, computed};
use Illuminate\Support\Facades\Auth;

state([
    'unreadCount' => 0,
    'showDropdown' => false,
]);

mount(function () {
    $this->loadNotifications();
});

$loadNotifications = function () {
    $this->unreadCount = Auth::user()->unreadNotifications()->count();
};

$toggleDropdown = function () {
    $this->showDropdown = !$this->showDropdown;
    if ($this->showDropdown) {
        $this->dispatch('dropdown-opened');
    }
};

$markAsRead = function ($notificationId) {
    try {
        $notification = Auth::user()->notifications()->findOrFail($notificationId);
        $notification->markAsRead();
        
        $this->dispatch('swal:toast', [
            'type' => 'success',
            'title' => 'Notification marked as read'
        ]);
    } catch (\Exception $e) {
        $this->dispatch('swal:toast', [
            'type' => 'error',
            'title' => 'Could not mark notification as read'
        ]);
    }

    $this->loadNotifications();
};

$markAllAsRead = function () {
    try {
        Auth::user()->unreadNotifications->markAsRead();
        
        $this->dispatch('swal:toast', [
            'type' => 'success',
            'title' => 'All notifications marked as read'
        ]);
    } catch (\Exception $e) {
        $this->dispatch('swal:toast', [
            'type' => 'error',
            'title' => 'Could not mark notifications as read'
        ]);
    }

    $this->loadNotifications();
};

$notifications = computed(function () {
    return Auth::user()
        ->notifications()
        ->latest()
        ->take(5)
        ->get();
});

?>

<a href="{{ route('notifications') }}" class="flex items-center gap-2 w-full px-3 py-2 text-sm font-medium text-emerald-700 dark:text-emerald-300 hover:text-emerald-900 dark:hover:text-emerald-100 hover:bg-emerald-100 dark:hover:bg-emerald-900/30 rounded-md transition-colors" wire:navigate>
    <div class="relative">
        <svg class="size-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
        </svg>
        @if($unreadCount > 0)
            <span class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-emerald-500 text-xs font-bold text-white ring-2 ring-white dark:ring-zinc-800">
                {{ $unreadCount }}
            </span>
        @endif
    </div>
    <span>Notifications</span>
</a>
