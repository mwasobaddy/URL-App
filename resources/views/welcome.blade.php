<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>URL-App</title>
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
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
    <main class="flex-1 flex items-center justify-center w-full">
        <div class="bg-white dark:bg-neutral-800 rounded-xl shadow-lg p-10 max-w-md w-full text-center">
            <h1 class="text-2xl font-semibold mb-2">Welcome to <span class="text-green-600">URL-App</span></h1>
            <p class="mb-6 text-neutral-600 dark:text-neutral-300">
                Create, manage, and share lists of your favorite URLs. Organize resources, publish lists, and share them with anyone.
            </p>
            @auth
                <div class="flex flex-col gap-3 mb-6">
                    <a href="{{ route('lists.dashboard') }}" class="btn btn-primary" wire:navigate>My URL Lists</a>
                    <a href="{{ route('lists.create') }}" class="btn btn-secondary" wire:navigate>Create New List</a>
                </div>
            @else
                <div class="flex flex-col gap-3 mb-6">
                    <a href="{{ route('register') }}" class="btn btn-primary" wire:navigate>Get Started</a>
                    <a href="{{ route('login') }}" class="btn btn-secondary" wire:navigate>Log In</a>
                </div>
            @endauth
            <div class="mt-4">
                <h2 class="font-semibold mb-1 text-base">Try a Public Demo</h2>
                <a href="{{ url('/l/demo') }}" class="underline text-green-600 hover:text-green-700" wire:navigate>View a sample published list</a>
            </div>
        </div>
    </main>
</body>
</html>
