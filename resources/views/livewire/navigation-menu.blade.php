<div>
    <nav x-data="{ open: false }" class="bg-white dark:bg-zinc-800 border-b border-emerald-100 dark:border-emerald-900/50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <!-- Logo -->
                    <div class="shrink-0 flex items-center">
                        <a href="{{ route('dashboard') }}" wire:navigate>
                            <div class="size-10 rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-700 dark:from-emerald-600 dark:to-emerald-800 shadow-md"></div>
                        </a>
                    </div>

                    <!-- Navigation Links -->
                    <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" wire:navigate>
                            {{ __('Dashboard') }}
                        </x-nav-link>
                        
                        <x-nav-link :href="route('lists.dashboard')" :active="request()->routeIs('lists.*')" wire:navigate>
                            {{ __('My Lists') }}
                        </x-nav-link>
                        
                        <x-nav-link :href="route('subscription.manage')" :active="request()->routeIs('subscription.*')" wire:navigate>
                            {{ __('Subscription') }}
                        </x-nav-link>

                        @role('admin')
                        <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.*')" wire:navigate>
                            {{ __('Admin') }}
                        </x-nav-link>
                        @endrole
                    </div>
                </div>

                <!-- Settings Dropdown -->
                <div class="hidden sm:flex sm:items-center sm:ms-6">
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-emerald-500 dark:text-emerald-400 bg-white dark:bg-zinc-800 hover:text-emerald-700 dark:hover:text-emerald-300 focus:outline-none transition ease-in-out duration-150">
                                <div>{{ Auth::user()->name }}</div>

                                <div class="ms-1">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile')" wire:navigate>
                                {{ __('Profile') }}
                            </x-dropdown-link>

                            <!-- Authentication -->
                            <button wire:click="logout" class="w-full text-start">
                                <x-dropdown-link>
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </button>
                        </x-slot>
                    </x-dropdown>
                </div>

                <!-- Hamburger -->
                <div class="-me-2 flex items-center sm:hidden">
                    <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-emerald-400 dark:text-emerald-500 hover:text-emerald-500 dark:hover:text-emerald-400 hover:bg-emerald-100 dark:hover:bg-emerald-900 focus:outline-none focus:bg-emerald-100 dark:focus:bg-emerald-900 focus:text-emerald-500 dark:focus:text-emerald-400 transition duration-150 ease-in-out">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Responsive Navigation Menu -->
        <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
            <div class="pt-2 pb-3 space-y-1">
                <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" wire:navigate>
                    {{ __('Dashboard') }}
                </x-responsive-nav-link>
                
                <x-responsive-nav-link :href="route('lists.dashboard')" :active="request()->routeIs('lists.*')" wire:navigate>
                    {{ __('My Lists') }}
                </x-responsive-nav-link>
                
                <x-responsive-nav-link :href="route('subscription.manage')" :active="request()->routeIs('subscription.*')" wire:navigate>
                    {{ __('Subscription') }}
                </x-responsive-nav-link>

                @role('admin')
                <x-responsive-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.*')" wire:navigate>
                    {{ __('Admin') }}
                </x-responsive-nav-link>
                @endrole
            </div>

            <!-- Responsive Settings Options -->
            <div class="pt-4 pb-1 border-t border-emerald-100 dark:border-emerald-900/50">
                <div class="px-4">
                    <div class="font-medium text-base text-emerald-800 dark:text-emerald-200">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-sm text-emerald-500">{{ Auth::user()->email }}</div>
                </div>

                <div class="mt-3 space-y-1">
                    <x-responsive-nav-link :href="route('profile')" wire:navigate>
                        {{ __('Profile') }}
                    </x-responsive-nav-link>

                    <!-- Authentication -->
                    <button wire:click="logout" class="w-full text-start">
                        <x-responsive-nav-link>
                            {{ __('Log Out') }}
                        </x-responsive-nav-link>
                    </button>
                </div>
            </div>
        </div>
    </nav>
</div>
