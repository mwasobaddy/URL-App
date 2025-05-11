<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Volt\Component;

new class extends Component {
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Update the password for the currently authenticated user.
     */
    public function updatePassword(): void
    {
        try {
            $validated = $this->validate([
                'current_password' => ['required', 'string', 'current_password'],
                'password' => ['required', 'string', Password::defaults(), 'confirmed'],
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');

            throw $e;
        }

        Auth::user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');

        $this->dispatch('password-updated');
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Update password')" :subheading="__('Ensure your account is using a long, random password to stay secure')">
        <div class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-zinc-900/60 dark:to-zinc-900/80 p-4 rounded-lg border border-gray-100/40 dark:border-neutral-700/50 mb-6">
            <div class="flex items-center space-x-3">
                <flux:icon name="shield-check" class="size-5 text-emerald-600 dark:text-emerald-400" />
                <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('Password should be at least 8 characters and include a mix of letters, numbers, and symbols.') }}</span>
            </div>
        </div>

        <form wire:submit="updatePassword" class="space-y-6">
            <flux:input
                wire:model="current_password"
                :label="__('Current password')"
                type="password"
                required
                autocomplete="current-password"
                class="focus:ring-emerald-500 focus:border-emerald-500 dark:focus:ring-emerald-500 dark:focus:border-emerald-500"
            />
            
            <div class="border-t border-gray-200 dark:border-gray-700 my-6 pt-6">
                <flux:input
                    wire:model="password"
                    :label="__('New password')"
                    type="password"
                    required
                    autocomplete="new-password"
                    class="focus:ring-emerald-500 focus:border-emerald-500 dark:focus:ring-emerald-500 dark:focus:border-emerald-500"
                />
            </div>
            
            <flux:input
                wire:model="password_confirmation"
                :label="__('Confirm Password')"
                type="password"
                required
                autocomplete="new-password"
                class="focus:ring-emerald-500 focus:border-emerald-500 dark:focus:ring-emerald-500 dark:focus:border-emerald-500"
            />

            <div class="flex items-center justify-end gap-4 pt-4">
                <x-action-message class="me-3 px-3 py-1.5 bg-green-50 dark:bg-green-900/30 text-sm text-green-700 dark:text-green-300 rounded-md" on="password-updated">
                    {{ __('Saved.') }}
                </x-action-message>
                
                <flux:button 
                    variant="primary" 
                    type="submit" 
                    class="bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white shadow-sm transition-all duration-300 hover:shadow-md dark:from-emerald-600 dark:to-teal-600 dark:hover:from-emerald-500 dark:hover:to-teal-500"
                >
                    {{ __('Save') }}
                </flux:button>
            </div>
        </form>
    </x-settings.layout>
</section>
