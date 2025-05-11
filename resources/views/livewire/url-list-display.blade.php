<?php

use Livewire\Volt\Component;
use WireUi\Traits\WireUiActions;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Http;
use function Livewire\Volt\state;
use function Livewire\Volt\computed;

new class extends Component {
    use WireUiActions, WithPagination;

    public $list;
    public $search = '';
    public $sortBy = 'created_at';
    public $sortDirection = 'desc';
    public $showAddUrlModal = false;
    public $newUrl = '';
    public $newTitle = '';
    public $newDescription = '';
    public $urlMetadata = [];
    public $fetchingMetadata = [];
    public $metadataQueue = [];
    public $isLoading = false;

    protected $queryString = ['search', 'sortBy', 'sortDirection'];
    protected $listeners = ['urlAdded' => 'handleUrlAdded', 'urlUpdated' => 'handleUrlUpdated', 'urlDeleted' => 'resetPage'];

    public function mount($custom_url)
    {
        $query = \App\Models\UrlList::where('custom_url', $custom_url);
        
        if (request()->route()->getName() !== 'lists.public') {
            $query->where('user_id', auth()->id());
        } else {
            $query->where('published', true);
        }
        
        $this->list = $query->firstOrFail();
    }

    public function with(): array
    {
        $query = $this->list->urls();
        
        if ($this->search) {
            $query->where(function($q) {
                $q->where('url', 'like', '%' . $this->search . '%')
                  ->orWhere('title', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        $query->orderBy($this->sortBy, $this->sortDirection);
        $urls = $query->paginate(10);

        // Initialize metadata for URLs that don't have it yet
        foreach ($urls as $url) {
            if (!isset($this->urlMetadata[$url->id])) {
                $this->urlMetadata[$url->id] = [
                    'title' => null,
                    'description' => null,
                    'loading' => false,
                    'error' => false
                ];
                $this->metadataQueue[] = $url->id;
            }
        }

        // Process metadata queue in chunks
        $this->processMetadataQueue();

        return [
            'urls' => $urls
        ];
    }

    protected function processMetadataQueue()
    {
        // Process up to 3 URLs at a time
        $chunk = array_slice($this->metadataQueue, 0, 3);
        foreach ($chunk as $urlId) {
            if (!in_array($urlId, $this->fetchingMetadata)) {
                $this->fetchUrlMetadata($urlId);
            }
        }
        $this->metadataQueue = array_diff($this->metadataQueue, $chunk);
    }

    public function fetchUrlMetadata($id)
    {
        if (in_array($id, $this->fetchingMetadata)) {
            return;
        }

        $url = $this->list->urls()->find($id);
        if (!$url) {
            return;
        }

        $this->urlMetadata[$id]['loading'] = true;
        $this->urlMetadata[$id]['error'] = false;
        $this->fetchingMetadata[] = $id;

        try {
            $response = Http::timeout(5)->get($url->url);
            if ($response->successful()) {
                $html = $response->body();
                
                // Extract title
                preg_match('/<title[^>]*>(.*?)<\/title>/si', $html, $titleMatches);
                $title = !empty($titleMatches[1]) ? html_entity_decode(trim($titleMatches[1]), ENT_QUOTES) : null;
                
                // Extract meta description
                preg_match('/<meta[^>]*name=["\']description["\'][^>]*content=["\']([^>"\']*)["\'][^>]*>/si', $html, $descMatches);
                if (empty($descMatches[1])) {
                    preg_match('/<meta[^>]*content=["\']([^>"\']*)["\'][^>]*name=["\']description["\'][^>]*>/si', $html, $descMatches);
                }
                $description = !empty($descMatches[1]) ? html_entity_decode(trim($descMatches[1]), ENT_QUOTES) : null;
                
                $this->urlMetadata[$id] = [
                    'title' => $title,
                    'description' => $description,
                    'loading' => false,
                    'error' => false
                ];

                // Save to database
                $url->update([
                    'title' => $title,
                    'description' => $description
                ]);
            } else {
                $this->urlMetadata[$id]['error'] = true;
                $this->urlMetadata[$id]['loading'] = false;
            }
        } catch (\Exception $e) {
            $this->urlMetadata[$id]['error'] = true;
            $this->urlMetadata[$id]['loading'] = false;
        }

        $this->fetchingMetadata = array_diff($this->fetchingMetadata, [$id]);
    }

    public function retryMetadata($id)
    {
        if (isset($this->urlMetadata[$id])) {
            $this->urlMetadata[$id] = [
                'title' => null,
                'description' => null,
                'loading' => false,
                'error' => false
            ];
            $this->metadataQueue[] = $id;
            $this->processMetadataQueue();
        }
    }

    public function handleUrlAdded($urlData) 
    {
        $this->resetPage();
    }

    public function handleUrlUpdated($urlData)
    {
        if ($this->sortBy !== 'created_at') {
            $this->resetPage();
        }
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function deleteUrl($id)
    {
        try {
            $url = $this->list->urls()->findOrFail($id);
            $url->delete();
            
            $this->notification()->success(
                'URL Deleted',
                'The URL was deleted successfully.'
            );

            $this->resetPage();
        } catch (\Exception $e) {
            $this->notification()->error(
                'Error',
                'There was a problem deleting the URL. Please try again.'
            );
        }
    }

    public function editUrl($id)
    {
        $this->dispatch('editUrl', $id);
    }

    public function toggleSort($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function addUrl()
    {
        $this->validate([
            'newUrl' => 'required|url'
        ]);

        try {
            $url = $this->list->urls()->create([
                'url' => $this->newUrl,
                'title' => $this->newTitle,
                'description' => $this->newDescription
            ]);

            $this->newUrl = '';
            $this->newTitle = '';
            $this->newDescription = '';
            $this->showAddUrlModal = false;
            
            $this->notification()->success(
                'URL Added',
                'The URL was successfully added to your list.'
            );

            $this->resetPage();
        } catch (\Exception $e) {
            $this->notification()->error(
                'Error',
                'There was a problem adding the URL. Please try again.'
            );
        }
    }
}; ?>

<div class="space-y-4">
    @if(auth()->check() && $list->user_id === auth()->id())
        <!-- Search and Add URL Button -->
        <div class="flex justify-between items-center mb-4">
            <input type="text" 
                wire:model.live.debounce.300ms="search" 
                placeholder="Search URLs, titles, or descriptions..." 
                class="flex-1 rounded-lg border border-gray-300 dark:border-gray-700 px-4 py-2 focus:ring-2 focus:ring-emerald-400 focus:outline-none bg-white dark:bg-neutral-900 text-gray-900 dark:text-gray-100">
            
            <button wire:click="$set('showAddUrlModal', true)" class="ml-4 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg transition">
                Add URL
            </button>
        </div>
    @elseif($search)
        <!-- Search only for public lists -->
        <div class="mb-4">
            <input type="text" 
                wire:model.live.debounce.300ms="search" 
                placeholder="Search URLs, titles, or descriptions..." 
                class="w-full rounded-lg border border-gray-300 dark:border-gray-700 px-4 py-2 focus:ring-2 focus:ring-emerald-400 focus:outline-none bg-white dark:bg-neutral-900 text-gray-900 dark:text-gray-100">
        </div>
    @endif

    <!-- URLs Table -->
    <div class="overflow-x-auto bg-white dark:bg-neutral-900 rounded-lg shadow">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-neutral-800">
                <tr>
                    <th wire:click="toggleSort('url')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:text-emerald-600 dark:hover:text-emerald-400">
                        URL
                        @if($sortBy === 'url')
                            <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                        @endif
                    </th>
                    <th wire:click="toggleSort('title')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:text-emerald-600 dark:hover:text-emerald-400">
                        Title & Description
                        @if($sortBy === 'title')
                            <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                        @endif
                    </th>
                    <th wire:click="toggleSort('created_at')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:text-emerald-600 dark:hover:text-emerald-400">
                        Added
                        @if($sortBy === 'created_at')
                            <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                        @endif
                    </th>
                    @if(auth()->check() && $list->user_id === auth()->id())
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Actions
                        </th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($urls as $url)
                    <tr wire:key="url-{{ $url->id }}" class="group hover:bg-gray-50 dark:hover:bg-neutral-800 transition-colors">
                        <td class="px-6 py-4">
                            <a href="{{ $url->url }}" target="_blank" rel="noopener noreferrer" class="text-emerald-600 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-300 truncate inline-block max-w-xs">
                                {{ $url->url }}
                            </a>
                        </td>
                        <td class="px-6 py-4">
                            @if(isset($urlMetadata[$url->id]))
                                @if($urlMetadata[$url->id]['loading'])
                                    <div class="flex items-center text-gray-500">
                                        <svg class="animate-spin h-4 w-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Loading metadata...
                                    </div>
                                @elseif($urlMetadata[$url->id]['error'])
                                    <div class="flex items-center text-gray-500 text-sm">
                                        <svg class="h-4 w-4 text-red-500 mr-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                        </svg>
                                        Failed to load metadata
                                        <button wire:click="retryMetadata({{ $url->id }})" class="ml-2 text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 dark:hover:text-emerald-300">
                                            Retry
                                        </button>
                                    </div>
                                @else
                                    @if($urlMetadata[$url->id]['title'])
                                        <div class="text-gray-900 dark:text-gray-100 font-medium">
                                            {{ $urlMetadata[$url->id]['title'] }}
                                        </div>
                                    @endif
                                    @if($urlMetadata[$url->id]['description'])
                                        <div class="mt-1 text-sm text-gray-500 dark:text-gray-400 line-clamp-2">
                                            {{ $urlMetadata[$url->id]['description'] }}
                                        </div>
                                    @endif
                                @endif
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                            {{ $url->created_at->diffForHumans() }}
                        </td>
                        @if(auth()->check() && $list->user_id === auth()->id())
                            <td class="px-6 py-4 text-right text-sm whitespace-nowrap">
                                <button wire:click="editUrl({{ $url->id }})" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 mr-3">
                                    Edit
                                </button>
                                <button wire:click="deleteUrl({{ $url->id }})" class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300">
                                    Delete
                                </button>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ auth()->check() && $list->user_id === auth()->id() ? '4' : '3' }}" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                            No URLs found. @if(auth()->check() && $list->user_id === auth()->id()) Add your first URL above. @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        @if($urls->hasPages())
            <div class="bg-white/70 dark:bg-neutral-800/70 backdrop-blur-sm rounded-xl py-3 px-4 shadow-sm border border-gray-100/50 dark:border-neutral-700/50">
                {{ $urls->links(data: ['scrollTo' => false]) }}
            </div>
        @else
            <div class="flex items-center justify-center">
                <div class="bg-white/70 dark:bg-neutral-800/70 backdrop-blur-sm rounded-full py-1.5 px-4 shadow-sm border border-gray-100/50 dark:border-neutral-700/50">
                    <span class="text-xs text-gray-500 dark:text-gray-400">
                        Showing {{ $urls->count() }} {{ Str::plural('URL', $urls->count()) }}
                    </span>
                </div>
            </div>
        @endif
    </div>
</div>
