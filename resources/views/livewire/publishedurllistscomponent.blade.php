<?php

use Livewire\Volt\Component;
use WireUi\Traits\WireUiActions;
use Livewire\WithPagination;
use App\Models\UrlList;

new class extends Component {
    use WireUiActions, WithPagination;

    public $search = '';
    public $sortBy = 'created_at';
    public $sortDirection = 'desc';
    protected $queryString = ['search', 'sortBy', 'sortDirection'];

    public function placeholder()
    {
        return <<<'HTML'
        <div class="max-w-6xl mx-auto backdrop-blur-sm bg-white/90 dark:bg-neutral-800/90 shadow-xl rounded-3xl p-6 lg:p-8 mt-8 border border-gray-100/40 dark:border-neutral-700/50">
            <div class="flex items-center justify-center p-12">
                <div class="flex flex-col items-center">
                    <div class="h-12 w-12 rounded-full border-4 border-emerald-500/30 border-t-emerald-500 animate-spin"></div>
                    <p class="mt-4 text-emerald-600 dark:text-emerald-400 text-sm">Loading public lists...</p>
                </div>
            </div>
        </div>
        HTML;
    }

    public function with(): array
    {
        $query = UrlList::where('published', true)
            ->with(['user'])
            ->withCount('urls');
        
        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('custom_url', 'like', '%' . $this->search . '%');
            });
        }

        $query->orderBy($this->sortBy, $this->sortDirection);

        return [
            'lists' => $query->paginate(10)
        ];
    }

    public function sort($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
    }
}; ?>

<!-- Main container with glass morphism effect -->
<div class="max-w-6xl mx-auto backdrop-blur-sm bg-white/80 dark:bg-neutral-800/80 shadow-xl rounded-3xl p-6 lg:p-8 mt-8 border border-gray-100/40 dark:border-neutral-700/50 transition-all duration-300 relative overflow-hidden">
    <!-- Decorative elements - subtle background patterns -->
    <div class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-bl from-emerald-400/10 to-transparent rounded-full blur-3xl -z-10"></div>
    <div class="absolute bottom-0 left-0 w-80 h-80 bg-gradient-to-tr from-teal-400/10 to-transparent rounded-full blur-3xl -z-10"></div>
    
    <!-- Header with modern typography -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-10">
        <div class="relative">
            <h2 class="text-3xl md:text-4xl font-extrabold tracking-tight">
                <span class="bg-clip-text text-transparent bg-gradient-to-r from-emerald-500 to-teal-400">
                    Public URL Lists
                </span>
            </h2>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 max-w-md">
                Discover public URL lists and request collaboration access
            </p>
            <!-- Animated decorative element -->
            <div class="absolute -bottom-3 left-0 h-1 w-16 bg-gradient-to-r from-emerald-500 to-teal-400 rounded-full animate-pulse"></div>
        </div>
        
        <!-- Stats/Info badge -->
        <div class="mt-4 sm:mt-0 flex items-center">
            <div class="bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300 px-4 py-1.5 rounded-full text-sm font-medium inline-flex items-center border border-emerald-100/80 dark:border-emerald-800/30 shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                </svg>
                <span>Last updated: <span class="font-semibold">{{ now()->format('M d, Y') }}</span></span>
            </div>
        </div>
    </div>

    <!-- Enhanced search component with animations -->
    <div class="mb-8 relative max-w-2xl mx-auto">
        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 dark:text-gray-500">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </div>
        <input 
            type="text" 
            wire:model.live.debounce.300ms="search" 
            placeholder="Search public lists by name or URL..." 
            class="w-full h-12 rounded-xl border border-gray-200 dark:border-gray-700 pl-12 pr-10 py-3 focus:ring-2 focus:ring-emerald-400/40 focus:border-emerald-400 focus:outline-none bg-white/80 dark:bg-neutral-800/50 text-gray-900 dark:text-gray-100 transition-all duration-200 placeholder-gray-400 dark:placeholder-gray-500 shadow-sm hover:shadow-md"
            wire:loading.class="bg-emerald-50 dark:bg-emerald-900/10"
        >
        
        <!-- Loading indicator -->
        <div wire:loading.delay wire:target="search" class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none hidden" wire:loading.class.remove="hidden">
            <div class="h-5 w-5">
                <div class="h-full w-full rounded-full border-2 border-emerald-500/30 border-t-emerald-500 animate-spin"></div>
            </div>
        </div>
        
        <!-- Clear search button with improved animation -->
        @if($search)
            <button 
                wire:click="$set('search', '')" 
                class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-all duration-200 transform hover:scale-110"
                wire:loading.class="hidden"
                wire:target="search"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
            </button>
        @endif
    </div>

    <!-- Lists container with enhanced styling -->
    <div class="bg-white/60 dark:bg-neutral-800/60 rounded-2xl overflow-hidden shadow border border-gray-100/60 dark:border-neutral-700/40 backdrop-blur-sm transition-all duration-300">
        <!-- Table headers with improved interaction -->
        <div class="hidden md:grid grid-cols-12 gap-4 py-4 px-6 bg-gray-50/90 dark:bg-neutral-700/20 border-b border-gray-100 dark:border-neutral-700/50">
            <div wire:click="sort('name')" class="col-span-3 flex items-center text-xs font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider cursor-pointer select-none group">
                <div class="flex items-center">
                    <span>Name</span>
                    <div class="ml-1.5 transition-all duration-200">
                        @if($sortBy === 'name')
                            <div class="h-4 w-4 flex items-center justify-center">
                                @if($sortDirection === 'asc')
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-emerald-500" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
                                    </svg>
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-emerald-500" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                @endif
                            </div>
                        @else
                            <div class="h-4 w-4 flex items-center justify-center opacity-0 group-hover:opacity-70">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M5 10a1 1 0 011-1h8a1 1 0 110 2H6a1 1 0 01-1-1z" />
                                </svg>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="col-span-2 flex items-center text-xs font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                <span>Created By</span>
            </div>
            
            <div wire:click="sort('custom_url')" class="col-span-3 flex items-center text-xs font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider cursor-pointer select-none group">
                <div class="flex items-center">
                    <span>Custom URL</span>
                    <div class="ml-1.5 transition-all duration-200">
                        @if($sortBy === 'custom_url')
                            <div class="h-4 w-4 flex items-center justify-center">
                                @if($sortDirection === 'asc')
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-emerald-500" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
                                    </svg>
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-emerald-500" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                @endif
                            </div>
                        @else
                            <div class="h-4 w-4 flex items-center justify-center opacity-0 group-hover:opacity-70">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M5 10a1 1 0 011-1h8a1 1 0 110 2H6a1 1 0 01-1-1z" />
                                </svg>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="col-span-1 flex items-center text-xs font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                <span>Links</span>
            </div>
            
            <div class="col-span-3 text-xs font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider text-center">
                Actions
            </div>
        </div>
        
        <!-- Enhanced list items with card-like appearance -->
        <div class="divide-y divide-gray-50 dark:divide-neutral-700/40">
            @forelse($lists as $list)
                <!-- List item with improved hover effects -->
                <div class="group block md:grid md:grid-cols-12 md:gap-4 p-5 hover:bg-emerald-50/40 dark:hover:bg-emerald-900/5 transition-all duration-300 relative">
                    <!-- Subtle hover indicator -->
                    <div class="absolute left-0 top-0 bottom-0 w-1 bg-emerald-500/0 group-hover:bg-emerald-500 transition-all duration-300 rounded-r"></div>
                    
                    <!-- List details with enhanced styling -->
                    <div class="md:col-span-3 mb-3 md:mb-0 flex flex-col justify-center">
                        <div class="font-medium text-base text-gray-800 dark:text-gray-200 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors duration-200">
                            {{ $list->name }}
                            
                            <!-- New - verification badge for popular lists -->
                            @if($list->urls_count > 5)
                                <span class="inline-flex ml-1.5 items-center justify-center">
                                    <svg class="w-4 h-4 text-blue-500" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M19.965 8.521C19.988 8.347 20 8.173 20 8c0-2.21-1.79-4-4-4-0.826 0-1.597 0.252-2.236 0.684C13.053 3.41 11.619 2.5 10 2.5c-2.21 0-4 1.79-4 4 0 0.173 0.012 0.347 0.035 0.521C4.266 7.621 3 9.179 3 11c0 2.21 1.79 4 4 4h10c2.21 0 4-1.79 4-4 0-1.821-1.266-3.379-2.035-3.979Z"></path>
                                    </svg>
                                </span>
                            @endif
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 md:hidden">
                            URL: <a href="{{ url('/lists/' . $list->custom_url) }}" target="_blank" class="text-emerald-600 dark:text-emerald-500 hover:underline">{{ $list->custom_url }}</a>
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 md:hidden">
                            Created by: {{ $list->user->name }}
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 md:hidden">
                            URLs: {{ $list->urls_count }}
                        </div>
                        
                        <!-- New - creation date (mobile only) -->
                        <div class="text-xs text-gray-400 dark:text-gray-500 mt-1 md:hidden">
                            Added {{ $list->created_at->diffForHumans() }}
                        </div>
                    </div>
                    
                    <!-- Author with avatar -->
                    <div class="md:col-span-2 mb-3 md:mb-0 hidden md:flex items-center">
                        <div class="flex items-center group/avatar">
                            <div class="flex-shrink-0 h-8 w-8 rounded-full bg-gradient-to-br from-emerald-500 to-teal-500 text-white flex items-center justify-center text-sm font-medium transform group-hover/avatar:scale-110 transition-all duration-300 shadow-md">
                                {{ substr($list->user->name, 0, 1) }}
                            </div>
                            <div class="ml-2 truncate text-sm text-gray-700 dark:text-gray-300">
                                {{ $list->user->name }}
                                
                                <!-- New - subtle creation date -->
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                                    {{ $list->created_at->format('M d, Y') }}
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- URL with animation -->
                    <div class="md:col-span-3 mb-3 md:mb-0 hidden md:flex">
                        <a 
                            href="{{ url('/lists/' . $list->custom_url) }}" 
                            target="_blank" 
                            class="text-emerald-600 dark:text-emerald-500 hover:underline flex items-center group/url relative overflow-hidden" 
                        >
                            <span class="truncate group-hover/url:text-emerald-700 dark:group-hover/url:text-emerald-400 transition-all duration-300">
                                {{ $list->custom_url }}
                            </span>
                            <!-- Improved link icon animation -->
                            <span class="transform translate-x-0 opacity-0 group-hover/url:opacity-100 group-hover/url:translate-x-1 transition-all duration-300 ml-1.5">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M11 3a1 1 0 100 2h2.586l-6.293 6.293a1 1 0 101.414 1.414L15 6.414V9a1 1 0 102 0V4a1 1 0 00-1-1h-5z" />
                                    <path d="M5 5a2 2 0 00-2 2v8a2 2 0 002 2h8a2 2 0 002-2v-3a1 1 0 10-2 0v3H5V7h3a1 1 0 000-2H5z" />
                                </svg>
                            </span>
                        </a>
                    </div>

                    <!-- Links count badge with improved styling -->
                    <div class="md:col-span-1 mb-3 md:mb-0 hidden md:flex items-center">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-800/30 shadow-sm group-hover:shadow group-hover:bg-emerald-100 dark:group-hover:bg-emerald-900/40 transition-all duration-300">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1.5" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z"/>
                            </svg>
                            {{ $list->urls_count }}
                        </span>
                    </div>
                    
                    <!-- Action buttons with enhanced interactions -->
                    <div class="md:col-span-3 flex flex-wrap gap-2 justify-start md:justify-center">
                        <!-- View button with shimmer effect -->
                        <a 
                            href="{{ url('/lists/' . $list->custom_url) }}" 
                            class="relative overflow-hidden inline-flex items-center px-3.5 py-1.5 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white rounded-lg text-xs font-medium transition-all duration-300 shadow-sm hover:shadow group/btn" 
                        >
                            <span class="relative z-10 flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1.5" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                    <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                                </svg>
                                View
                            </span>
                            <!-- Shimmer effect -->
                            <span class="absolute top-0 right-full w-12 h-full bg-white/30 transform rotate-12 translate-x-0 transition-transform duration-1000 ease-out group-hover/btn:translate-x-[400%]"></span>
                        </a>
                        
                        <!-- Request Access button -->
                        @if(auth()->check() && auth()->id() !== $list->user_id)
                            <a 
                                href="{{ url('/lists/' . $list->custom_url) }}" 
                                class="relative overflow-hidden inline-flex items-center px-3.5 py-1.5 bg-gradient-to-r from-teal-500 to-emerald-500 hover:from-teal-600 hover:to-emerald-600 text-white rounded-lg text-xs font-medium transition-all duration-300 shadow-sm hover:shadow group/btn2" 
                            >
                                <span class="relative z-10 flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1.5" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z" />
                                    </svg>
                                    Request Access
                                </span>
                                <!-- Shimmer effect -->
                                <span class="absolute top-0 right-full w-12 h-full bg-white/30 transform rotate-12 translate-x-0 transition-transform duration-1000 ease-out group-hover/btn2:translate-x-[400%]"></span>
                            </a>
                        @endif
                        
                        <!-- New - Copy URL button -->
                        <button
                            onclick="navigator.clipboard.writeText('{{ url('/lists/' . $list->custom_url) }}'); this.querySelector('span').textContent = 'Copied!'; setTimeout(() => this.querySelector('span').textContent = 'Copy URL', 1500)"
                            class="relative overflow-hidden inline-flex items-center px-3.5 py-1.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-lg text-xs font-medium transition-all duration-200 border border-gray-200 dark:border-gray-600"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1.5" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M8 3a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z" />
                                <path d="M6 3a2 2 0 00-2 2v11a2 2 0 002 2h8a2 2 0 002-2V5a2 2 0 00-2-2 3 3 0 01-3 3H9a3 3 0 01-3-3z" />
                            </svg>
                            <span>Copy URL</span>
                        </button>
                    </div>
                </div>
            @empty
                <!-- Enhanced empty state with illustrations -->
                <div class="py-16 px-4 text-center">
                    <div class="w-32 h-32 mx-auto mb-6 bg-gray-50 dark:bg-gray-800/50 rounded-full flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-300 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                    
                    <h3 class="mt-4 text-xl font-semibold text-gray-900 dark:text-gray-100">No public lists found</h3>
                    <p class="mt-2 text-gray-500 dark:text-gray-400 max-w-md mx-auto">
                        There are no published URL lists available right now. Lists will appear here once users make them public.
                    </p>
                    
                    <!-- New - CTA for empty state -->
                    @auth
                        <a href="{{ route('lists.create') }}" class="mt-6 inline-flex items-center px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg text-sm font-medium transition-all duration-200 shadow-sm hover:shadow">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                            </svg>
                            Create your first public list
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="mt-6 inline-flex items-center px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg text-sm font-medium transition-all duration-200 shadow-sm hover:shadow">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M3 3a1 1 0 011 1v12a1 1 0 11-2 0V4a1 1 0 011-1zm7.707 3.293a1 1 0 010 1.414L9.414 9H17a1 1 0 110 2H9.414l1.293 1.293a1 1 0 01-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                            Sign in to create lists
                        </a>
                    @endauth
                </div>
            @endforelse
        </div>
    </div>
    
    <!-- Enhanced pagination with animations -->
    @if($lists->hasPages())
        <div class="mt-6 transform transition-all duration-300 hover:translate-y-[-2px]">
            <div class="bg-white/70 dark:bg-neutral-800/70 backdrop-blur-sm rounded-xl py-3 px-4 shadow-sm border border-gray-100/50 dark:border-neutral-700/50 hover:shadow-md transition-all duration-300">
                {{ $lists->links(data: ['scrollTo' => false]) }}
            </div>
        </div>
    @else
        <!-- Status indicator at the bottom with animation -->
        <div class="mt-6 flex items-center justify-center">
            <div class="bg-white/70 dark:bg-neutral-800/70 backdrop-blur-sm rounded-full py-1.5 px-4 shadow-sm border border-gray-100/50 dark:border-neutral-700/50 hover:shadow transition-all duration-300 transform hover:scale-105">
                <span class="text-xs text-gray-500 dark:text-gray-400">
                    Showing {{ count($lists) }} public {{ Str::plural('list', count($lists)) }}
                </span>
            </div>
        </div>
    @endif
</div>
