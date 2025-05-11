<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky stashable class="border-e border-emerald-200 bg-white dark:border-emerald-800 dark:bg-neutral-950 min-h-screen flex flex-col">
            <div class="flex items-center gap-2 px-6 py-4 border-b border-emerald-100 dark:border-emerald-900">
                <x-app-logo class="h-8 w-8 text-emerald-600" />
                <span class="font-bold text-lg text-emerald-700 tracking-tight">URL-App</span>
            </div>
            <nav class="flex-1 px-4 py-6 space-y-2">
                <a href="{{ route('dashboard') }}" class="block px-4 py-2 rounded-lg text-gray-700 dark:text-gray-200 hover:bg-emerald-50 dark:hover:bg-emerald-900/30 font-medium transition" wire:navigate>Dashboard</a>
                <a href="{{ route('lists.dashboard') }}" class="block px-4 py-2 rounded-lg text-gray-700 dark:text-gray-200 hover:bg-emerald-50 dark:hover:bg-emerald-900/30 font-medium transition" wire:navigate>My URL Lists</a>
                <a href="{{ route('lists.create') }}" class="block px-4 py-2 rounded-lg text-emerald-700 dark:text-emerald-300 hover:bg-emerald-100 dark:hover:bg-emerald-900/50 font-semibold transition" wire:navigate>Create New List</a>
            </nav>
            <div class="mt-auto px-4 pb-4">
                <div class="flex gap-2 mb-2">
                    <a href="https://github.com/laravel/livewire-starter-kit" target="_blank" class="text-gray-400 hover:text-emerald-600" title="GitHub">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 .5C5.73.5.5 5.73.5 12c0 5.08 3.29 9.39 7.86 10.91.58.11.79-.25.79-.56v-2.02c-3.2.7-3.87-1.54-3.87-1.54-.53-1.34-1.3-1.7-1.3-1.7-1.06-.72.08-.71.08-.71 1.17.08 1.79 1.2 1.79 1.2 1.04 1.78 2.73 1.27 3.4.97.11-.75.41-1.27.75-1.56-2.55-.29-5.23-1.28-5.23-5.7 0-1.26.45-2.29 1.19-3.1-.12-.29-.52-1.46.11-3.05 0 0 .97-.31 3.18 1.18a11.1 11.1 0 0 1 2.9-.39c.98 0 1.97.13 2.9.39 2.2-1.49 3.17-1.18 3.17-1.18.63 1.59.23 2.76.11 3.05.74.81 1.19 1.84 1.19 3.1 0 4.43-2.69 5.41-5.25 5.7.42.36.8 1.09.8 2.2v3.26c0 .31.21.67.8.56C20.71 21.39 24 17.08 24 12c0-6.27-5.23-11.5-12-11.5z"/></svg>
                    </a>
                    <a href="https://laravel.com/docs/starter-kits#livewire" target="_blank" class="text-gray-400 hover:text-emerald-600" title="Docs">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75V19.5A2.25 2.25 0 0 0 4.5 21.75h15a2.25 2.25 0 0 0 2.25-2.25V6.75M2.25 6.75A2.25 2.25 0 0 1 4.5 4.5h15a2.25 2.25 0 0 1 2.25 2.25M2.25 6.75h19.5"/></svg>
                    </a>
                </div>
                @auth
                <div class="flex items-center gap-3 p-3 rounded-lg bg-emerald-50 dark:bg-emerald-900/30">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-emerald-200 text-emerald-800 font-bold">{{ auth()->user()->initials() }}</span>
                    <div class="flex-1">
                        <div class="font-semibold text-emerald-800 dark:text-emerald-200">{{ auth()->user()->name }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ auth()->user()->email }}</div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="ml-2 text-xs text-red-500 hover:underline">Logout</button>
                    </form>
                </div>
                <a href="{{ route('settings.profile') }}" class="block mt-2 text-xs text-gray-500 hover:text-emerald-700">Settings</a>
                @endauth
            </div>
        </flux:sidebar>

        {{ $slot }}

        @fluxScripts
        @livewireScripts
    </body>
</html>
