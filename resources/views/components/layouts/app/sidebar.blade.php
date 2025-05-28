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
                            @if(auth()->user()->hasRole('admin'))
                                <a href="{{ route('admin.dashboard') }}" class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm {{ request()->routeIs('admin.dashboard') ? 'bg-emerald-100 dark:bg-emerald-900/50 text-emerald-900 dark:text-emerald-100' : 'text-emerald-800 dark:text-emerald-200 hover:bg-emerald-50 dark:hover:bg-emerald-900/30' }}" wire:navigate>
                                    <svg class="size-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" /></svg>
                                    Admin Dashboard
                                </a>
                            @endif
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
                    
                    @if(auth()->user()->hasRole('admin'))
                    <!-- Admin Navigation -->
                    <div class="space-y-2">
                        <div class="px-3 text-xs font-medium text-emerald-700 dark:text-emerald-400 uppercase tracking-wider">
                            Administration
                        </div>
                        <div class="space-y-1">
                            <a href="{{ route('admin.subscriptions.index') }}" class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm {{ request()->routeIs('admin.subscriptions*') ? 'bg-emerald-100 dark:bg-emerald-900/50 text-emerald-900 dark:text-emerald-100' : 'text-emerald-800 dark:text-emerald-200 hover:bg-emerald-50 dark:hover:bg-emerald-900/30' }}" wire:navigate>
                                <svg class="size-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m9 12h-6m3 0V9.75m-3 0V9"></path></svg>
                                Subscriptions
                            </a>
                            
                            <a href="{{ route('admin.users.index') }}" class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm {{ request()->routeIs('admin.users*') ? 'bg-emerald-100 dark:bg-emerald-900/50 text-emerald-900 dark:text-emerald-100' : 'text-emerald-800 dark:text-emerald-200 hover:bg-emerald-50 dark:hover:bg-emerald-900/30' }}" wire:navigate>
                                <svg class="size-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"></path></svg>
                                Users & Roles
                            </a>
                            
                            <a href="{{ route('admin.plans.index') }}" class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm {{ request()->routeIs('admin.plans*') ? 'bg-emerald-100 dark:bg-emerald-900/50 text-emerald-900 dark:text-emerald-100' : 'text-emerald-800 dark:text-emerald-200 hover:bg-emerald-50 dark:hover:bg-emerald-900/30' }}" wire:navigate>
                                <svg class="size-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z"></path></svg>
                                Plans
                            </a>
                            
                            <a href="{{ route('admin.revenue.analytics') }}" class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm {{ request()->routeIs('admin.revenue*') ? 'bg-emerald-100 dark:bg-emerald-900/50 text-emerald-900 dark:text-emerald-100' : 'text-emerald-800 dark:text-emerald-200 hover:bg-emerald-50 dark:hover:bg-emerald-900/30' }}" wire:navigate>
                                <svg class="size-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" /></svg>
                                Revenue Analytics
                            </a>
                        </div>
                    </div>

                    <!-- System Health Section -->
                    <div class="space-y-2">
                        <div class="px-3 text-xs font-medium text-emerald-700 dark:text-emerald-400 uppercase tracking-wider">
                            System Health
                        </div>
                        <div class="space-y-1">
                            <a href="{{ route('admin.monitoring.system-logs') }}" class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm {{ request()->routeIs('admin.monitoring.system-logs*') ? 'bg-emerald-100 dark:bg-emerald-900/50 text-emerald-900 dark:text-emerald-100' : 'text-emerald-800 dark:text-emerald-200 hover:bg-emerald-50 dark:hover:bg-emerald-900/30' }}" wire:navigate>
                                <svg class="size-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z"></path></svg>
                                System Logs
                            </a>
                            
                            <a href="{{ route('admin.monitoring.health') }}" class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm {{ request()->routeIs('admin.monitoring*') ? 'bg-emerald-100 dark:bg-emerald-900/50 text-emerald-900 dark:text-emerald-100' : 'text-emerald-800 dark:text-emerald-200 hover:bg-emerald-50 dark:hover:bg-emerald-900/30' }}" wire:navigate>
                                <svg class="size-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"></path></svg>
                                Monitoring
                            </a>
                            
                            <a href="{{ route('admin.webhooks.index') }}" class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm {{ request()->routeIs('admin.webhooks*') ? 'bg-emerald-100 dark:bg-emerald-900/50 text-emerald-900 dark:text-emerald-100' : 'text-emerald-800 dark:text-emerald-200 hover:bg-emerald-50 dark:hover:bg-emerald-900/30' }}" wire:navigate>
                                <svg class="size-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"></path></svg>
                                PayPal Webhooks
                            </a>
                        </div>
                    </div>
                    @endif

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

                    <!-- Subscription Section -->
                    <div class="space-y-2">
                        <div class="px-3 text-xs font-medium text-emerald-700 dark:text-emerald-400 uppercase tracking-wider">
                            Subscription
                        </div>
                        <div class="space-y-1">
                            <a href="{{ route('subscription.manage') }}" class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm {{ request()->routeIs('subscription.manage') ? 'bg-emerald-100 dark:bg-emerald-900/50 text-emerald-900 dark:text-emerald-100' : 'text-emerald-800 dark:text-emerald-200 hover:bg-emerald-50 dark:hover:bg-emerald-900/30' }}" wire:navigate>
                                <svg class="size-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
                                </svg>
                                My Subscription
                            </a>
                            <a href="{{ route('plans') }}" class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm {{ request()->routeIs('plans') ? 'bg-emerald-100 dark:bg-emerald-900/50 text-emerald-900 dark:text-emerald-100' : 'text-emerald-800 dark:text-emerald-200 hover:bg-emerald-50 dark:hover:bg-emerald-900/30' }}" wire:navigate>
                                <svg class="size-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                View Plans
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
                            <div>
                                @php
                                $unreadCount = auth()->user()->unreadNotifications()->count();
                                @endphp
                                
                                <a href="{{ route('notifications') }}" class="flex items-center gap-2 w-full px-3 py-2 text-sm font-medium text-emerald-700 dark:text-emerald-300 hover:text-emerald-900 dark:hover:text-emerald-100 hover:bg-emerald-100 dark:hover:bg-emerald-900/30 rounded-md transition-colors" wire:navigate>
                                    <div class="relative">
                                        <svg class="size-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                                        </svg>
                                        @if($unreadCount > 0)
                                            <span class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-emerald-500 text-xs font-bold text-white ring-2 ring-white dark:ring-zinc-800">
                                                {{ $unreadCount }}
                                            </span>
                                        @endif
                                    </div>
                                    <span>Notifications</span>
                                </a>
                            </div>
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

        <!-- SweetAlert2 JS -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        
        @fluxScripts
        @livewireScripts
    </body>
</html>
