<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\UrlList; // Added import

new class extends Component {
    use WithPagination;

    public $search = '';
    public $sortBy = 'created_at';
    public $sortDirection = 'desc';
    protected $queryString = ['search', 'sortBy', 'sortDirection'];

    public $showDeleteListModal = false;
    public ?UrlList $listToDelete = null;

    protected $listeners = ['listDeleted' => 'showListDeletedToast', 'listUpdated' => 'showListUpdatedToast'];

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
            // Use SweetAlert2 for error notification
            $this->dispatch('swal:toast', [
                'type' => 'error',
                'title' => 'List not found or you do not have permission to delete it.',
            ]);
            $this->closeDeleteListModal(); // Added to close the modal if the list is not found
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
            // Use SweetAlert2 for error notification
             $this->dispatch('swal:toast', [
                'type' => 'error',
                'title' => 'No list selected for deletion.',
            ]);
            $this->closeDeleteListModal();
            return;
        }

        try {
            // Ensure the user owns the list before deleting (already implicitly checked by find in confirmDeleteList for this user)
            $listName = $this->listToDelete->name;
            $this->listToDelete->delete();
            
            $this->dispatch('listDeleted', listId: $this->listToDelete->id, listName: $listName);
        } catch (\Exception $e) {
            // Use SweetAlert2 for error notification
            $this->dispatch('swal:toast', [
                'type' => 'error',
                'title' => 'There was a problem deleting the list. Please try again.',
            ]);
            // Log the error for debugging purposes
            \Log::error('Error deleting list: ' . $e->getMessage());
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
            $this->dispatch('listUpdated', listId: $list->id, published: $list->published, listName: $list->name);
        } catch (\Exception $e) {
            // Use SweetAlert2 for error notification
            $this->dispatch('swal:toast', [
                'type' => 'error',
                'title' => 'There was a problem updating the list.',
            ]);
            // Log the error for debugging purposes
            \Log::error('Error updating list: ' . $e->getMessage());
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

    public function showListDeletedToast(int $listId, string $listName)
    {
        $this->dispatch('swal:toast', [
            'type' => 'success',
            'title' => "The list '{$listName}' has been deleted successfully.",
        ]);
    }

    public function showListUpdatedToast(int $listId, bool $published, string $listName)
    {
        $status = $published ? 'published' : 'unpublished';
        $this->dispatch('swal:toast', [
            'type' => 'success',
            'title' => "The list '{$listName}' has been {$status} successfully.",
        ]);
    }
}; ?>

<!-- Main container with glass morphism effect -->
<div class="max-w-6xl mx-auto my-8 backdrop-blur-sm bg-white/90 dark:bg-neutral-800/90 shadow-xl rounded-3xl p-6 border border-gray-100/40 dark:border-neutral-700/50 transition-all duration-300 relative overflow-hidden">
    <!-- Decorative elements - subtle background patterns -->
    <div class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-bl from-emerald-400/10 to-transparent rounded-full blur-3xl -z-10"></div>
    <div class="absolute bottom-0 left-0 w-80 h-80 bg-gradient-to-tr from-teal-400/10 to-transparent rounded-full blur-3xl -z-10"></div>
    
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

    {{-- URL Lists Grid Cards --}}
    <div class="bg-white/50 dark:bg-neutral-800/50 rounded-2xl overflow-hidden shadow-sm border border-gray-100 dark:border-neutral-700/50 backdrop-blur-sm">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 p-4">
            @forelse($lists as $list)
                <div wire:key="list-{{ $list->id }}" class="flex flex-col bg-white dark:bg-neutral-800 rounded-xl shadow-sm border border-gray-100 dark:border-neutral-700/50 overflow-hidden hover:shadow-md transition-shadow duration-300">
                    <a href="{{ route('lists.show', $list->custom_url) }}" wire:navigate class="block p-4 pb-0 flex-1">
                        <div class="flex items-center mb-3">
                            <div class="h-8 w-8 rounded-md flex items-center justify-center bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 mr-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h8m-8 6h16" />
                                </svg>
                            </div>
                            <h3 class="font-medium text-gray-900 dark:text-white truncate flex-1">{{ $list->name }}</h3>
                        </div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-2 line-clamp-2">{{ $list->description ?? 'No description provided.' }}</p>
                        <div class="text-xs text-gray-400 dark:text-gray-500">
                            <span class="font-medium">{{ $list->urls_count }}</span> {{ Str::plural('URL', $list->urls_count) }} • Created {{ $list->created_at->format('M d, Y') }}
                        </div>
                    </a>
                    <div class="px-4 pb-4 pt-3 border-t border-gray-100 dark:border-neutral-800/60 flex justify-between flex-col text-xs text-gray-500 dark:text-gray-400 space-y-2">
                        
                        <div>
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
                                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                                    </svg>
                                    Private
                                </span>
                            @endif
                        </div>

                        <div class="flex space-y-2 space-x-2 flex-wrap">
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
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-12 bg-white dark:bg-neutral-800/70 shadow-md rounded-xl border border-gray-200 dark:border-neutral-700/50">
                    <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                    <h3 class="mt-2 text-sm font-semibold text-gray-800 dark:text-gray-200">No lists found</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Start by creating a new list via the button above.</p>
                </div>
            @endforelse
        </div>
    </div>
    {{-- Pagination --}}
    @if($lists->hasPages())
        <div class="mt-6 px-2">
            {{ $lists->links() }}
        </div>
    @endif

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
            <flux:button type="button" wire:click="deleteList" class="relative overflow-hidden inline-flex items-center px-4 py-2 bg-gradient-to-r from-red-500 to-rose-500 hover:from-red-600 hover:to-rose-600 text-white rounded-lg text-sm font-medium transition-all duration-200 shadow-sm hover:shadow" wire:loading.attr="disabled" wire:target="deleteList">
                <span wire:loading.remove wire:target="deleteList">{{ __('Delete List') }}</span>
                <span class="hidden" wire:loading.class.remove="hidden" wire:target="deleteList">{{ __('Deleting...') }}</span>
            </flux:button>
            </div>
        </flux:modal>
    @endif
</div>
