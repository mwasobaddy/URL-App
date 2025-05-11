<?php

use Livewire\Volt\Component;
use WireUi\Traits\WireUiActions;
use Livewire\WithPagination;
use function Livewire\Volt\state;
use function Livewire\Volt\computed;

new class extends Component {
    use WireUiActions, WithPagination;

    public $list;
    public $urls;
    public $search = '';
    public $sortBy = 'created_at';
    public $sortDirection = 'desc';
    public $showAddUrlModal = false;
    public $newUrl = '';
    public $newTitle = '';
    public $newDescription = '';

    protected $queryString = ['search', 'sortBy', 'sortDirection'];
    protected $listeners = ['urlAdded' => 'handleUrlAdded', 'urlUpdated' => 'handleUrlUpdated', 'urlDeleted' => 'loadUrls'];

    public function mount($custom_url)
    {
        $query = \App\Models\UrlList::where('custom_url', $custom_url);
        
        // If this is not a public route, ensure the user owns the list
        if (request()->route()->getName() !== 'lists.public') {
            $query->where('user_id', auth()->id());
        } else {
            $query->where('published', true);
        }
        
        $this->list = $query->firstOrFail();
        $this->loadUrls();
    }

    public function loadUrls()
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
        $this->urls = $query->get();
    }

    public function handleUrlAdded($urlData)
    {
        // Add the new URL to the beginning or end of the list based on sort direction
        if ($this->sortDirection === 'desc') {
            $this->urls->prepend(new \App\Models\Url($urlData));
        } else {
            $this->urls->push(new \App\Models\Url($urlData));
        }
    }

    public function handleUrlUpdated($urlData)
    {
        // Update the URL in the existing collection
        $index = $this->urls->search(fn($item) => $item->id === $urlData['id']);
        if ($index !== false) {
            $this->urls[$index] = new \App\Models\Url($urlData);
        }
        // Re-sort the collection if needed
        if ($this->sortBy !== 'created_at') {
            $this->loadUrls();
        }
    }

    public function updatedSearch()
    {
        $this->loadUrls();
    }

    public function deleteUrl($id)
    {
        try {
            $url = $this->list->urls()->findOrFail($id);
            $url->delete();
            $this->urls = $this->urls->reject(fn($item) => $item->id === $id);
            
            $this->notification()->success(
                'URL Deleted',
                'The URL was deleted successfully.'
            );
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
        $this->loadUrls();
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

            $this->loadUrls();
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
                            {{-- display metadata of the link it is not in the database --}}
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

    <flux:modal wire:model="showAddUrlModal" title="Add New URL" class="w-full max-w-lg">
        <div class="modal-header">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Add a new URL to your list</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">You can add a URL, title, and description.</p>
        </div>
        <div class="modal-body">
            <p class="text-sm text-gray-500 dark:text-gray-400">Fill in the details below:</p>
        </div>
        <div class="p-6">
            <form wire:submit="addUrl">
                <div class="space-y-4">
                    <div>
                        <label for="url" class="block text-sm font-medium text-gray-700 dark:text-gray-300">URL</label>
                        <input type="url" 
                               id="url"
                               wire:model="newUrl" 
                               class="w-full rounded-lg border border-gray-300 dark:border-gray-700 px-4 py-2 focus:ring-2 focus:ring-emerald-400 focus:outline-none bg-gray-50 dark:bg-neutral-800 text-gray-900 dark:text-gray-100"
                               required>
                    </div>
                    
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Title (Optional)</label>
                        <input type="text" 
                               id="title"
                               wire:model="newTitle" 
                               class="w-full rounded-lg border border-gray-300 dark:border-gray-700 px-4 py-2 focus:ring-2 focus:ring-emerald-400 focus:outline-none bg-gray-50 dark:bg-neutral-800 text-gray-900 dark:text-gray-100">
                    </div>
                    
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description (Optional)</label>
                        <textarea id="description"
                                 wire:model="newDescription" 
                                 rows="3"
                                 class="w-full rounded-lg border border-gray-300 dark:border-gray-700 px-4 py-2 focus:ring-2 focus:ring-emerald-400 focus:outline-none bg-gray-50 dark:bg-neutral-800 text-gray-900 dark:text-gray-100"></textarea>
                    </div>
                    
                    <div class="flex justify-end gap-3">
                        <button type="button" 
                                wire:click="$set('showAddUrlModal', false)"
                                class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-neutral-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500">
                            Add URL
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </flux:modal>
</div>
