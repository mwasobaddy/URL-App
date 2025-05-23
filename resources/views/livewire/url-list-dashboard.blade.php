<?php

use Livewire\Volt\Component;
use WireUi\Traits\WireUiActions;
use Livewire\WithPagination;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\UrlList; // Added import

new class extends Component {
    use WireUiActions, WithPagination;

    public $search = '';
    public $sortBy = 'created_at';
    public $sortDirection = 'desc';
    protected $queryString = ['search', 'sortBy', 'sortDirection'];

    public $showDeleteListModal = false;
    public ?UrlList $listToDelete = null;

    public function placeholder()
    {
        return <<<'HTML'
        <div class="max-w-6xl mx-auto backdrop-blur-sm bg-white/90 dark:bg-neutral-800/90 shadow-xl rounded-3xl p-6 lg:p-8 mt-8 border border-gray-100/40 dark:border-neutral-700/50">
            <div class="flex items-center justify-center p-12">
                <div class="flex flex-col items-center">
                    <div class="h-12 w-12 rounded-full border-4 border-emerald-500/30 border-t-emerald-500 animate-spin"></div>
                    <p class="mt-4 text-emerald-600 dark:text-emerald-400 text-sm">Loading your lists...</p>
                </div>
            </div>
        </div>
        HTML;
    }

    public function with(): array
    {
        $query = \App\Models\UrlList::where('user_id', auth()->id())
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

    public function mount()
    {
        // $this->isLoading = false; // isLoading property is not defined, consider removing or defining it.
    }

    public function confirmDeleteList(int $listId)
    {
        $this->listToDelete = UrlList::where('user_id', auth()->id())->find($listId);
        if ($this->listToDelete) {
            $this->showDeleteListModal = true;
        } else {
            $this->notification()->error('Error', 'List not found or you do not have permission to delete it.');
        }
    }

    public function closeDeleteListModal()
    {
        $this->showDeleteListModal = false;
        $this->listToDelete = null;
    }

    public function deleteList()
    {
        if (!$this->listToDelete) {
            $this->notification()->error('Error', 'No list selected for deletion.');
            $this->closeDeleteListModal();
            return;
        }

        try {
            // Ensure the user owns the list before deleting (already implicitly checked by find in confirmDeleteList for this user)
            $listName = $this->listToDelete->name;
            $this->listToDelete->delete();
            
            $this->notification()->success(
                title: 'List Deleted',
                description: "The list '{$listName}' was deleted successfully."
            );
        } catch (\Exception $e) {
            $this->notification()->error(
                title: 'Error',
                description: 'There was a problem deleting the list. Please try again.'
            );
        }
        $this->closeDeleteListModal();
        // Optionally, refresh the list data if not automatically handled by Livewire's rendering cycle
        // $this->dispatch('$refresh'); // or a specific event to re-fetch lists
    }

    public function togglePublish($id)
    {
        try {
            $list = \App\Models\UrlList::where('user_id', auth()->id())->findOrFail($id);
            $list->published = !$list->published;
            $list->save();
            
            $this->notification()->success(
                title: $list->published ? 'List Published' : 'List Unpublished',
                description: $list->published 
                    ? 'Your list is now publicly accessible.'
                    : 'Your list is now private.'
            );
        } catch (\Exception $e) {
            $this->notification()->error(
                title: 'Error',
                description: 'There was a problem updating the list. Please try again.'
            );
        }
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
<div class="max-w-6xl mx-auto backdrop-blur-sm bg-white/90 dark:bg-neutral-800/90 shadow-xl rounded-3xl p-6 lg:p-8 mt-8 border border-gray-100/40 dark:border-neutral-700/50">
    <!-- Header with modern typography and micro-interaction -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-10">
        <div class="relative">
            <h2 class="text-3xl md:text-4xl font-extrabold tracking-tight">
                <span class="bg-clip-text text-transparent bg-gradient-to-br from-emerald-500 to-teal-400">
                    Your URL Lists
                </span>
            </h2>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 max-w-md">
                Organize, share, and track your curated links in one place
            </p>
            <!-- Decorative element -->
            <div class="absolute -bottom-3 left-0 h-1 w-16 bg-gradient-to-r from-emerald-500 to-teal-400 rounded-full"></div>
        </div>
        
        <div class="mt-6 sm:mt-0 relative group">
            <!-- Decorative blob behind button (subtle micro-interaction) -->
            <div class="absolute inset-0 rounded-xl bg-emerald-300/20 dark:bg-emerald-700/20 blur-xl transition-all duration-300 opacity-0 group-hover:opacity-100 scale-0 group-hover:scale-110"></div>
            <a href="{{ route('lists.create') }}" class="relative z-10 inline-flex items-center px-5 py-2.5 bg-gradient-to-br from-emerald-500 to-teal-400 hover:from-emerald-600 hover:to-teal-500 text-white rounded-xl transition-all duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-0.5" wire:navigate>
                <span class="flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    Create New List
                </span>
            </a>
        </div>
    </div>
    
    <!-- Notifications with enhanced styling -->
    @if(session('error'))
        <div class="mb-6 bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400 p-4 rounded-xl border border-red-100 dark:border-red-800/50 flex items-start">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3 mt-0.5 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif
    
    @if($errors->any())
        <div class="mb-6 bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400 p-4 rounded-xl border border-red-100 dark:border-red-800/50">
            <div class="flex items-center mb-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                <span class="font-medium">Please fix the following issues:</span>
            </div>
            <ul class="list-disc pl-10 text-sm space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Modern search component with interactive states -->
    <div class="mb-8 relative max-w-2xl mx-auto">
        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 dark:text-gray-500">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </div>
        <input 
            type="text" 
            wire:model.live.debounce.300ms="search" 
            placeholder="Search lists by name or URL..." 
            class="w-full h-12 rounded-xl border border-gray-200 dark:border-gray-700 pl-12 pr-10 py-3 focus:ring-2 focus:ring-emerald-400/40 focus:border-emerald-400 focus:outline-none bg-white dark:bg-neutral-800/50 text-gray-900 dark:text-gray-100 transition-all duration-200 placeholder-gray-400 dark:placeholder-gray-500"
            wire:loading.class="bg-emerald-50 dark:bg-emerald-900/10"
        >

        <div wire:loading.delay wire:target="search" class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none hidden" wire:loading.class.remove="hidden">
            <div class="h-5 w-5">
                <div class="h-full w-full rounded-full border-2 border-emerald-500/30 border-t-emerald-500 animate-spin"></div>
            </div>
        </div>
        
        <!-- Empty search clear button (only shows when search has content) -->
        @if($search)
            <button 
                wire:click="$set('search', '')" 
                class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors duration-200"
                wire:loading.class="hidden"
                wire:target="search"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
            </button>
        @endif
    </div>

    <!-- Lists Table -->
    <div class="overflow-x-auto bg-white/50 dark:bg-neutral-800/50 rounded-2xl shadow-sm border border-gray-100 dark:border-neutral-700/50 backdrop-blur-sm">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
            <thead class="bg-gray-50/80 dark:bg-neutral-700/20">
                <tr>
                    <th scope="col" wire:click="sort('name')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer select-none group">
                        <div class="flex items-center">
                            Name
                            <span class="ml-1.5 transition-all duration-200">
                                @if($sortBy === 'name')
                                    <svg class="h-4 w-4 text-emerald-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        @if($sortDirection === 'asc')
                                            <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
                                        @else
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        @endif
                                    </svg>
                                @else
                                    <svg class="h-4 w-4 text-gray-400 opacity-0 group-hover:opacity-70" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path d="M5 10a1 1 0 011-1h8a1 1 0 110 2H6a1 1 0 01-1-1z" />
                                    </svg>
                                @endif
                            </span>
                        </div>
                    </th>
                    <th scope="col" wire:click="sort('custom_url')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer select-none group">
                        <div class="flex items-center">
                            Custom URL
                            <span class="ml-1.5 transition-all duration-200">
                                @if($sortBy === 'custom_url')
                                    <svg class="h-4 w-4 text-emerald-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        @if($sortDirection === 'asc')
                                            <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
                                        @else
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        @endif
                                    </svg>
                                @else
                                    <svg class="h-4 w-4 text-gray-400 opacity-0 group-hover:opacity-70" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path d="M5 10a1 1 0 011-1h8a1 1 0 110 2H6a1 1 0 01-1-1z" />
                                    </svg>
                                @endif
                            </span>
                        </div>
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Links
                    </th>
                    <th scope="col" wire:click="sort('published')" class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer select-none group">
                        <div class="flex items-center justify-center">
                            Status
                            <span class="ml-1.5 transition-all duration-200">
                                @if($sortBy === 'published')
                                    <svg class="h-4 w-4 text-emerald-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        @if($sortDirection === 'asc')
                                            <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
                                        @else
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        @endif
                                    </svg>
                                @else
                                    <svg class="h-4 w-4 text-gray-400 opacity-0 group-hover:opacity-70" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path d="M5 10a1 1 0 011-1h8a1 1 0 110 2H6a1 1 0 01-1-1z" />
                                    </svg>
                                @endif
                            </span>
                        </div>
                    </th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-neutral-800 divide-y divide-gray-100 dark:divide-neutral-700/70">
                @forelse($lists as $list)
                    <tr class="group hover:bg-emerald-50/40 dark:hover:bg-emerald-900/5 transition-all duration-150">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors duration-150">
                                {{ $list->name }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <a href="{{ url('/lists/' . $list->custom_url . '/manage/') }}" target="_blank" class="text-sm text-emerald-600 dark:text-emerald-500 hover:underline flex items-center group/link" wire:navigate>
                                <span class="truncate">{{ $list->custom_url }}</span>
                                <span class="transform translate-x-0 opacity-0 group-hover/link:opacity-100 group-hover/link:translate-x-1 transition-all duration-300 ml-1.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M11 3a1 1 0 100 2h2.586l-6.293 6.293a1 1 0 101.414 1.414L15 6.414V9a1 1 0 102 0V4a1 1 0 00-1-1h-5z" />
                                        <path d="M5 5a2 2 0 00-2 2v8a2 2 0 002 2h8a2 2 0 002-2v-3a1 1 0 10-2 0v3H5V7h3a1 1 0 000-2H5z" />
                                    </svg>
                                </span>
                            </a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-400 border border-blue-100 dark:border-blue-800/30">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1.5" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z"/>
                                </svg>
                                {{ $list->urls_count }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            @if($list->published)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-800/30">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1.5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    Published
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-50 text-gray-700 dark:bg-gray-800/30 dark:text-gray-400 border border-gray-100 dark:border-gray-700/30">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1.5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z" clip-rule="evenodd" />
                                        <path d="M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.741L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.065 7 9.542 7 .847 0 1.669-.105 2.454-.303z" />
                                    </svg>
                                    Private
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-center">
                            <div class="flex items-center justify-center space-x-2">
                                <button 
                                    wire:click="togglePublish({{ $list->id }})" 
                                    wire:loading.attr="disabled"
                                    class="relative overflow-hidden {{ $list->published ? 'bg-amber-500 hover:bg-amber-600' : 'bg-emerald-500 hover:bg-emerald-600' }} inline-flex items-center px-3 py-1.5 rounded-lg text-white text-xs font-medium transition-all duration-200 shadow-sm hover:shadow group/button"
                                >
                                    <span class="relative z-10 flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                            @if($list->published)
                                                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                            @else
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.707l-3-3a1 1 0 00-1.414 1.414L10.586 9H7a1 1 0 100 2h3.586l-1.293 1.293a1 1 0 101.414 1.414l3-3a1 1 0 000-1.414z" clip-rule="evenodd" />
                                            @endif
                                        </svg>
                                        {{ $list->published ? 'Unpublish' : 'Publish' }}
                                    </span>
                                    <span class="absolute top-0 right-full w-12 h-full bg-white/30 transform rotate-12 translate-x-0 transition-transform duration-1000 ease-out group-hover/button:translate-x-[400%]"></span>
                                </button>
                                
                                <a 
                                    href="{{ route('lists.show', $list->custom_url) }}" 
                                    class="relative overflow-hidden inline-flex items-center px-3 py-1.5 bg-blue-500 hover:bg-blue-600 text-white rounded-lg text-xs font-medium transition-all duration-200 shadow-sm hover:shadow group/button" 
                                    wire:navigate
                                >
                                    <span class="relative z-10 flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                                        </svg>
                                        View
                                    </span>
                                    <span class="absolute top-0 right-full w-12 h-full bg-white/30 transform rotate-12 translate-x-0 transition-transform duration-1000 ease-out group-hover/button:translate-x-[400%]"></span>
                                </a>
                                
                                <a 
                                    href="{{ route('lists.share', $list->custom_url) }}" 
                                    class="relative overflow-hidden inline-flex items-center px-3 py-1.5 bg-indigo-500 hover:bg-indigo-600 text-white rounded-lg text-xs font-medium transition-all duration-200 shadow-sm hover:shadow group/button" 
                                    wire:navigate
                                >
                                    <span class="relative z-10 flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M15 8a3 3 0 10-2.977-2.63l-4.94 2.47a3 3 0 100 4.319l4.94 2.47a3 3 0 10.895-1.789l-4.94-2.47a3.027 3.027 0 000-.74l4.94-2.47C13.456 7.68 14.19 8 15 8z" />
                                        </svg>
                                        Share
                                    </span>
                                    <span class="absolute top-0 right-full w-12 h-full bg-white/30 transform rotate-12 translate-x-0 transition-transform duration-1000 ease-out group-hover/button:translate-x-[400%]"></span>
                                </a>
                                
                                <button 
                                    wire:click="confirmDeleteList({{ $list->id }})"
                                    wire:loading.attr="disabled"
                                    class="relative overflow-hidden inline-flex items-center px-3 py-1.5 bg-red-500 hover:bg-red-600 text-white rounded-lg text-xs font-medium transition-all duration-200 shadow-sm hover:shadow group/button"
                                >
                                    <span class="relative z-10 flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                        Delete
                                    </span>
                                    <span class="absolute top-0 right-full w-12 h-full bg-white/30 transform rotate-12 translate-x-0 transition-transform duration-1000 ease-out group-hover/button:translate-x-[400%]"></span>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <div class="w-24 h-24 mx-auto mb-4 bg-gray-50 dark:bg-gray-800/50 rounded-full flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-300 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                            </div>
                            <h3 class="mt-4 text-xl font-semibold text-gray-900 dark:text-gray-100">No lists found</h3>
                            <p class="mt-2 text-gray-500 dark:text-gray-400 max-w-md mx-auto">
                                You haven't created any URL lists yet. Start by creating your first collection to organize and share your links.
                            </p>
                            <div class="mt-8">
                                <a 
                                    href="{{ route('lists.create') }}" 
                                    class="inline-flex items-center px-5 py-2.5 bg-gradient-to-br from-emerald-500 to-teal-400 hover:from-emerald-600 hover:to-teal-500 text-white rounded-xl transition-all duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-0.5"
                                    wire:navigate
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                                    </svg>
                                    Create Your First List
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <div class="mt-6">
        {{ $lists->links() }}
    </div>

    <!-- Delete Confirmation Modal -->
    @if ($showDeleteListModal && $listToDelete)
        <flux:modal wire:model.live="showDeleteListModal" name="delete-list-modal" title="{{ __('Confirm Delete List') }}" class="w-auto">
            <div class="relative mb-8">
            <h2 class="text-3xl md:text-4xl font-extrabold tracking-tight">
                <span class="bg-clip-text text-transparent bg-gradient-to-br from-red-500 to-rose-400">
                {{ __('Delete List?') }}
                </span>
            </h2>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 max-w-md">
                {{ __('Are you sure you want to delete the list') }} "<strong>{{ $listToDelete->name }}</strong>"? 
                {{ __('This action cannot be undone, and all associated URLs will also be permanently removed.') }}
            </p>
            <div class="absolute -bottom-3 left-0 h-1 w-16 bg-gradient-to-r from-red-500 to-rose-400 rounded-full"></div>
            </div>

            <div class="mt-6 flex justify-end gap-x-2">
            <flux:button flat type="button" wire:click="closeDeleteListModal">
                {{ __('Cancel') }}
            </flux:button>
            <flux:button type="button" wire:click="deleteList" class="bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg text-sm px-4 py-2 text-center items-center transition-all duration-200 shadow-sm hover:shadow-md" wire:loading.attr="disabled" wire:target="deleteList">
                <span wire:loading.remove wire:target="deleteList">{{ __('Delete List') }}</span>
                <span class="hidden" wire:loading.class.remove="hidden" wire:target="deleteList" >{{ __('Deleting...') }}</span>
            </flux:button>
            </div>
        </flux:modal>
    @endif
</div>
