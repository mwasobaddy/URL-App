<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>@yield('title')</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                darkMode: 'class',
                theme: {
                    extend: {
                        animation: {
                            'pulse-slow': 'pulse 4s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                            'bounce-slow': 'bounce 2s infinite',
                            'shimmer': 'shimmer 2s infinite linear',
                            'float': 'float 6s ease-in-out infinite',
                        },
                        keyframes: {
                            shimmer: {
                              '100%': { transform: 'translateX(100%)' }
                            },
                            float: {
                              '0%': { transform: 'translateY(0px)' },
                              '50%': { transform: 'translateY(-20px)' },
                              '100%': { transform: 'translateY(0px)' }
                            }
                        },
                    }
                },
                plugins: [],
            }
            
            // Check if dark mode is preferred
            if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark')
            } else {
                document.documentElement.classList.remove('dark')
            }
        </script>
        <!-- Add Poppins font for a modern look -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <style>
            body {
                font-family: 'Poppins', sans-serif;
            }
        </style>
    </head>
    <body class="antialiased min-h-screen flex items-center justify-center p-4 sm:p-0 bg-white dark:bg-zinc-800">
        <!-- Main container with glass morphism design -->
        <div class="max-w-4xl w-full mx-auto relative">
            <!-- Decorative elements - subtle background patterns -->
            <div class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-bl from-emerald-400/10 to-transparent rounded-full blur-3xl -z-10"></div>
            <div class="absolute bottom-0 left-0 w-80 h-80 bg-gradient-to-tr from-teal-400/10 to-transparent rounded-full blur-3xl -z-10"></div>
            
            <!-- Error content area -->
            <div class="backdrop-blur-sm bg-white/80 dark:bg-neutral-800/80 shadow-xl rounded-3xl p-8 border border-gray-100/40 dark:border-neutral-700/50 transition-all duration-300 relative overflow-hidden">
                <div class="w-full lg:w-1/2 text-gray-800 dark:text-white p-8 relative z-10">
                    <!-- Error heading with gradient text -->
                    <h1 class="text-6xl md:text-8xl font-bold mb-2">
                        <span class="bg-clip-text text-transparent bg-gradient-to-r from-emerald-500 to-teal-400">
                            @yield('code') Error
                        </span>
                    </h1>
                    
                    <!-- Subtitle with accent color -->
                    <div class="relative">
                        <h2 class="text-5xl md:text-7xl font-bold mb-6 text-gray-800/80 dark:text-white/80 drop-shadow-[0_0_15px_rgba(16,185,129,0.3)]">
                            Oops!
                        </h2>
                        <div class="absolute -bottom-1 left-0 h-1 w-20 bg-gradient-to-r from-emerald-500 to-teal-400 rounded-full"></div>
                    </div>
                    
                    <!-- Error message with improved typography -->
                    <p class="mt-8 mb-12 text-lg text-gray-600 dark:text-gray-300 max-w-md">
                        @hasSection('message_description')
                            @yield('message_description')
                        @else
                            Error occurred. Please contact the administrator if the problem persists.
                        @endif
                    </p>
                    
                    <!-- Back to home button with enhanced effects -->
                    <a href="{{ url('/') }}" 
                       class="group relative inline-flex items-center px-8 py-3.5 rounded-xl overflow-hidden bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white font-medium shadow-lg transition-all duration-300 hover:shadow-emerald-300/20 hover:scale-105">
                        Back to Homepage
                        <!-- Enhanced shimmer effect -->
                        <span class="absolute top-0 right-full w-12 h-full bg-white/30 transform rotate-12 translate-x-0 group-hover:translate-x-[400%] transition-transform duration-1000 ease-out"></span>
                    </a>
                    
                    <!-- Secondary action button -->
                    <button onclick="window.history.back()" 
                            class="ml-4 inline-flex items-center px-6 py-3 rounded-xl text-sm font-medium transition-all duration-200 bg-white/60 hover:bg-white dark:bg-neutral-700/50 dark:hover:bg-neutral-700/80 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-neutral-600/50">
                        Go Back
                    </button>
                </div>

                <!-- Right illustration area (visible on larger screens) -->
                <div class="absolute top-0 right-0 flex items-center transform -translate-y-1/2 w-[60%] lg:w-[55%] h-full opacity-90 animate-float hidden lg:block">
                    @yield('illustration', '<img src="/images/error-illustration.svg" alt="Error illustration" class="w-full h-auto">')
                </div>
            </div>

            <!-- Decorative network lines -->
            <svg class="absolute inset-0 h-full w-full -z-20 opacity-10" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse">
                        <path d="M0 40V0h40" fill="none" stroke="currentColor" stroke-opacity="0.2" stroke-width="0.5"></path>
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#grid)"></rect>
            </svg>
        </div>
    </body>
</html>
