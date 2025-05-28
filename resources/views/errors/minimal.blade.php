<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'media',
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
<body class="antialiased min-h-screen flex items-center justify-center p-4 sm:p-0 bg-gradient-to-br from-blue-900 via-indigo-800 to-blue-900">
    <!-- Main container with enhanced 3D-like design -->
    <div class="max-w-4xl w-full mx-auto relative overflow-hidden">
        <!-- Left content area -->
        <div class="w-full lg:w-1/2 text-white p-8 relative z-10">
            <!-- Error heading with gradient text -->
            <h1 class="text-6xl md:text-8xl font-bold mb-2">
                <span class="bg-clip-text text-transparent bg-gradient-to-r from-cyan-300 to-blue-200">
                    @yield('code') Error
                </span>
            </h1>
            
            <!-- Subtitle with light blue glow -->
            <div class="relative">
                <h2 class="text-5xl md:text-7xl font-bold mb-6 text-white/80 drop-shadow-[0_0_15px_rgba(186,230,253,0.3)]">
                    Oops!
                </h2>
                <div class="absolute -bottom-1 left-0 h-1 w-20 bg-gradient-to-r from-cyan-400 to-blue-500 rounded-full"></div>
            </div>
            
            <!-- Error message with improved typography -->
            <p class="mt-8 mb-12 text-lg text-blue-100/90 max-w-md">
                @yield('message_description', 'A ' . trim(preg_replace('/\d+/','',@yield('code'))) . ' is an HTTP status code that means you\'re able to communicate with the server but the server can\'t find the specific page.')
            </p>
            
            <!-- Back to home button with enhanced effects -->
            <a href="{{ url('/') }}" 
               class="group relative inline-flex items-center px-8 py-3.5 rounded-xl overflow-hidden bg-gradient-to-r from-blue-500 to-cyan-400 text-white font-medium shadow-lg transition-all duration-300 hover:shadow-cyan-300/20 hover:scale-105">
                Back to Homepage
                <!-- Enhanced shimmer effect -->
                <span class="absolute top-0 right-full w-12 h-full bg-white/30 transform rotate-12 translate-x-0 group-hover:translate-x-[400%] transition-transform duration-1000 ease-out"></span>
            </a>
            
            <!-- Secondary action button -->
            <button onclick="window.history.back()" 
                    class="ml-4 inline-flex items-center px-6 py-3 rounded-xl text-sm font-medium transition-all duration-200 bg-white/10 hover:bg-white/20 text-white border border-white/20">
                Go Back
            </button>
        </div>

        <!-- Right illustration area (visible on larger screens) -->
        <div class="absolute top-1/2 right-[-10%] lg:right-[-5%] transform -translate-y-1/2 w-[60%] lg:w-[55%] opacity-90 animate-float hidden lg:block">
            @yield('illustration', '<img src="/images/error-illustration.svg" alt="Error illustration" class="w-full h-auto">')
        </div>
        
        <!-- Background decorative elements -->
        <div class="absolute top-0 right-0 w-80 h-80 bg-gradient-to-bl from-blue-400/20 to-transparent rounded-full blur-3xl -z-10"></div>
        <div class="absolute bottom-0 left-0 w-80 h-80 bg-gradient-to-tr from-cyan-400/20 to-transparent rounded-full blur-3xl -z-10"></div>
        
        <!-- Decorative network lines -->
        <svg class="absolute inset-0 h-full w-full -z-20 opacity-10" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse">
                    <path d="M0 40V0h40" fill="none" stroke="white" stroke-opacity="0.2" stroke-width="0.5"></path>
                </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#grid)"></rect>
        </svg>
    </div>
</body>
</html>
