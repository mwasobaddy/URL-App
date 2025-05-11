<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky stashable class="border-e border-emerald-200 dark:border-emerald-800 bg-gradient-to-b from-white to-emerald-50/20 dark:from-neutral-950 dark:to-emerald-950/20">
            <div class="flex items-center gap-3 px-6 py-4 border-b border-emerald-100 dark:border-emerald-900">
                <div class="flex items-center justify-center size-10 rounded-xl bg-emerald-600 dark:bg-emerald-500">
                    <x-app-logo-icon class="size-6 fill-current text-white" />
                </div>
                <span class="font-bold text-xl text-emerald-950 dark:text-emerald-100 tracking-tight">URL-App</span>
            </div>

            <nav class="flex-1 px-4 py-6">
                <div class="space-y-6">
                    <!-- Main Navigation -->
                    <div class="space-y-2">
                        <div class="px-3 text-xs font-medium text-emerald-700 dark:text-emerald-400 uppercase tracking-wider">
                            Main
                        </div>
                        <div class="space-y-1">
                            <a href="{{ route('dashboard') }}" class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm {{ request()->routeIs('dashboard') ? 'bg-emerald-100 dark:bg-emerald-900/50 text-emerald-900 dark:text-emerald-100' : 'text-emerald-800 dark:text-emerald-200 hover:bg-emerald-50 dark:hover:bg-emerald-900/30' }}" wire:navigate>
                                <svg class="size-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" /></svg>
                                Dashboard
                            </a>
                            <a href="{{ route('lists.dashboard') }}" class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm {{ request()->routeIs('lists.*') ? 'bg-emerald-100 dark:bg-emerald-900/50 text-emerald-900 dark:text-emerald-100' : 'text-emerald-800 dark:text-emerald-200 hover:bg-emerald-50 dark:hover:bg-emerald-900/30' }}" wire:navigate>
                                <svg class="size-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" /></svg>
                                My URL Lists
                            </a>
                        </div>
                    </div>

                    <!-- Settings Navigation -->
                    <div class="space-y-2">
                        <div class="px-3 text-xs font-medium text-emerald-700 dark:text-emerald-400 uppercase tracking-wider">
                            Settings
                        </div>
                        <div class="space-y-1">
                            <a href="{{ route('settings.profile') }}" class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm {{ request()->routeIs('settings.profile') ? 'bg-emerald-100 dark:bg-emerald-900/50 text-emerald-900 dark:text-emerald-100' : 'text-emerald-800 dark:text-emerald-200 hover:bg-emerald-50 dark:hover:bg-emerald-900/30' }}" wire:navigate>
                                <svg class="size-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
                                Profile
                            </a>
                            <a href="{{ route('settings.password') }}" class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm {{ request()->routeIs('settings.password') ? 'bg-emerald-100 dark:bg-emerald-900/50 text-emerald-900 dark:text-emerald-100' : 'text-emerald-800 dark:text-emerald-200 hover:bg-emerald-50 dark:hover:bg-emerald-900/30' }}" wire:navigate>
                                <svg class="size-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" /></svg>
                                Password
                            </a>
                            <a href="{{ route('settings.appearance') }}" class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm {{ request()->routeIs('settings.appearance') ? 'bg-emerald-100 dark:bg-emerald-900/50 text-emerald-900 dark:text-emerald-100' : 'text-emerald-800 dark:text-emerald-200 hover:bg-emerald-50 dark:hover:bg-emerald-900/30' }}" wire:navigate>
                                <svg class="size-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.098 19.902a3.75 3.75 0 005.304 0l6.401-6.402M6.75 21A3.75 3.75 0 013 17.25V4.125C3 3.504 3.504 3 4.125 3h5.25c.621 0 1.125.504 1.125 1.125v4.072M6.75 21a3.75 3.75 0 003.75-3.75V8.197M6.75 21h13.125c.621 0 1.125-.504 1.125-1.125v-5.25c0-.621-.504-1.125-1.125-1.125h-4.072M10.5 8.197l2.88-2.88c.438-.439 1.15-.439 1.59 0l3.712 3.713c.44.44.44 1.152 0 1.59l-2.879 2.88M6.75 17.25h.008v.008H6.75v-.008z" /></svg>
                                Appearance
                            </a>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="space-y-2">
                        <div class="px-3 text-xs font-medium text-emerald-700 dark:text-emerald-400 uppercase tracking-wider">
                            Actions
                        </div>
                        <div class="space-y-1">
                            <a href="{{ route('lists.create') }}" class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium bg-emerald-600 hover:bg-emerald-700 text-white" wire:navigate>
                                <svg class="size-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                                Create New List
                            </a>
                        </div>
                    </div>
                </div>
            </nav>

            <div class="mt-auto px-4 pb-4">
                <div class="rounded-xl bg-emerald-50 dark:bg-emerald-900/30 p-4">
                    <!-- Profile Info -->
                    <div class="flex items-center gap-3 pb-4 border-b border-emerald-200/50 dark:border-emerald-800/50">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-600 dark:bg-emerald-500 text-white font-bold text-sm">
                            {{ auth()->user()->initials() }}
                        </span>
                        <div class="flex-1 min-w-0">
                            <div class="truncate font-medium text-emerald-900 dark:text-emerald-100">{{ auth()->user()->name }}</div>
                            <div class="truncate text-xs text-emerald-700 dark:text-emerald-300">{{ auth()->user()->email }}</div>
                        </div>
                    </div>

                    <!-- Navigation Actions -->
                    <div class="pt-4 space-y-1">

                        <!-- Notifications Container with Fixed Position Context -->
                        <div class="relative">
                            <livewire:notifications-dropdown />
                        </div>

                        <!-- Logout -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="flex items-center gap-2 w-full px-3 py-2 text-sm font-medium text-red-600 hover:text-red-700 dark:text-red-500 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-md transition-colors">
                                <svg class="size-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                                </svg>
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </flux:sidebar>

        {{ $slot }}

        @fluxScripts
        @livewireScripts
    </body>
</html>
