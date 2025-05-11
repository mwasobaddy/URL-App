<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        @if(auth()->check() && !request()->routeIs('lists.public'))
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
                <div class="rounded-xl bg-emerald-50 dark:bg-emerald-900/30 p-3">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-600 dark:bg-emerald-500 text-white font-bold text-sm">
                            {{ auth()->user()->initials() }}
                        </span>
                        <div class="flex-1 min-w-0">
                            <div class="truncate font-medium text-emerald-900 dark:text-emerald-100">{{ auth()->user()->name }}</div>
                            <div class="truncate text-xs text-emerald-700 dark:text-emerald-300">{{ auth()->user()->email }}</div>
                        </div>
                    </div>
                    <div class="mt-3 flex items-center gap-2 text-sm">
                        <a href="{{ route('settings.profile') }}" class="inline-flex items-center gap-1 text-emerald-700 dark:text-emerald-300 hover:text-emerald-900 dark:hover:text-emerald-100" wire:navigate>
                            <svg class="size-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            Settings
                        </a>
                        <form method="POST" action="{{ route('logout') }}" class="flex-1 text-right">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-1 text-red-600 hover:text-red-700 dark:text-red-500 dark:hover:text-red-400">
                                <svg class="size-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" /></svg>
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </flux:sidebar>
        @else
        <!-- Simple header for guests and public list views -->
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
                            Available Links
                        </div>
                        <div class="space-y-1">

                            <a href="{{ route('login') }}" class="text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 dark:hover:text-emerald-300 font-medium" wire:navigate>Login</a>
                            <a href="{{ route('register') }}" class="inline-flex items-center justify-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700 dark:bg-emerald-500 dark:hover:bg-emerald-600 transition-colors" wire:navigate>Get Started</a>
                        </div>
                    </div>
                </div>
            </nav>
        </flux:sidebar>
        @endif

        {{ $slot }}

        @fluxScripts
        @livewireScripts
    </body>
</html>
