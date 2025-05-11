<?php

use Livewire\Volt\Component;
use WireUi\Traits\WireUiActions;

new class extends Component {
    use WireUiActions;

    public $notificationPreferences = [];
    
    public function mount()
    {
        $this->notificationPreferences = auth()->user()->notification_preferences ?? [
            'email_list_access' => true,
            'email_list_updates' => true,
            'browser_notifications' => false
        ];
    }

    public function updateNotificationPreferences()
    {
        $user = auth()->user();
        $user->notification_preferences = $this->notificationPreferences;
        $user->save();

        $this->notification()->success(
            title: 'Preferences Updated',
            description: 'Your notification preferences have been saved.'
        );
    }
}; ?>

<div class="max-w-4xl mx-auto backdrop-blur-sm bg-white/90 dark:bg-zinc-800/90 shadow-xl rounded-3xl p-6 lg:p-8 mt-8 border border-gray-100/40 dark:border-neutral-700/50">
    <!-- Header Section -->
    <div class="relative mb-8">
        <h2 class="text-3xl md:text-4xl font-extrabold tracking-tight">
            <span class="bg-clip-text text-transparent bg-gradient-to-br from-emerald-500 to-teal-400">
                Notification Settings
            </span>
        </h2>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 max-w-md">
            Customize how and when you receive notifications
        </p>
        <!-- Decorative element -->
        <div class="absolute -bottom-3 left-0 h-1 w-16 bg-gradient-to-r from-emerald-500 to-teal-400 rounded-full"></div>
    </div>

    <form wire:submit="updateNotificationPreferences" class="space-y-6">
        <!-- Email Notifications Section -->
        <div class="bg-white dark:bg-zinc-800/50 rounded-xl border border-gray-100 dark:border-neutral-700/50 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Email Notifications</h3>
            
            <div class="space-y-4">
                <!-- List Access Notifications -->
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">List Access Updates</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            Receive emails about access requests and collaborator changes
                        </p>
                    </div>
                    <button
                        type="button"
                        wire:click="$set('notificationPreferences.email_list_access', {{ !$notificationPreferences['email_list_access'] }})"
                        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200 ease-in-out {{ $notificationPreferences['email_list_access'] ? 'bg-emerald-500' : 'bg-gray-200 dark:bg-gray-700' }}"
                        role="switch"
                    >
                        <span
                            class="inline-block h-4 w-4 transform rounded-full bg-white shadow-sm ring-1 ring-gray-900/5 transition-transform duration-200 ease-in-out {{ $notificationPreferences['email_list_access'] ? 'translate-x-6' : 'translate-x-1' }}"
                        ></span>
                    </button>
                </div>

                <!-- List Updates Notifications -->
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">List Content Updates</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            Get notified when changes are made to lists you collaborate on
                        </p>
                    </div>
                    <button
                        type="button"
                        wire:click="$set('notificationPreferences.email_list_updates', {{ !$notificationPreferences['email_list_updates'] }})"
                        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200 ease-in-out {{ $notificationPreferences['email_list_updates'] ? 'bg-emerald-500' : 'bg-gray-200 dark:bg-gray-700' }}"
                        role="switch"
                    >
                        <span
                            class="inline-block h-4 w-4 transform rounded-full bg-white shadow-sm ring-1 ring-gray-900/5 transition-transform duration-200 ease-in-out {{ $notificationPreferences['email_list_updates'] ? 'translate-x-6' : 'translate-x-1' }}"
                        ></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Browser Notifications Section -->
        <div class="bg-white dark:bg-zinc-800/50 rounded-xl border border-gray-100 dark:border-neutral-700/50 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Browser Notifications</h3>
            
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">Enable Push Notifications</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Receive real-time notifications in your browser
                    </p>
                </div>
                <button
                    type="button"
                    wire:click="$set('notificationPreferences.browser_notifications', {{ !$notificationPreferences['browser_notifications'] }})"
                    class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200 ease-in-out {{ $notificationPreferences['browser_notifications'] ? 'bg-emerald-500' : 'bg-gray-200 dark:bg-gray-700' }}"
                    role="switch"
                >
                    <span
                        class="inline-block h-4 w-4 transform rounded-full bg-white shadow-sm ring-1 ring-gray-900/5 transition-transform duration-200 ease-in-out {{ $notificationPreferences['browser_notifications'] ? 'translate-x-6' : 'translate-x-1' }}"
                    ></span>
                </button>
            </div>
        </div>

        <!-- Save Button -->
        <div class="flex justify-end">
            <button
                type="submit"
                class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white rounded-xl transition-all duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-0.5"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
                Save Preferences
            </button>
        </div>
    </form>
</div>