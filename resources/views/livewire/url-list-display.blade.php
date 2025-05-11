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
    public $isLoading = false;
    public $loadedCount = 0;
    public $totalUrls = 0;
    public $fetchingMetadataFor = [];

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
        $this->initializeUrlMetadata();
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
        
        // Initialize metadata for newly loaded URLs
        $this->initializeUrlMetadata($urls);

        return [
            'urls' => $urls
        ];
    }

    protected function initializeUrlMetadata($urls = null)
    {
        if (!$urls) {
            $urls = $this->list->urls()->get();
        }
        
        foreach ($urls as $url) {
            if (!isset($this->urlMetadata[$url->id])) {
                $this->urlMetadata[$url->id] = [
                    'title' => null,
                    'description' => null,
                    'loading' => false,
                    'error' => false
                ];
                
                // Fetch metadata for new URLs
                $this->fetchUrlMetadata($url->id);
            }
        }
    }

    public function fetchUrlMetadata($id)
    {
        $url = $this->list->urls()->find($id);
        if (!$url || in_array($id, $this->fetchingMetadataFor)) {
            return;
        }

        $this->urlMetadata[$id]['loading'] = true;
        $this->urlMetadata[$id]['error'] = false;
        $this->fetchingMetadataFor[] = $id;
        
        try {
            $response = Http::timeout(5)->get($url->url);
            if ($response->successful()) {
                $html = $response->body();
                preg_match('/<title>(.*?)<\/title>/i', $html, $titleMatches);
                preg_match('/<meta name="description" content="(.*?)">/i', $html, $descMatches);
                
                $this->urlMetadata[$id] = [
                    'title' => !empty($titleMatches[1]) ? html_entity_decode($titleMatches[1], ENT_QUOTES) : null,
                    'description' => !empty($descMatches[1]) ? html_entity_decode($descMatches[1], ENT_QUOTES) : null,
                    'loading' => false,
                    'error' => false
                ];
            } else {
                $this->urlMetadata[$id]['error'] = true;
            }
        } catch (\Exception $e) {
            $this->urlMetadata[$id]['error'] = true;
        }

        $this->urlMetadata[$id]['loading'] = false;
        $this->fetchingMetadataFor = array_diff($this->fetchingMetadataFor, [$id]);
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
    <div class="flex justify-between items-center mb-4">
        <input type="text" 
               wire:model.live="search" 
               placeholder="Search URLs, titles, or descriptions..." 
               class="flex-1 rounded-lg border border-gray-300 dark:border-gray-700 px-4 py-2 focus:ring-2 focus:ring-emerald-400 focus:outline-none bg-white dark:bg-neutral-900 text-gray-900 dark:text-gray-100">
        
        <button wire:click="$set('showAddUrlModal', true)" class="ml-4 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg transition">
            Add URL
        </button>
    </div>

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
                        Title
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
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($urls as $url)
                    <tr wire:key="url-{{ $url->id }}" class="hover:bg-gray-50 dark:hover:bg-neutral-800 transition-colors">
                        <td class="px-6 py-4">
                            <a href="{{ $url->url }}" target="_blank" rel="noopener noreferrer" class="text-emerald-600 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-300 truncate inline-block max-w-xs">
                                {{ $url->url }}
                            </a>
                            <div class="text-xs">
                                @if($urlMetadata[$url->id]['loading'])
                                    <div class="text-gray-500">Loading metadata...</div>
                                @else
                                    @if($urlMetadata[$url->id]['title'])
                                        <div class="text-gray-600 dark:text-gray-400 font-medium">{{ $urlMetadata[$url->id]['title'] }}</div>
                                    @endif
                                    @if($urlMetadata[$url->id]['description'])
                                        <div class="text-gray-500 dark:text-gray-500 line-clamp-2">{{ $urlMetadata[$url->id]['description'] }}</div>
                                    @endif
                                    @if(!$urlMetadata[$url->id]['title'] && !$urlMetadata[$url->id]['description'])
                                        <div class="flex items-center text-gray-500">
                                            <span>{{ $urlMetadata[$url->id]['error'] ? 'Failed to load metadata' : 'No metadata found' }}</span>
                                            <button wire:click="fetchUrlMetadata({{ $url->id }})" class="text-emerald-600 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-300 ml-1">
                                                Retry
                                            </button>
                                        </div>
                                    @endif
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-gray-900 dark:text-gray-100">{{ $url->title ?: 'No title' }}</span>
                            @if($url->description)
                                <p class="text-sm text-gray-500 dark:text-gray-400 truncate max-w-xs">{{ $url->description }}</p>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                            {{ $url->created_at->diffForHumans() }}
                        </td>
                        <td class="px-6 py-4 text-right text-sm">
                            <button wire:click="editUrl({{ $url->id }})" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 mr-3">
                                Edit
                            </button>
                            <button wire:click="deleteUrl({{ $url->id }})" class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300">
                                Delete
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                            No URLs found. Add your first URL above.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($urls->hasPages())
        <div class="mt-6">
            <div class="bg-white/70 dark:bg-neutral-800/70 backdrop-blur-sm rounded-xl py-3 px-4 shadow-sm border border-gray-100/50 dark:border-neutral-700/50">
                {{ $urls->links(data: ['scrollTo' => false]) }}
            </div>
        </div>
    @else
        <!-- Status indicator at the bottom -->
        <div class="mt-6 flex items-center justify-center">
            <div class="bg-white/70 dark:bg-neutral-800/70 backdrop-blur-sm rounded-full py-1.5 px-4 shadow-sm border border-gray-100/50 dark:border-neutral-700/50">
                <span class="text-xs text-gray-500 dark:text-gray-400">
                    Showing {{ $urls->count() }} {{ Str::plural('URL', $urls->count()) }}
                </span>
            </div>
        </div>
    @endif
</div>
