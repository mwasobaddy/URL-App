<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public string $password = '';

    /**
     * Delete the currently authenticated user.
     */
    public function deleteUser(Logout $logout): void
    {
        $this->validate([
            'password' => ['required', 'string', 'current_password'],
        ]);

        tap(Auth::user(), $logout(...))->delete();

        $this->redirect('/', navigate: true);
    }
}; ?>

<section class="mt-10 space-y-6">
    <div class="relative mb-5">
        <div class="flex items-center space-x-3">
            <flux:icon name="exclamation-triangle" class="size-5 text-red-600 dark:text-red-500" />
            <flux:heading class="text-red-600 dark:text-red-500">{{ __('Delete account') }}</flux:heading>
        </div>
        <flux:subheading class="text-red-700/80 dark:text-red-400/80">{{ __('Delete your account and all of its resources') }}</flux:subheading>
    </div>

    <div class="bg-gradient-to-r from-red-50 to-red-100/70 dark:from-red-950/40 dark:to-red-900/30 p-5 rounded-lg border border-red-200/70 dark:border-red-800/50">
        <p class="text-sm text-red-700 dark:text-red-400">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
        </p>
    </div>

    <flux:modal.trigger name="confirm-user-deletion">
        <flux:button 
            variant="danger" 
            x-data="" 
            x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
            class="mt-3 bg-gradient-to-r from-red-500 to-rose-500 hover:from-red-600 hover:to-rose-600 border-red-600 shadow-sm hover:shadow-md transition-all duration-300"
        >
            {{ __('Delete account') }}
        </flux:button>
    </flux:modal.trigger>

    <flux:modal name="confirm-user-deletion" :show="$errors->isNotEmpty()" focusable class="max-w-lg">
        <form wire:submit="deleteUser" class="space-y-6">
            <div>
                <flux:heading size="lg" class="text-red-600 dark:text-red-500">{{ __('Are you sure you want to delete your account?') }}</flux:heading>

                <flux:subheading class="text-red-700/80 dark:text-red-400/80">
                    {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
                </flux:subheading>
            </div>

            <div class="bg-gradient-to-r from-red-50 to-red-100/70 dark:from-red-950/40 dark:to-red-900/30 p-4 rounded-lg border border-red-200/70 dark:border-red-800/50">
                <flux:input 
                    wire:model="password" 
                    :label="__('Password')" 
                    type="password"
                    class="focus:ring-red-500 focus:border-red-500 dark:focus:ring-red-500 dark:focus:border-red-500"
                />
            </div>

            <div class="flex justify-end space-x-2 rtl:space-x-reverse">
                <flux:modal.close>
                    <flux:button variant="filled">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>

                <flux:button 
                    variant="danger" 
                    type="submit"
                    class="bg-gradient-to-r from-red-500 to-rose-500 hover:from-red-600 hover:to-rose-600 shadow-sm hover:shadow-md transition-all duration-300"
                >
                    {{ __('Delete account') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>
</section>
