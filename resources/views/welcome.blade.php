<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body class="bg-neutral-100 dark:bg-neutral-900 min-h-screen flex flex-col items-center justify-center">
    <header class="w-full max-w-2xl mx-auto flex justify-end p-6">
        @if (Route::has('login'))
            <nav class="flex gap-4">
                @auth
                    <a href="{{ url('/dashboard') }}" class="btn btn-primary" wire:navigate>Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-secondary" wire:navigate>Log in</a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="btn btn-primary" wire:navigate>Register</a>
                    @endif
                @endauth
            </nav>
        @endif
    </header>
    <main class="flex-1 flex items-center justify-center w-full flex-col">
        <header
            class="fixed top-0 z-30 w-full bg-white/80 dark:bg-zinc-900/80 backdrop-blur-lg shadow-sm border-b border-emerald-100 dark:border-emerald-900/50">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <!-- Logo/Brand -->
                    <div class="flex items-center gap-3">
                        <div class="flex items-center justify-center size-10 rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-700 dark:from-emerald-600 dark:to-emerald-800 shadow-md">
                            <x-app-logo-icon class="size-6 fill-current text-white animate-pulse" />
                        </div>
                        <span class="font-bold text-xl text-emerald-950 dark:text-emerald-100 tracking-tight">URL-App</span>
                    </div>

                    <!-- Desktop Navigation Links -->
                    <nav class="hidden md:flex items-center space-x-6">
                        <a href="#features" class="text-sm font-medium text-emerald-800 dark:text-emerald-200 hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors">Features</a>
                        <a href="#pricing" class="text-sm font-medium text-emerald-800 dark:text-emerald-200 hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors">Pricing</a>
                        <a href="#faq" class="text-sm font-medium text-emerald-800 dark:text-emerald-200 hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors">FAQ</a>
                        <a href="#contact" class="text-sm font-medium text-emerald-800 dark:text-emerald-200 hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors">Contact</a>
                    </nav>

                    <!-- Mobile Menu Button -->
                    <div class="md:hidden">
                        <button id="mobile-menu-toggle" class="p-2 rounded focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <svg class="w-6 h-6 text-emerald-700 dark:text-emerald-200" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" /></svg>
                        </button>
                    </div>

                    <!-- Auth Buttons -->
                    <div class="hidden md:flex items-center gap-4">
                        <a href="{{ route('login') }}" class="text-sm font-medium text-emerald-700 hover:text-emerald-800 dark:text-emerald-300 dark:hover:text-emerald-100 transition-colors py-2" wire:navigate>Sign in</a>
                        <a href="{{ route('register') }}" class="inline-flex items-center justify-center rounded-full bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 px-5 py-2 text-sm font-medium text-white shadow-sm transition-all duration-300 hover:shadow-md dark:from-emerald-600 dark:to-teal-600 dark:hover:from-emerald-500 dark:hover:to-teal-500" wire:navigate>Get started <span class="ml-1">→</span></a>
                    </div>
                </div>
                <!-- Mobile Navigation Menu -->
                <div id="mobile-menu" class="md:hidden hidden flex-col gap-2 mt-2 bg-white dark:bg-zinc-900 rounded shadow-lg p-4">
                    <a href="#features" class="block py-2 text-emerald-800 dark:text-emerald-200 hover:text-emerald-600 dark:hover:text-emerald-400">Features</a>
                    <a href="#pricing" class="block py-2 text-emerald-800 dark:text-emerald-200 hover:text-emerald-600 dark:hover:text-emerald-400">Pricing</a>
                    <a href="#faq" class="block py-2 text-emerald-800 dark:text-emerald-200 hover:text-emerald-600 dark:hover:text-emerald-400">FAQ</a>
                    <a href="#contact" class="block py-2 text-emerald-800 dark:text-emerald-200 hover:text-emerald-600 dark:hover:text-emerald-400">Contact</a>
                    <div class="flex gap-2 mt-2">
                        <a href="{{ route('login') }}" class="flex-1 text-center rounded bg-emerald-100 text-emerald-700 py-2 font-medium" wire:navigate>Sign in</a>
                        <a href="{{ route('register') }}" class="flex-1 text-center rounded bg-emerald-600 text-white py-2 font-medium" wire:navigate>Get started</a>
                    </div>
                </div>
                <script>
                    const menuBtn = document.getElementById('mobile-menu-toggle');
                    const menu = document.getElementById('mobile-menu');
                    menuBtn?.addEventListener('click', () => menu.classList.toggle('hidden'));
                </script>
            </div>
        </header>

        <!-- Hero Section -->
        <div
            class="relative overflow-hidden pt-24 pb-16 bg-gradient-to-b from-white to-emerald-50/30 dark:from-zinc-900 dark:to-emerald-950/20 min-h-[70vh] flex items-center">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid lg:grid-cols-2 gap-12 items-center">
                    <!-- Text Content -->
                    <div class="space-y-6">
                        <span
                            class="inline-flex items-center rounded-full bg-emerald-100 dark:bg-emerald-900/50 px-3 py-1 text-xs font-medium text-emerald-700 dark:text-emerald-300">
                            ✨ Organize and share URLs effortlessly
                        </span>
                        <h1 class="text-4xl sm:text-5xl font-bold tracking-tight text-emerald-950 dark:text-white">
                            Collect & Share <span class="text-emerald-600 dark:text-emerald-400">Links</span> Like Never
                            Before
                        </h1>
                        <p class="text-lg text-emerald-800/80 dark:text-emerald-100/80 max-w-2xl">
                            Create personalized URL collections. Organize your favorite websites, share them easily, and
                            access them from anywhere.
                        </p>
                        <div class="flex flex-wrap gap-4 mt-2">
                            <a href="{{ route('register') }}"
                                class="inline-flex items-center justify-center rounded-full bg-emerald-600 hover:bg-emerald-700 px-6 py-3 text-base font-medium text-white shadow-lg hover:shadow-emerald-500/20 transition-all duration-300"
                                wire:navigate>
                                Start for free
                            </a>
                            <a href="#demo"
                                class="inline-flex items-center justify-center rounded-full bg-white dark:bg-zinc-800 border border-emerald-200 dark:border-emerald-800/50 px-6 py-3 text-base font-medium text-emerald-700 dark:text-emerald-300 hover:bg-emerald-50 dark:hover:bg-emerald-900/30 shadow-sm transition-colors"
                                wire:navigate>
                                Watch demo <svg class="ml-2 size-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                        d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z">
                                    </path>
                                </svg>
                            </a>
                        </div>
                    </div>

                    <!-- Image/Visual -->
                    <div class="hidden lg:block relative">
                        <div
                            class="absolute -inset-1 bg-gradient-to-r from-emerald-500 to-teal-500 rounded-2xl blur-xl opacity-20 dark:opacity-30 animate-pulse">
                        </div>
                        <div
                            class="relative bg-white dark:bg-zinc-800 rounded-xl shadow-xl overflow-hidden border border-emerald-100 dark:border-emerald-900/50">
                            <div
                                class="p-2 bg-emerald-50 dark:bg-emerald-900/30 border-b border-emerald-100 dark:border-emerald-900/50 flex items-center gap-1.5">
                                <div class="size-3 rounded-full bg-red-500"></div>
                                <div class="size-3 rounded-full bg-amber-500"></div>
                                <div class="size-3 rounded-full bg-emerald-500"></div>
                            </div>
                            <div class="p-6">
                                <div class="space-y-4">
                                    <div class="h-10 bg-emerald-100/50 dark:bg-emerald-900/20 rounded-lg animate-pulse">
                                    </div>
                                    <div class="space-y-2">
                                        <div
                                            class="h-4 bg-emerald-100/70 dark:bg-emerald-900/30 rounded w-3/4 animate-pulse">
                                        </div>
                                        <div class="h-4 bg-emerald-100/70 dark:bg-emerald-900/30 rounded animate-pulse">
                                        </div>
                                        <div
                                            class="h-4 bg-emerald-100/70 dark:bg-emerald-900/30 rounded w-5/6 animate-pulse">
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-3">
                                        <div
                                            class="h-24 bg-emerald-100/80 dark:bg-emerald-900/40 rounded-lg animate-pulse">
                                        </div>
                                        <div
                                            class="h-24 bg-emerald-100/80 dark:bg-emerald-900/40 rounded-lg animate-pulse">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Background decoration -->
            <div class="absolute top-10 right-0 -z-10 transform translate-x-1/2">
                <div
                    class="size-64 rounded-full bg-gradient-to-br from-emerald-300/20 to-teal-300/20 dark:from-emerald-700/10 dark:to-teal-700/10 blur-3xl">
                </div>
            </div>
            <div class="absolute bottom-10 left-0 -z-10 transform -translate-x-1/2">
                <div
                    class="size-64 rounded-full bg-gradient-to-tr from-emerald-300/10 to-teal-300/10 dark:from-emerald-700/5 dark:to-teal-700/5 blur-3xl">
                </div>
            </div>
        </div>

        <!-- Features Section -->
        <section id="features" class="py-24 bg-white dark:bg-zinc-900">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h1 class="text-4xl font-bold tracking-tight text-emerald-950 dark:text-white mb-4">
                        Powerful Features for <span class="text-emerald-600 dark:text-emerald-400">URL Management</span>
                    </h1>
                    <p class="text-lg text-emerald-800/80 dark:text-emerald-100/80 max-w-2xl mx-auto">
                        Discover all the tools and features that make URL-App the perfect solution for organizing and sharing your links.
                    </p>
                </div>
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8 mt-12">
                    <!-- Feature 1: List Creation -->
                    <div class="bg-white dark:bg-zinc-800 rounded-xl p-6 border border-emerald-100 dark:border-emerald-900/50 shadow-sm hover:shadow-md transition-shadow">
                        <div class="size-12 rounded-lg bg-emerald-100 dark:bg-emerald-900/50 flex items-center justify-center mb-4">
                            <svg class="size-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-emerald-950 dark:text-white mb-2">Easy List Creation</h3>
                        <p class="text-emerald-800/80 dark:text-emerald-100/80">Create and organize your URL collections in seconds with our intuitive interface.</p>
                    </div>
                    <!-- Feature 2: Custom URLs -->
                    <div class="bg-white dark:bg-zinc-800 rounded-xl p-6 border border-emerald-100 dark:border-emerald-900/50 shadow-sm hover:shadow-md transition-shadow">
                        <div class="size-12 rounded-lg bg-emerald-100 dark:bg-emerald-900/50 flex items-center justify-center mb-4">
                            <svg class="size-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-emerald-950 dark:text-white mb-2">Custom URLs</h3>
                        <p class="text-emerald-800/80 dark:text-emerald-100/80">Choose your own custom URLs for easy sharing and memorability.</p>
                    </div>
                    <!-- Feature 3: Instant Sharing -->
                    <div class="bg-white dark:bg-zinc-800 rounded-xl p-6 border border-emerald-100 dark:border-emerald-900/50 shadow-sm hover:shadow-md transition-shadow">
                        <div class="size-12 rounded-lg bg-emerald-100 dark:bg-emerald-900/50 flex items-center justify-center mb-4">
                            <svg class="size-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 100 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186l9.566-5.314m-9.566 7.5l9.566 5.314m0 0a2.25 2.25 0 103.935 2.186 2.25 2.25 0 00-3.935-2.186zm0-12.814a2.25 2.25 0 103.933-2.185 2.25 2.25 0 00-3.933 2.185z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-emerald-950 dark:text-white mb-2">Instant Sharing</h3>
                        <p class="text-emerald-800/80 dark:text-emerald-100/80">Share your URL collections instantly with anyone, anywhere.</p>
                    </div>
                    <!-- Feature 4: List Management -->
                    <div class="bg-white dark:bg-zinc-800 rounded-xl p-6 border border-emerald-100 dark:border-emerald-900/50 shadow-sm hover:shadow-md transition-shadow">
                        <div class="size-12 rounded-lg bg-emerald-100 dark:bg-emerald-900/50 flex items-center justify-center mb-4">
                            <svg class="size-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-emerald-950 dark:text-white mb-2">List Management</h3>
                        <p class="text-emerald-800/80 dark:text-emerald-100/80">Easily edit, organize, and manage your URL collections in one place.</p>
                    </div>
                    <!-- Feature 5: Public/Private Lists -->
                    <div class="bg-white dark:bg-zinc-800 rounded-xl p-6 border border-emerald-100 dark:border-emerald-900/50 shadow-sm hover:shadow-md transition-shadow">
                        <div class="size-12 rounded-lg bg-emerald-100 dark:bg-emerald-900/50 flex items-center justify-center mb-4">
                            <svg class="size-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-emerald-950 dark:text-white mb-2">Privacy Controls</h3>
                        <p class="text-emerald-800/80 dark:text-emerald-100/80">Choose between public and private lists for complete control over your sharing.</p>
                    </div>
                    <!-- Feature 6: Analytics -->
                    <div class="bg-white dark:bg-zinc-800 rounded-xl p-6 border border-emerald-100 dark:border-emerald-900/50 shadow-sm hover:shadow-md transition-shadow">
                        <div class="size-12 rounded-lg bg-emerald-100 dark:bg-emerald-900/50 flex items-center justify-center mb-4">
                            <svg class="size-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125-1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-emerald-950 dark:text-white mb-2">Basic Analytics</h3>
                        <p class="text-emerald-800/80 dark:text-emerald-100/80">Track views and engagement with your shared URL lists.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Pricing Section -->
        <section id="pricing" class="py-24 bg-emerald-50 dark:bg-zinc-950 w-full">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <span class="inline-flex items-center rounded-full bg-emerald-100 dark:bg-emerald-900/50 px-3 py-1 text-xs font-medium text-emerald-700 dark:text-emerald-300 mb-4">
                        Start for free, upgrade when you need
                    </span>
                    <h1 class="text-4xl font-bold tracking-tight text-emerald-950 dark:text-white mb-4">
                        Simple, Transparent <span class="text-emerald-600 dark:text-emerald-400">Pricing</span>
                    </h1>
                    <p class="text-lg text-emerald-800/80 dark:text-emerald-100/80 max-w-2xl mx-auto">
                        Choose the plan that best fits your needs. All plans include core features.
                    </p>
                </div>
                {{-- <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8 max-w-5xl mx-auto">
                    <!-- Free Plan -->
                    <div class="bg-white dark:bg-zinc-800 rounded-xl p-8 border border-emerald-100 dark:border-emerald-900/50 shadow-sm">
                        <div class="mb-6">
                            <h3 class="text-xl font-bold text-emerald-950 dark:text-white mb-2">Free</h3>
                            <p class="text-emerald-800/80 dark:text-emerald-100/80 text-sm">Perfect for getting started</p>
                        </div>
                        <div class="mb-6">
                            <span class="text-4xl font-bold text-emerald-950 dark:text-white">$0</span>
                            <span class="text-emerald-800/80 dark:text-emerald-100/80">/month</span>
                        </div>
                        <ul class="space-y-4 mb-8">
                            <li class="flex items-center text-sm text-emerald-800 dark:text-emerald-200">
                                <svg class="size-5 mr-3 text-emerald-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                Up to 5 URL lists
                            </li>
                            <li class="flex items-center text-sm text-emerald-800 dark:text-emerald-200">
                                <svg class="size-5 mr-3 text-emerald-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                Basic analytics
                            </li>
                            <li class="flex items-center text-sm text-emerald-800 dark:text-emerald-200">
                                <svg class="size-5 mr-3 text-emerald-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                Custom URLs
                            </li>
                        </ul>
                        <a href="{{ route('register') }}" class="block text-center rounded-lg bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-900/30 dark:hover:bg-emerald-900/50 px-4 py-2.5 text-sm font-medium text-emerald-700 dark:text-emerald-300 transition-colors" wire:navigate>
                            Get started
                        </a>
                    </div>
                    <!-- Pro Plan -->
                    <div class="bg-white dark:bg-zinc-800 rounded-xl p-8 border-2 border-emerald-500 dark:border-emerald-400 shadow-xl relative">
                        <div class="absolute -top-4 left-1/2 transform -translate-x-1/2">
                            <span class="bg-gradient-to-r from-emerald-500 to-teal-500 text-white text-xs font-bold px-3 py-1 rounded-full">MOST POPULAR</span>
                        </div>
                        <div class="mb-6">
                            <h3 class="text-xl font-bold text-emerald-950 dark:text-white mb-2">Pro</h3>
                            <p class="text-emerald-800/80 dark:text-emerald-100/80 text-sm">For power users</p>
                        </div>
                        <div class="mb-6">
                            <span class="text-4xl font-bold text-emerald-950 dark:text-white">$9</span>
                            <span class="text-emerald-800/80 dark:text-emerald-100/80">/month</span>
                        </div>
                        <ul class="space-y-4 mb-8">
                            <li class="flex items-center text-sm text-emerald-800 dark:text-emerald-200">
                                <svg class="size-5 mr-3 text-emerald-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                Unlimited URL lists
                            </li>
                            <li class="flex items-center text-sm text-emerald-800 dark:text-emerald-200">
                                <svg class="size-5 mr-3 text-emerald-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                Advanced analytics
                            </li>
                            <li class="flex items-center text-sm text-emerald-800 dark:text-emerald-200">
                                <svg class="size-5 mr-3 text-emerald-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                Premium URLs
                            </li>
                            <li class="flex items-center text-sm text-emerald-800 dark:text-emerald-200">
                                <svg class="size-5 mr-3 text-emerald-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                Priority support
                            </li>
                        </ul>
                        <a href="{{ route('register') }}" class="block text-center rounded-lg bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition-all duration-300 hover:shadow-md dark:from-emerald-600 dark:to-teal-600 dark:hover:from-emerald-500 dark:hover:to-teal-500" wire:navigate>
                            Start free trial
                        </a>
                    </div>
                    <!-- Enterprise Plan -->
                    <div class="bg-white dark:bg-zinc-800 rounded-xl p-8 border border-emerald-100 dark:border-emerald-900/50 shadow-sm">
                        <div class="mb-6">
                            <h3 class="text-xl font-bold text-emerald-950 dark:text-white mb-2">Enterprise</h3>
                            <p class="text-emerald-800/80 dark:text-emerald-100/80 text-sm">For large organizations</p>
                        </div>
                        <div class="mb-6">
                            <span class="text-4xl font-bold text-emerald-950 dark:text-white">Custom</span>
                        </div>
                        <ul class="space-y-4 mb-8">
                            <li class="flex items-center text-sm text-emerald-800 dark:text-emerald-200">
                                <svg class="size-5 mr-3 text-emerald-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                Everything in Pro
                            </li>
                            <li class="flex items-center text-sm text-emerald-800 dark:text-emerald-200">
                                <svg class="size-5 mr-3 text-emerald-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                Custom integration
                            </li>
                            <li class="flex items-center text-sm text-emerald-800 dark:text-emerald-200">
                                <svg class="size-5 mr-3 text-emerald-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                Dedicated support
                            </li>
                            <li class="flex items-center text-sm text-emerald-800 dark:text-emerald-200">
                                <svg class="size-5 mr-3 text-emerald-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                SLA
                            </li>
                        </ul>
                        <a href="mailto:enterprise@url-app.com" class="block text-center rounded-lg bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-900/30 dark:hover:bg-emerald-900/50 px-4 py-2.5 text-sm font-medium text-emerald-700 dark:text-emerald-300 transition-colors">
                            Contact sales
                        </a>
                    </div>
                </div> --}}

                <livewire:plans.pricing-table />
            </div>
        </section>

        <!-- FAQ Section -->
        <section id="faq" class="py-24 bg-white dark:bg-zinc-900">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h1 class="text-4xl font-bold tracking-tight text-emerald-950 dark:text-white mb-4">
                        Frequently Asked <span class="text-emerald-600 dark:text-emerald-400">Questions</span>
                    </h1>
                    <p class="text-lg text-emerald-800/80 dark:text-emerald-100/80 max-w-2xl mx-auto">
                        Find answers to common questions about URL-App's features and functionality.
                    </p>
                </div>
                <div class="max-w-3xl mx-auto space-y-6">
                    <!-- Question 1 -->
                    <div class="bg-white dark:bg-zinc-800 rounded-xl p-6 border border-emerald-100 dark:border-emerald-900/50 shadow-sm">
                        <h3 class="text-lg font-semibold text-emerald-950 dark:text-white mb-3">
                            What is URL-App?
                        </h3>
                        <p class="text-emerald-800/80 dark:text-emerald-100/80">
                            URL-App is a web application that helps you organize and share collections of URLs. You can create lists of links, customize their URLs, and share them with others easily.
                        </p>
                    </div>
                    <!-- Question 2 -->
                    <div class="bg-white dark:bg-zinc-800 rounded-xl p-6 border border-emerald-100 dark:border-emerald-900/50 shadow-sm">
                        <h3 class="text-lg font-semibold text-emerald-950 dark:text-white mb-3">
                            How many URL lists can I create?
                        </h3>
                        <p class="text-emerald-800/80 dark:text-emerald-100/80">
                            With a free account, you can create up to 5 URL lists. Pro users get unlimited lists, and Enterprise users can create as many as needed for their organization.
                        </p>
                    </div>
                    <!-- Question 3 -->
                    <div class="bg-white dark:bg-zinc-800 rounded-xl p-6 border border-emerald-100 dark:border-emerald-900/50 shadow-sm">
                        <h3 class="text-lg font-semibold text-emerald-950 dark:text-white mb-3">
                            Can I customize the URL for my lists?
                        </h3>
                        <p class="text-emerald-800/80 dark:text-emerald-100/80">
                            Yes! You can create custom URLs for your lists to make them more memorable and branded. If you don't specify a custom URL, we'll automatically generate one for you.
                        </p>
                    </div>
                    <!-- Question 4 -->
                    <div class="bg-white dark:bg-zinc-800 rounded-xl p-6 border border-emerald-100 dark:border-emerald-900/50 shadow-sm">
                        <h3 class="text-lg font-semibold text-emerald-950 dark:text-white mb-3">
                            How do I share my URL lists?
                        </h3>
                        <p class="text-emerald-800/80 dark:text-emerald-100/80">
                            Once you've created a list, you can share it by copying its unique URL. Pro users get additional sharing features and analytics to track engagement.
                        </p>
                    </div>
                    <!-- Question 5 -->
                    <div class="bg-white dark:bg-zinc-800 rounded-xl p-6 border border-emerald-100 dark:border-emerald-900/50 shadow-sm">
                        <h3 class="text-lg font-semibold text-emerald-950 dark:text-white mb-3">
                            Is there a limit to how many URLs I can add to a list?
                        </h3>
                        <p class="text-emerald-800/80 dark:text-emerald-100/80">
                            Free accounts can add up to 20 URLs per list. Pro accounts have no limits on the number of URLs per list.
                        </p>
                    </div>
                    <!-- Question 6 -->
                    <div class="bg-white dark:bg-zinc-800 rounded-xl p-6 border border-emerald-100 dark:border-emerald-900/50 shadow-sm">
                        <h3 class="text-lg font-semibold text-emerald-950 dark:text-white mb-3">
                            Can I make my lists private?
                        </h3>
                        <p class="text-emerald-800/80 dark:text-emerald-100/80">
                            Yes, you can choose whether each list is public or private. Private lists are only accessible to you and people you specifically share them with.
                        </p>
                    </div>
                </div>
                <div class="mt-16 text-center">
                    <p class="text-emerald-800/80 dark:text-emerald-100/80">
                        Still have questions? Contact our support team at
                        <a href="mailto:support@url-app.com" class="text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 dark:hover:text-emerald-300">
                            support@url-app.com
                        </a>
                    </p>
                </div>
            </div>
        </section>

        <!-- Contact Us Section -->
        <section id="contact" class="py-24 bg-emerald-50 dark:bg-zinc-950">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h2 class="text-3xl font-bold text-emerald-900 dark:text-white mb-4">Contact Us</h2>
                <p class="text-lg text-emerald-800/80 dark:text-emerald-100/80 mb-6">Have questions or need help? Reach out to our team and we'll get back to you as soon as possible.</p>
                <a href="mailto:support@url-app.com" class="inline-block rounded bg-emerald-600 text-white px-6 py-3 font-medium hover:bg-emerald-700 transition">support@url-app.com</a>
            </div>
        </section>
    </main>
</body>

</html>
