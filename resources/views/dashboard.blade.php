<x-layouts.app :title="__('Dashboard')">
    <!-- Hero section with glass morphism effect -->
    <div class="max-w-6xl mx-auto backdrop-blur-sm bg-white/80 dark:bg-neutral-800/80 shadow-xl rounded-3xl p-6 lg:p-8 mt-8 border border-gray-100/40 dark:border-neutral-700/50 transition-all duration-300 relative overflow-hidden">
        <!-- Decorative elements - subtle background patterns -->
        <div class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-bl from-emerald-400/10 to-transparent rounded-full blur-3xl -z-10"></div>
        <div class="absolute bottom-0 left-0 w-80 h-80 bg-gradient-to-tr from-teal-400/10 to-transparent rounded-full blur-3xl -z-10"></div>
        
        <div class="flex flex-col md:flex-row md:items-center gap-8">
            <!-- Welcome content -->
            <div class="flex-1">
                <div class="relative mb-2">
                    <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight">
                        <span class="bg-clip-text text-transparent bg-gradient-to-r from-emerald-500 to-teal-400">
                            Welcome to Your Dashboard
                        </span>
                    </h1>
                    <!-- Animated decorative element -->
                    <div class="absolute -bottom-3 left-0 h-1 w-16 bg-gradient-to-r from-emerald-500 to-teal-400 rounded-full animate-pulse"></div>
                </div>
                
                <p class="mt-4 text-gray-600 dark:text-gray-300 max-w-xl">
                    Create, manage, and share your URL collections all in one place. Organize your favorite resources and share them with the world.
                </p>
                
                <!-- Stats cards -->
                <div class="grid grid-cols-2 gap-4 mt-6">
                    <div class="bg-white/60 dark:bg-neutral-700/30 rounded-xl p-4 border border-gray-100/60 dark:border-neutral-700/40 backdrop-blur-sm transition-all duration-300 hover:shadow-md">
                        <div class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Your Lists</div>
                        <div class="mt-1 flex items-center">
                            <span class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ auth()->user()->urlLists()->count() }}</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2 text-emerald-500" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
                                <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                    <div class="bg-white/60 dark:bg-neutral-700/30 rounded-xl p-4 border border-gray-100/60 dark:border-neutral-700/40 backdrop-blur-sm transition-all duration-300 hover:shadow-md">
                        <div class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Public Lists</div>
                        <div class="mt-1 flex items-center">
                            <span class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ auth()->user()->urlLists()->where('published', true)->count() }}</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2 text-emerald-500" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Quick actions card -->
            <div class="w-full md:w-80 lg:w-96 bg-white/70 dark:bg-neutral-800/60 rounded-2xl p-6 border border-gray-100/60 dark:border-neutral-700/40 backdrop-blur-sm shadow-sm hover:shadow-md transition-all duration-300">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-emerald-500" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M6.672 1.911a1 1 0 10-1.932.518l.259.966a1 1 0 001.932-.518l-.26-.966zM2.429 4.74a1 1 0 10-.517 1.932l.966.259a1 1 0 00.517-1.932l-.966-.26zm8.814-.569a1 1 0 00-1.415-1.414l-.707.707a1 1 0 101.415 1.415l.707-.708zm-7.071 7.072l.707-.707A1 1 0 003.465 9.12l-.708.707a1 1 0 001.415 1.415zm3.2-5.171a1 1 0 00-1.3 1.3l4 10a1 1 0 001.823.075l1.38-2.759 3.018 3.02a1 1 0 001.414-1.415l-3.019-3.02 2.76-1.379a1 1 0 00-.076-1.822l-10-4z" clip-rule="evenodd" />
                    </svg>
                    Quick Actions
                </h3>
                
                <div class="space-y-3">
                    <!-- Create list button with shimmer effect -->
                    <a 
                        href="{{ route('lists.create') }}" 
                        class="relative overflow-hidden flex items-center w-full py-3 px-4 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white rounded-xl text-sm font-medium transition-all duration-300 shadow-sm hover:shadow group"
                        wire:navigate
                    >
                        <span class="relative z-10 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                            </svg>
                            Create New List
                        </span>
                        <!-- Shimmer effect -->
                        <span class="absolute top-0 right-full w-12 h-full bg-white/30 transform rotate-12 translate-x-0 transition-transform duration-1000 ease-out group-hover:translate-x-[400%]"></span>
                    </a>
                    
                    <!-- My lists button -->
                    <a 
                        href="{{ route('lists.dashboard') }}" 
                        class="flex items-center w-full py-3 px-4 bg-white hover:bg-gray-50 dark:bg-neutral-700/50 dark:hover:bg-neutral-700/80 text-gray-700 dark:text-gray-200 rounded-xl text-sm font-medium transition-all duration-200 border border-gray-200 dark:border-neutral-600/50"
                        wire:navigate
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3 text-emerald-500" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z" />
                        </svg>
                        View My Lists
                    </a>
                    
                    <!-- Profile button -->
                    <a 
                        href="{{ route('settings.profile') }}" 
                        class="flex items-center w-full py-3 px-4 bg-white hover:bg-gray-50 dark:bg-neutral-700/50 dark:hover:bg-neutral-700/80 text-gray-700 dark:text-gray-200 rounded-xl text-sm font-medium transition-all duration-200 border border-gray-200 dark:border-neutral-600/50"
                        wire:navigate
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3 text-emerald-500" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                        </svg>
                        Manage Profile
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recently Updated Lists - New Component -->
    <div class="max-w-6xl mx-auto my-8">
        <div class="flex items-center justify-between mb-6">
            <div class="relative">
                <h2 class="text-2xl font-bold tracking-tight text-gray-800 dark:text-gray-200">
                    Your Recent Lists
                </h2>
                <div class="absolute -bottom-2 left-0 h-1 w-12 bg-gradient-to-r from-emerald-500 to-teal-400 rounded-full"></div>
            </div>
            
            <a href="{{ route('lists.dashboard') }}" class="text-sm text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300 font-medium flex items-center transition-colors duration-200" wire:navigate>
                View All
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                </svg>
            </a>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse(auth()->user()->urlLists()->latest()->take(3)->get() as $list)
                <!-- List card with hover effects -->
                <div class="group bg-white/70 dark:bg-neutral-800/60 rounded-2xl overflow-hidden border border-gray-100/60 dark:border-neutral-700/40 backdrop-blur-sm transition-all duration-300 hover:shadow-lg relative">
                    <!-- Status indicator -->
                    <div class="absolute top-3 right-3">
                        @if($list->published)
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-800/30">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                Public
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-50 text-gray-600 dark:bg-gray-700/50 dark:text-gray-400 border border-gray-100 dark:border-gray-600/30">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                                </svg>
                                Private
                            </span>
                        @endif
                    </div>
                    
                    <div class="p-5">
                        <div class="flex items-center mb-3">
                            <div class="flex-shrink-0 h-8 w-8 rounded-full bg-gradient-to-br from-emerald-500 to-teal-500 text-white flex items-center justify-center text-sm font-medium">
                                {{ substr($list->name, 0, 1) }}
                            </div>
                            <h3 class="ml-3 text-lg font-semibold text-gray-900 dark:text-gray-100 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors duration-200">
                                {{ $list->name }}
                            </h3>
                        </div>
                        
                        <div class="flex items-center text-sm text-gray-500 dark:text-gray-400 mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                            </svg>
                            Created {{ $list->created_at->diffForHumans() }}
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-teal-50 text-teal-700 dark:bg-teal-900/30 dark:text-teal-400 border border-teal-100 dark:border-teal-800/30">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z"/>
                                </svg>
                                {{ $list->urls()->count() }} {{ Str::plural('link', $list->urls()->count()) }}
                            </div>
                            
                            <a href="{{ route('lists.show', $list) }}" class="text-emerald-600 dark:text-emerald-500 hover:text-emerald-700 dark:hover:text-emerald-400 group/url relative overflow-hidden flex items-center text-sm font-medium transition-colors duration-200" wire:navigate>
                                View
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1.5 transform group-hover/url:translate-x-1 transition-all duration-200" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-3 bg-white/70 dark:bg-neutral-800/60 rounded-2xl p-8 border border-gray-100/60 dark:border-neutral-700/40 backdrop-blur-sm text-center">
                    <div class="w-16 h-16 mx-auto mb-4 bg-gray-50 dark:bg-gray-800/50 rounded-full flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-300 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                    
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">No lists yet</h3>
                    <p class="mt-2 text-gray-500 dark:text-gray-400 max-w-md mx-auto">
                        You haven't created any URL lists yet. Get started by creating your first list!
                    </p>
                    
                    <a href="{{ route('lists.create') }}" class="mt-4 inline-flex items-center px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg text-sm font-medium transition-all duration-200 shadow-sm hover:shadow" wire:navigate>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        Create your first list
                    </a>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Public URL Lists Section -->
    <livewire:publishedurllistscomponent />
</x-layouts.app>
