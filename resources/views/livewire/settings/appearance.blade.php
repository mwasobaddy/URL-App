<?php

use Livewire\Volt\Component;

new class extends Component {
    //
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Appearance')" :subheading=" __('Update the appearance settings for your account')">
        <div class="space-y-6">
            <flux:radio.group 
                x-data 
                variant="segmented" 
                x-model="$flux.appearance" 
                class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-zinc-900/60 dark:to-zinc-900/80 p-4 rounded-lg border border-gray-100/40 dark:border-neutral-700/50 h-auto"
            >
                <flux:radio value="light" icon="sun" class="hover:bg-white dark:hover:bg-zinc-800 py-2">{{ __('Light') }}</flux:radio>
                <flux:radio value="dark" icon="moon" class="hover:bg-white dark:hover:bg-zinc-800 py-2">{{ __('Dark') }}</flux:radio>
                <flux:radio value="system" icon="computer-desktop" class="hover:bg-white dark:hover:bg-zinc-800 py-2">{{ __('System') }}</flux:radio>
            </flux:radio.group>
            
            <div class="mt-4">
                <div class="flex items-center justify-between p-4 bg-emerald-50/50 dark:bg-emerald-900/20 rounded-lg border border-emerald-100 dark:border-emerald-900/30">
                    <div class="flex items-center space-x-3">
                        <flux:icon name="sparkles" class="size-5 text-emerald-600 dark:text-emerald-400" />
                        <span class="text-sm text-emerald-700 dark:text-emerald-300">{{ __('UI theme applies to this device only') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </x-settings.layout>
</section>
