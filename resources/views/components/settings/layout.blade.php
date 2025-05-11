<div class="flex items-start max-md:flex-col">
    <!-- Settings Navigation -->
    <div class="me-10 w-full pb-4 md:w-[220px]">
        <div class="backdrop-blur-sm bg-white/90 dark:bg-zinc-800/90 rounded-xl shadow-sm border border-gray-100/40 dark:border-neutral-700/50 overflow-hidden">
            <flux:navlist>
                <flux:navlist.item :href="route('settings.profile')" wire:navigate class="px-4 py-3 hover:bg-emerald-50/60 dark:hover:bg-emerald-900/20">
                    <div class="flex items-center">
                        <flux:icon name="user-circle" class="me-3 size-5 text-emerald-600 dark:text-emerald-400" />
                        {{ __('Profile') }}
                    </div>
                </flux:navlist.item>
                <flux:navlist.item :href="route('settings.password')" wire:navigate class="px-4 py-3 hover:bg-emerald-50/60 dark:hover:bg-emerald-900/20">
                    <div class="flex items-center">
                        <flux:icon name="lock-closed" class="me-3 size-5 text-emerald-600 dark:text-emerald-400" />
                        {{ __('Password') }}
                    </div>
                </flux:navlist.item>
                <flux:navlist.item :href="route('settings.appearance')" wire:navigate class="px-4 py-3 hover:bg-emerald-50/60 dark:hover:bg-emerald-900/20">
                    <div class="flex items-center">
                        <flux:icon name="paint-brush" class="me-3 size-5 text-emerald-600 dark:text-emerald-400" />
                        {{ __('Appearance') }}
                    </div>
                </flux:navlist.item>
            </flux:navlist>
        </div>
    </div>

    <flux:separator class="md:hidden" />

    <!-- Settings Content -->
    <div class="flex-1 self-stretch max-md:pt-6">
        <div class="backdrop-blur-sm bg-white/90 dark:bg-zinc-800/90 rounded-xl shadow-sm border border-gray-100/40 dark:border-neutral-700/50 p-6">
            <flux:heading class="text-gray-900 dark:text-white">{{ $heading ?? '' }}</flux:heading>
            <flux:subheading class="text-gray-600 dark:text-gray-300">{{ $subheading ?? '' }}</flux:subheading>

            <div class="mt-6 w-full max-w-lg">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
