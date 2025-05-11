<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;

new class extends Component {
    public string $name = '';
    public string $email = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],

            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($user->id)
            ],
        ]);

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->dispatch('profile-updated', name: $user->name);
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Profile')" :subheading="__('Update your name and email address')">
        <form wire:submit="updateProfileInformation" class="space-y-6">
            <div class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-zinc-900/60 dark:to-zinc-900/80 p-5 rounded-lg border border-gray-100/40 dark:border-neutral-700/50 mb-6">
                <div class="flex items-center space-x-4">
                    <div class="relative">
                        <div class="size-16 rounded-full bg-gradient-to-r from-emerald-500 to-teal-500 flex items-center justify-center text-white text-xl font-semibold">
                            {{ substr(Auth::user()->name, 0, 1) }}
                        </div>
                        <div class="absolute -bottom-1 -right-1 bg-white dark:bg-zinc-800 rounded-full p-1 border border-gray-200 dark:border-zinc-700">
                            <flux:icon name="camera" class="size-3 text-gray-500 dark:text-gray-400" />
                        </div>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-900 dark:text-white">{{ Auth::user()->name }}</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ Auth::user()->email }}</p>
                    </div>
                </div>
            </div>

            <flux:input 
                wire:model="name" 
                :label="__('Name')" 
                type="text" 
                required 
                autofocus 
                autocomplete="name"
                class="focus:ring-emerald-500 focus:border-emerald-500 dark:focus:ring-emerald-500 dark:focus:border-emerald-500" 
            />

            <div>
                <flux:input 
                    wire:model="email" 
                    :label="__('Email')" 
                    type="email" 
                    required 
                    autocomplete="email"
                    class="focus:ring-emerald-500 focus:border-emerald-500 dark:focus:ring-emerald-500 dark:focus:border-emerald-500"
                />

                @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail &&! auth()->user()->hasVerifiedEmail())
                    <div class="mt-2 p-3 bg-yellow-50 dark:bg-yellow-900/30 rounded-lg border border-yellow-100 dark:border-yellow-900/50">
                        <div class="flex items-center space-x-3">
                            <flux:icon name="exclamation-triangle" class="size-5 text-yellow-600 dark:text-yellow-400" />
                            <flux:text class="text-sm text-yellow-700 dark:text-yellow-300">
                                {{ __('Your email address is unverified.') }}
                            </flux:text>
                        </div>

                        <div class="mt-2 ml-8">
                            <flux:link class="text-sm cursor-pointer text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300" wire:click.prevent="resendVerificationNotification">
                                {{ __('Click here to re-send the verification email.') }}
                            </flux:link>
                        </div>

                        @if (session('status') === 'verification-link-sent')
                            <div class="mt-2 ml-8 p-2 bg-green-50 dark:bg-green-900/30 rounded-lg border border-green-100 dark:border-green-900/50">
                                <flux:text class="text-xs font-medium text-green-600 dark:text-green-400">
                                    {{ __('A new verification link has been sent to your email address.') }}
                                </flux:text>
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            <div class="flex items-center justify-end gap-4 pt-4">
                <x-action-message class="me-3 px-3 py-1.5 bg-green-50 dark:bg-green-900/30 text-sm text-green-700 dark:text-green-300 rounded-md" on="profile-updated">
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

        <livewire:settings.delete-user-form />
    </x-settings.layout>
</section>
