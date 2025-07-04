<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Http;
use function Livewire\Volt\state;
use function Livewire\Volt\computed;

new class extends Component {
    use WithPagination;

    public $list;
    public $search = '';
    public $sortBy = 'created_at';
    public $sortDirection = 'desc';
    public $showEditListModal = false;
    public $editListName = '';
    public $editListDescription = '';
    public $editListPublished = false;
    public $showDeleteListModal = false; // Added for delete confirmation
    public $urlMetadata = [];
    public $fetchingMetadata = [];
    public $metadataQueue = [];
    public $isLoading = false;
    public $isEditing = false;
    public $editingUrlId = null;
    public $showUrlModal = false; // Added for direct modal control
    public $urlData = [
        'url' => '',
        'title' => '',
        'description' => ''
    ];
    public $isFetchingMetadataForNewUrl = false; // New property for loading state

    protected $queryString = ['search', 'sortBy', 'sortDirection'];
    protected $listeners = ['urlAdded' => 'handleUrlAdded', 'urlUpdated' => 'handleUrlUpdated', 'urlDeleted' => 'resetPage', 'listUpdated' => '$refresh'];

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

        // Use database title/description as initial fallback
        $this->urlMetadata[$id] = [
            'title' => $url->title, // Initialize with DB title
            'description' => $url->description, // Initialize with DB description
            'loading' => true,
            'error' => false
        ];
        $this->fetchingMetadata[] = $id;

        try {
            $response = Http::timeout(5)->get($url->url);
            if ($response->successful()) {
                $html = $response->body();
                
                preg_match('/<title[^>]*>(.*?)<\/title>/si', $html, $titleMatches);
                $title = !empty($titleMatches[1]) ? html_entity_decode(trim($titleMatches[1]), ENT_QUOTES) : $url->title; // Fallback to DB title
                
                preg_match('/<meta[^>]*name=["\']description["\'][^>]*content=["\']([^>"\']*)["\'][^>]*>/si', $html, $descMatches);
                if (empty($descMatches[1])) {
                    preg_match('/<meta[^>]*content=["\']([^>"\']*)["\'][^>]*name=["\']description["\'][^>]*>/si', $html, $descMatches);
                }
                $description = !empty($descMatches[1]) ? html_entity_decode(trim($descMatches[1]), ENT_QUOTES) : $url->description; // Fallback to DB description
                
                $this->urlMetadata[$id] = [
                    'title' => $title,
                    'description' => $description,
                    'loading' => false,
                    'error' => false
                ];

                // Save to database only if fetched values are different or if DB values were null
                if ($title !== $url->title || $description !== $url->description || is_null($url->title) || is_null($url->description)) {
                    $url->update([
                        'title' => $title,
                        'description' => $description
                    ]);
                }
            } else {
                $this->urlMetadata[$id]['error'] = true;
                $this->urlMetadata[$id]['loading'] = false;
                // Keep existing DB title/description if fetch fails
                $this->urlMetadata[$id]['title'] = $url->title;
                $this->urlMetadata[$id]['description'] = $url->description;
            }
        } catch (\Exception $e) {
            $this->urlMetadata[$id]['error'] = true;
            $this->urlMetadata[$id]['loading'] = false;
            // Keep existing DB title/description on exception
            $this->urlMetadata[$id]['title'] = $url->title;
            $this->urlMetadata[$id]['description'] = $url->description;
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

    public function updatedUrlDataUrl(string $value): void
    {
        // Only fetch if not editing and URL field is not empty
        if ($this->isEditing || empty(trim($value))) {
            $this->isFetchingMetadataForNewUrl = false;
            return;
        }

        $trimmedValue = trim($value);

        // Basic URL validation - ensure it starts with http or https
        if (!preg_match('/^https?:\/\//i', $trimmedValue) || !filter_var($trimmedValue, FILTER_VALIDATE_URL)) {
            // If URL becomes invalid or doesn't have a scheme, clear previously fetched data if title/description are still default from fetch
            // For simplicity, we'll just return. User can clear manually or type a new valid URL.
            $this->isFetchingMetadataForNewUrl = false;
            return;
        }

        $this->isFetchingMetadataForNewUrl = true;

        try {
            $response = \Illuminate\Support\Facades\Http::timeout(5)->get($trimmedValue);

            if ($response->successful()) {
                $html = $response->body();
                $doc = new \DOMDocument();
                libxml_use_internal_errors(true); // Suppress HTML5 parsing errors
                if (!empty($html)) {
                    $doc->loadHTML($html);
                }
                libxml_clear_errors();
                $xpath = new \DOMXPath($doc);

                // Fetch title
                $titleNode = $xpath->query('//title')->item(0);
                $fetchedTitle = $titleNode ? trim($titleNode->textContent) : null;

                // Fetch meta description
                $descriptionNode = $xpath->query("//meta[@name='description']/@content")->item(0);
                $fetchedDescription = $descriptionNode ? trim($descriptionNode->nodeValue) : null;

                // Update title field if it's empty (or only whitespace) and we fetched a title
                if (empty(trim($this->urlData['title'])) && $fetchedTitle) {
                    $this->urlData['title'] = $fetchedTitle;
                }

                // Update description field if it's empty (or only whitespace) and we fetched a description
                if (empty(trim($this->urlData['description'])) && $fetchedDescription) {
                    $this->urlData['description'] = $fetchedDescription;
                }
            }
        } catch (\Throwable $e) {
            // Log error or handle silently. For now, we'll proceed without fetched meta.
            // report($e); // Uncomment to log errors
        } finally {
            $this->isFetchingMetadataForNewUrl = false;
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
            
            $this->resetPage();
        } catch (\Exception $e) {
            // Handle error
        }
    }

    public function showAddUrlModal()
    {
        $this->isEditing = false;
        $this->reset('urlData', 'editingUrlId');
        $this->showUrlModal = true; // Control visibility directly
    }

    public function editUrl($id)
    {
        $this->isEditing = true;
        $this->editingUrlId = $id;
        $url = $this->list->urls()->find($id);
        if ($url) {
            $this->urlData = [
                'url' => $url->url,
                'title' => $url->title,
                'description' => $url->description,
            ];
        }
        $this->showUrlModal = true; // Control visibility directly
    }

    public function saveUrl()
    {
        $this->validate([
            'urlData.url' => 'required|url',
            'urlData.title' => 'nullable|string|max:255',
            'urlData.description' => 'nullable|string|max:500',
        ]);

        if ($this->isEditing) {
            $url = $this->list->urls()->find($this->editingUrlId);
            if ($url) {
                $url->update($this->urlData);
                $this->dispatch('swal:toast', [
                    'type' => 'success',
                    'title' => 'URL updated successfully.'
                ]);
            }
        } else {
            $this->list->urls()->create($this->urlData);
            $this->dispatch('swal:toast', [
                'type' => 'success',
                'title' => 'URL added successfully.'
            ]);
        }

        $this->showUrlModal = false; // Close modal
        $this->reset('urlData', 'editingUrlId', 'isEditing');
        $this->resetPage();
    }

    public function closeUrlModal()
    {
        $this->showUrlModal = false;
        $this->reset('urlData', 'editingUrlId', 'isEditing');
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

    public function editList()
    {
        $this->editListName = $this->list->name;
        $this->editListDescription = $this->list->description; // Populate description
        $this->editListPublished = $this->list->published;
        $this->showEditListModal = true;
    }

    public function updateList()
    {
        $this->validate([
            'editListName' => 'required|min:3|max:255',
            'editListDescription' => 'nullable|string|max:1000', // Added validation for description
        ]);

        try {
            $this->list->update([
                'name' => $this->editListName,
                'description' => $this->editListDescription, // Save description
                'published' => $this->editListPublished,
            ]);

            $this->dispatch('swal:toast', [
                'type' => 'success',
                'title' => 'List settings updated successfully.'
            ]);

            $this->showEditListModal = false;
            $this->dispatch('listUpdated');
        } catch (\Exception $e) {
            // Handle error
            // Optionally, you could dispatch an error toast here as well:
            $this->dispatch('swal:toast', [
                'type' => 'error',
                'title' => 'Failed to update list settings.'
            ]);
            // Optionally, log the error for debugging
            \Log::error('List update failed: ' . $e->getMessage());
        }
    }

    public function confirmDeleteList()
    {
        $this->showDeleteListModal = true;
    }

    public function closeDeleteListModal()
    {
        $this->showDeleteListModal = false;
    }

    public function deleteList()
    {
        if (auth()->id() !== $this->list->user_id) {
            $this->showDeleteListModal = false;
            return;
        }

        try {
            $listName = $this->list->name;
            $this->list->delete(); // Eloquent model delete

            $this->showDeleteListModal = false;
            // Redirect to a relevant page, e.g., dashboard
            return redirect()->route('lists.dashboard'); 
        } catch (\Exception $e) {
            // Handle error
            $this->showDeleteListModal = false;
        }
    }
}; ?>

<div class="max-w-6xl mx-auto my-8 backdrop-blur-sm bg-white/90 dark:bg-neutral-800/90 shadow-xl rounded-3xl p-6 border border-gray-100/40 dark:border-neutral-700/50 transition-all duration-300 relative overflow-hidden">
    <!-- Decorative elements - subtle background patterns -->
    <div class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-bl from-emerald-400/10 to-transparent rounded-full blur-3xl -z-10"></div>
    <div class="absolute bottom-0 left-0 w-80 h-80 bg-gradient-to-tr from-teal-400/10 to-transparent rounded-full blur-3xl -z-10"></div>
        
    <!-- Header with modern typography -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-10">
        <div class="relative">
            <h2 class="text-3xl md:text-4xl font-extrabold tracking-tight">
                <span class="bg-clip-text text-transparent bg-gradient-to-br from-emerald-500 to-teal-400">
                    {{ $list->name }}
                </span>
                @if(auth()->check() && $list->user_id === auth()->id())
                    <button wire:click="editList" class="inline-block ml-2 text-emerald-500 hover:text-emerald-600 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                        </svg>
                    </button>
                @endif
            </h2>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 max-w-md">
                @if($list->description)
                    {{ $list->description }} <!-- Display list description -->
                @else
                    A curated collection of URLs
                @endif
            </p>
            <!-- Decorative element -->
            <div class="absolute -bottom-3 left-0 h-1 w-16 bg-gradient-to-r from-emerald-500 to-teal-400 rounded-full"></div>
        </div>
        
        <div class="flex space-x-2 mt-6 sm:mt-0 space-y-2 sm:space-y-0 flex-wrap">
            @if(auth()->check() && ($list->user_id === auth()->id() || $list->isCollaborator(auth()->id())))
                <!-- Add Link Button -->
                <button wire:click="showAddUrlModal" class="relative overflow-hidden inline-flex items-center px-4 py-2 bg-gradient-to-r from-purple-500 to-indigo-500 hover:from-purple-600 hover:to-indigo-600 text-white rounded-lg text-sm font-medium transition-all duration-200 shadow-sm hover:shadow">
                    <span class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        Add Link
                    </span>
                    <span class="absolute top-0 right-full w-12 h-full bg-white/30 transform rotate-12 translate-x-0 transition-transform duration-1000 ease-out group-hover:translate-x-[400%]"></span>
                </button>
            @endif
            
            @if(auth()->check() && $list->user_id === auth()->id())
                <a href="{{ route('lists.share', $list->custom_url) }}" class="relative overflow-hidden inline-flex items-center px-4 py-2 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white rounded-lg text-sm font-medium transition-all duration-200 shadow-sm hover:shadow" wire:navigate>
                    <span class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                        </svg>
                        Share
                    </span>
                    <span class="absolute top-0 right-full w-12 h-full bg-white/30 transform rotate-12 translate-x-0 transition-transform duration-1000 ease-out group-hover:translate-x-[400%]"></span>
                </a>
                <a href="{{ route('lists.access', $list) }}" class="relative overflow-hidden inline-flex items-center px-4 py-2 bg-gradient-to-r from-teal-500 to-emerald-500 hover:from-teal-600 hover:to-emerald-600 text-white rounded-lg text-sm font-medium transition-all duration-200 shadow-sm hover:shadow" wire:navigate>
                    <flux:icon name="users" class="me-2 size-4" />
                    {{ __('Manage Access') }}
                </a>
                <a href="{{ route('lists.dashboard') }}" class="relative overflow-hidden inline-flex items-center px-4 py-2 bg-gradient-to-r from-emerald-400 to-teal-400 hover:from-emerald-500 hover:to-teal-500 text-white rounded-lg text-sm font-medium transition-all duration-200 shadow-sm hover:shadow" wire:navigate>
                    <span class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        Dashboard
                    </span>
                    <span class="absolute top-0 right-full w-12 h-full bg-white/30 transform rotate-12 translate-x-0 transition-transform duration-1000 ease-out group-hover:translate-x-[400%]"></span>
                </a>
                <!-- Delete List Button -->
                <button wire:click="confirmDeleteList" class="relative overflow-hidden inline-flex items-center px-4 py-2 bg-gradient-to-r from-red-500 to-rose-500 hover:from-red-600 hover:to-rose-600 text-white rounded-lg text-sm font-medium transition-all duration-200 shadow-sm hover:shadow">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    {{ __('Delete List') }}
                </button>
            @elseif(auth()->check() && $list->published)
                <!-- Display request access component for authenticated users who are not the owner -->
                <livewire:request-list-access :urlList="$list" />
                
                <a href="{{ route('lists.dashboard') }}" class="relative overflow-hidden inline-flex items-center px-4 py-2 bg-gradient-to-r from-emerald-400 to-teal-400 hover:from-emerald-500 hover:to-teal-500 text-white rounded-lg text-sm font-medium transition-all duration-200 shadow-sm hover:shadow" wire:navigate>
                    <span class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        Dashboard
                    </span>
                    <span class="absolute top-0 right-full w-12 h-full bg-white/30 transform rotate-12 translate-x-0 transition-transform duration-1000 ease-out group-hover:translate-x-[400%]"></span>
                </a>
            @elseif(!auth()->check())
                <a href="{{ route('login') }}" class="relative overflow-hidden inline-flex items-center px-4 py-2 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white rounded-lg text-sm font-medium transition-all duration-200 shadow-sm hover:shadow" wire:navigate>
                    <span class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                        </svg>
                        Sign in
                    </span>
                    <span class="absolute top-0 right-full w-12 h-full bg-white/30 transform rotate-12 translate-x-0 transition-transform duration-1000 ease-out group-hover:translate-x-[400%]"></span>
                </a>
                <a href="{{ route('register') }}" class="relative overflow-hidden inline-flex items-center px-4 py-2 bg-gradient-to-r from-teal-500 to-emerald-500 hover:from-teal-600 hover:to-emerald-600 text-white rounded-lg text-sm font-medium transition-all duration-200 shadow-sm hover:shadow" wire:navigate>
                    <span class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                        </svg>
                        Join for free
                    </span>
                    <span class="absolute top-0 right-full w-12 h-full bg-white/30 transform rotate-12 translate-x-0 transition-transform duration-1000 ease-out group-hover:translate-x-[400%]"></span>
                </a>
            @endif
        </div>
    </div>

    <!-- Page meta information -->
    <div class="flex flex-wrap items-center gap-4 mb-8 text-sm text-gray-500 dark:text-gray-400">
        <div class="flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>Created {{ $list->created_at->diffForHumans() }}</span>
        </div>
        <div class="flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            <span>By {{ $list->user->name }}</span>
        </div>
        <div class="flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
            </svg>
            <span>{{ $list->urls_count ?? $urls->total() }} {{ Str::plural('link', $urls->total()) }}</span>
        </div>
    </div>

    @if(auth()->check() && ($list->user_id === auth()->id() || $list->isCollaborator(auth()->id())))
        <!-- Search and Add URL Button -->
        <div class="mb-8 relative max-w-2xl mx-auto">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 dark:text-gray-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <input 
                type="text" 
                wire:model.live.debounce.300ms="search" 
                placeholder="Search URLs, titles, or descriptions..." 
                class="w-full h-12 rounded-xl border border-gray-200 dark:border-gray-700 pl-12 pr-10 py-3 focus:ring-2 focus:ring-emerald-400/40 focus:border-emerald-400 focus:outline-none bg-white dark:bg-neutral-800/50 text-gray-900 dark:text-gray-100 transition-all duration-200 placeholder-gray-400 dark:placeholder-gray-500"
                wire:loading.class="bg-emerald-50 dark:bg-emerald-900/10"
            >
            
            <div wire:loading.delay wire:target="search" class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none hidden" wire:loading.class.remove="hidden">
                <div class="h-5 w-5">
                    <div class="h-full w-full rounded-full border-2 border-emerald-500/30 border-t-emerald-500 animate-spin"></div>
                </div>
            </div>
            
            <button wire:click="showAddUrlModal" class="absolute inset-y-0 right-0 pr-3 flex items-center text-emerald-600 hover:text-emerald-800 dark:text-emerald-400 dark:hover:text-emerald-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                </svg>
            </button>
        </div>
    @elseif($search)
        <!-- Search only for public lists -->
        <div class="mb-8 relative max-w-2xl mx-auto">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 dark:text-gray-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <input 
                type="text" 
                wire:model.live.debounce.300ms="search" 
                placeholder="Search URLs, titles, or descriptions..." 
                class="w-full h-12 rounded-xl border border-gray-200 dark:border-gray-700 pl-12 pr-10 py-3 focus:ring-2 focus:ring-emerald-400/40 focus:border-emerald-400 focus:outline-none bg-white dark:bg-neutral-800/50 text-gray-900 dark:text-gray-100 transition-all duration-200 placeholder-gray-400 dark:placeholder-gray-500"
                wire:loading.class="bg-emerald-50 dark:bg-emerald-900/10"
            >
            
            <div wire:loading.delay wire:target="search" class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none hidden" wire:loading.class.remove="hidden">
                <div class="h-5 w-5">
                    <div class="h-full w-full rounded-full border-2 border-emerald-500/30 border-t-emerald-500 animate-spin"></div>
                </div>
            </div>
            
            @if($search)
                <button 
                    wire:click="$set('search', '')" 
                    class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors duration-200"
                    wire:loading.class="hidden"
                    wire:target="search"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293-1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                </button>
            @endif
        </div>
    @else 
        <!-- Search button for public lists to reveal search field -->
        <div class="mb-8 flex justify-center">
            <button 
                wire:click="$toggle('search')" 
                class="flex items-center px-4 py-2 bg-white/80 dark:bg-neutral-800/80 border border-gray-200 dark:border-gray-700 rounded-lg text-gray-600 dark:text-gray-300 hover:bg-emerald-50 dark:hover:bg-emerald-900/10 transition-colors duration-200 shadow-sm"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                Search in this list
            </button>
        </div>
    @endif

    <!-- URLs Table/Cards -->
    <div class="bg-white/50 dark:bg-neutral-800/50 rounded-2xl overflow-hidden shadow-sm border border-gray-100 dark:border-neutral-700/50 backdrop-blur-sm">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 p-4">
            @forelse($urls as $url)
                <div wire:key="url-{{ $url->id }}" class="flex flex-col bg-white dark:bg-neutral-900 rounded-xl shadow-sm border border-gray-100 dark:border-neutral-700/50 overflow-hidden hover:shadow-md transition-shadow duration-300">
                    <a href="{{ $url->url }}" target="_blank" rel="noopener noreferrer" class="block p-4 pb-0 flex-1">
                        <div class="flex items-center mb-3">
                            <div class="h-8 w-8 rounded-md flex items-center justify-center bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 mr-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                </svg>
                            </div>
                            @if(isset($urlMetadata[$url->id]) && $urlMetadata[$url->id]['title'])
                                <h3 class="font-medium text-gray-900 dark:text-white truncate flex-1">{{ $urlMetadata[$url->id]['title'] }}</h3>
                            @elseif($url->title) {{-- Fallback to DB title --}}
                                <h3 class="font-medium text-gray-900 dark:text-white truncate flex-1">{{ $url->title }}</h3>
                            @else
                                <h3 class="font-medium text-gray-900 dark:text-white truncate flex-1">{{ parse_url($url->url, PHP_URL_HOST) }}</h3>
                            @endif
                        </div>
                        
                        @if(isset($urlMetadata[$url->id]) && $urlMetadata[$url->id]['loading'])
                            <div class="text-sm text-gray-500 dark:text-gray-400 flex items-center mb-3">
                                <div class="h-4 w-4 mr-2">
                                    <div class="h-full w-full rounded-full border-2 border-emerald-500/30 border-t-emerald-500 animate-spin"></div>
                                </div>
                                Loading metadata...
                            </div>
                        @elseif(isset($urlMetadata[$url->id]) && $urlMetadata[$url->id]['error'] && !$url->title && !$url->description)
                            <div class="text-sm text-gray-500 dark:text-gray-400 mb-3">
                                <div class="flex items-center mb-1">
                                    <svg class="h-4 w-4 text-amber-500 mr-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                    Metadata unavailable
                                </div>
                                <div class="truncate text-xs opacity-70">{{ $url->url }}</div>
                            </div>
                        @elseif(isset($urlMetadata[$url->id]) && $urlMetadata[$url->id]['description'])
                            <div class="text-sm text-gray-500 dark:text-gray-400 mb-3">
                                <div class="line-clamp-3">{{ $urlMetadata[$url->id]['description'] }}</div>
                                <div class="mt-2 truncate text-xs opacity-70">{{ $url->url }}</div>
                            </div>
                        @elseif($url->description) {{-- Fallback to DB description --}}
                            <div class="text-sm text-gray-500 dark:text-gray-400 mb-3">
                                <div class="line-clamp-3">{{ $url->description }}</div>
                                <div class="mt-2 truncate text-xs opacity-70">{{ $url->url }}</div>
                            </div>
                        @else
                            <div class="text-sm text-gray-500 dark:text-gray-400 mb-3 truncate">{{ $url->url }}</div>
                        @endif
                    </a>
                        
                    <div class="pb-4 px-4 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 mt-4 pt-3 border-t border-gray-100 dark:border-neutral-800">
                        <span>Added {{ $url->created_at->diffForHumans() }}</span>
                        
                        @if(auth()->check() && ($list->user_id === auth()->id() || $list->isCollaborator(auth()->id())))
                            <div class="flex space-x-2">
                                <!-- Edit button -->
                                <button type="button" wire:click="editUrl({{ $url->id }})" class="text-emerald-600 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-300 transition-colors duration-200">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                    </svg>
                                </button>
                                <!-- Delete button -->
                                @if($list->user_id === auth()->id())
                                    <button type="button" wire:click="deleteUrl({{ $url->id }})" class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 transition-colors duration-200">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                @endif
                            </div>
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-emerald-500" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.05 3.636a1 1 0 010 1.414 7 7 0 000 9.9 1 1 0 11-1.414 1.414 9 9 0 010-12.728 1 1 0 011.414 0zm9.9 0a1 1 0 011.414 0 9 9 0 010 12.728 1 1 0 11-1.414-1.414 7 7 0 000-9.9 1 1 0 010-1.414zM7.879 6.464a1 1 0 010 1.414 3 3 0 000 4.243 1 1 0 11-1.414 1.414 5 5 0 010-7.07 1 1 0 011.414 0zm4.242 0a1 1 0 011.414 0 5 5 0 010 7.072 1 1 0 01-1.414-1.415 3 3 0 000-4.242 1 1 0 010-1.415z" clip-rule="evenodd" />
                            </svg>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-span-full py-16 px-4 text-center">
                    <div class="w-24 h-24 mx-auto mb-4 bg-gray-50 dark:bg-gray-800/50 rounded-full flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-300 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                        </svg>
                    </div>
                    
                    <h3 class="mt-4 text-xl font-semibold text-gray-900 dark:text-gray-100">No URLs found</h3>
                    <p class="mt-2 text-gray-500 dark:text-gray-400 max-w-md mx-auto">
                        @if(auth()->check() && ($list->user_id === auth()->id() || $list->isCollaborator(auth()->id())))
                            This list is empty. Add your first URL by clicking the button above.
                        @else
                            This list is empty. Please check back later for content.
                        @endif
                    </p>
                </div>
            @endforelse
        </div>
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

    <!-- Unified Add/Edit URL Modal -->
    <flux:modal wire:model.live="showUrlModal" name="url-modal" variant="default" title="{{ $isEditing ? 'Edit URL' : 'Add New URL' }}" class="w-auto">
        <!-- Decorative elements - subtle background patterns -->
        <div class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-bl from-emerald-400/10 to-transparent rounded-full blur-3xl -z-10"></div>
        <div class="absolute bottom-0 left-0 w-80 h-80 bg-gradient-to-tr from-teal-400/10 to-transparent rounded-full blur-3xl -z-10"></div>
            
        <form wire:submit.prevent="saveUrl">
            <div class="space-y-4">
                <div class="relative mb-8">
                    <h2 class="text-3xl md:text-4xl font-extrabold tracking-tight">
                        <span class="bg-clip-text text-transparent bg-gradient-to-br from-emerald-500 to-teal-400">
                            {{ $isEditing ? 'Edit URL' : 'Add New URL' }}
                        </span>
                    </h2>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 max-w-md">
                        {{ $isEditing ? 'Update the URL and its details below.' : 'Add a new URL to your list.' }}
                    </p>
                    <!-- Decorative element -->
                    <div class="absolute -bottom-3 left-0 h-1 w-16 bg-gradient-to-r from-emerald-500 to-teal-400 rounded-full"></div>
                </div>

                <flux:input
                    wire:model.live.debounce.750ms="urlData.url"
                    label="URL"
                    placeholder="https://example.com"
                    required
                />
                <div class="relative">
                    <flux:input
                        wire:model="urlData.title"
                        label="Title (Optional)"
                        placeholder="Page title"
                    />
                    <div x-show="$wire.isFetchingMetadataForNewUrl && !$wire.isEditing" class="absolute top-0 right-0 mt-2 mr-2" wire:loading wire:target="updatedUrlDataUrl">
                        <svg class="animate-spin h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                </div>
                <div class="relative">
                    <flux:textarea
                        wire:model.live="urlData.description"
                        label="Description (Optional)"
                        placeholder="Brief description of the URL"
                        rows="3"
                    />
                    <div x-show="$wire.isFetchingMetadataForNewUrl && !$wire.isEditing" class="absolute top-0 right-0 mt-2 mr-2" wire:loading wire:target="updatedUrlDataUrl">
                         <svg class="animate-spin h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-x-2">
                <flux:button flat type="button" wire:click="closeUrlModal">
                    {{ __('Cancel') }}
                </flux:button>
                <flux:button type="submit" primary wire:loading.attr="disabled" class="relative items-center font-medium justify-center gap-2 whitespace-nowrap disabled:opacity-75 dark:disabled:opacity-75 disabled:cursor-default disabled:pointer-events-none h-10 text-sm rounded-lg px-4 inline-flex  bg-[var(--color-accent)] hover:bg-[color-mix(in_oklab,_var(--color-accent),_transparent_10%)] text-[var(--color-accent-foreground)] border border-black/10 dark:border-0 shadow-[inset_0px_1px_--theme(--color-white/.2)] [[data-flux-button-group]_&]:border-e-0 [:is([data-flux-button-group]>&:last-child,_[data-flux-button-group]_:last-child>&)]:border-e-[1px] dark:[:is([data-flux-button-group]>&:last-child,_[data-flux-button-group]_:last-child>&)]:border-e-0 dark:[:is([data-flux-button-group]>&:last-child,_[data-flux-button-group]_:last-child>&)]:border-s-[1px] [:is([data-flux-button-group]>&:not(:first-child),_[data-flux-button-group]_:not(:first-child)>&)]:border-s-[color-mix(in_srgb,var(--color-accent-foreground),transparent_85%)] *:transition-opacity [&[disabled]>:not([data-flux-loading-indicator])]:opacity-0 [&[disabled]>[data-flux-loading-indicator]]:opacity-100 [&[disabled]]:pointer-events-none bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white shadow-sm transition-all duration-300 hover:shadow-md dark:from-emerald-600 dark:to-teal-600 dark:hover:from-emerald-500 dark:hover:to-teal-500">
                    {{ $isEditing ? 'Update URL' : 'Add URL' }}
                </flux:button>
            </div>
        </form>
    </flux:modal>

    <!-- Edit List Modal -->
    @if(auth()->check() && $list->user_id === auth()->id())
        <flux:modal wire:model.live="showEditListModal" title="Edit Your List" class="w-auto">
            <form wire:submit="updateList">
                <div class="space-y-4">
                    <div class="relative mb-8">
                        <h2 class="text-3xl md:text-4xl font-extrabold tracking-tight">
                            <span class="bg-clip-text text-transparent bg-gradient-to-br from-emerald-500 to-teal-400">
                                {{ __('List Settings') }}
                            </span>
                        </h2>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 max-w-md">
                            {{ __('You can change the name, description, and visibility of your list.') }}
                        </p>
                        <!-- Decorative element -->
                        <div class="absolute -bottom-3 left-0 h-1 w-16 bg-gradient-to-r from-emerald-500 to-teal-400 rounded-full"></div>
                    </div>
                    
                    <flux:input
                        wire:model="editListName"
                        label="List Name"
                        placeholder="Enter list name"
                        required
                    />
                    
                    <flux:textarea
                        wire:model="editListDescription"
                        label="Description (Optional)"
                        placeholder="What kind of links will this list contain?"
                        rows="3"
                    />
                    
                    <div class="bg-gray-50 dark:bg-gray-800/50 p-3 rounded-lg">
                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Visibility Settings</h4>
                        <x-checkbox
                            wire:model="editListPublished"
                            label="Make this list public (published)"
                        />
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                            When published, your list will be accessible to anyone with the link.
                        </p>
                    </div>
                </div>
                
                <div class="mt-6 flex justify-end gap-x-2">
                    <flux:button flat wire:click="$toggle('showEditListModal')">
                        {{ __('Cancel') }}
                    </flux:button>
                    <flux:button type="submit" primary wire:loading.attr="disabled" class="relative items-center font-medium justify-center gap-2 whitespace-nowrap disabled:opacity-75 dark:disabled:opacity-75 disabled:cursor-default disabled:pointer-events-none h-10 text-sm rounded-lg px-4 inline-flex  bg-[var(--color-accent)] hover:bg-[color-mix(in_oklab,_var(--color-accent),_transparent_10%)] text-[var(--color-accent-foreground)] border border-black/10 dark:border-0 shadow-[inset_0px_1px_--theme(--color-white/.2)] [[data-flux-button-group]_&]:border-e-0 [:is([data-flux-button-group]>&:last-child,_[data-flux-button-group]_:last-child>&)]:border-e-[1px] dark:[:is([data-flux-button-group]>&:last-child,_[data-flux-button-group]_:last-child>&)]:border-e-0 dark:[:is([data-flux-button-group]>&:last-child,_[data-flux-button-group]_:last-child>&)]:border-s-[1px] [:is([data-flux-button-group]>&:not(:first-child),_[data-flux-button-group]_:not(:first-child)>&)]:border-s-[color-mix(in_srgb,var(--color-accent-foreground),transparent_85%)] *:transition-opacity [&[disabled]>:not([data-flux-loading-indicator])]:opacity-0 [&[disabled]>[data-flux-loading-indicator]]:opacity-100 [&[disabled]]:pointer-events-none bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white shadow-sm transition-all duration-300 hover:shadow-md dark:from-emerald-600 dark:to-teal-600 dark:hover:from-emerald-500 dark:hover:to-teal-500">
                        {{ __('Save Changes') }}
                    </flux:button>
                </div>
            </form>
        </flux:modal>
    @endif

    <!-- Delete List Confirmation Modal -->
    @if(auth()->check() && $list->user_id === auth()->id())
        <flux:modal wire:model.live="showDeleteListModal" name="delete-list-modal" title="{{ __('Confirm Delete List') }}" class="w-auto">
            <div class="relative mb-8">
                <h2 class="text-3xl md:text-4xl font-extrabold tracking-tight">
                    <span class="bg-clip-text text-transparent bg-gradient-to-br from-red-500 to-rose-400">
                        {{ __('Delete List?') }}
                    </span>
                </h2>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 max-w-md">
                    {{ __('Are you sure you want to delete the list') }} "<strong>{{ $list->name }}</strong>"? 
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

