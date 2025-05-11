<?php

use Livewire\Volt\Component;
use WireUi\Traits\WireUiActions;
use Illuminate\Support\Facades\Http;

new class extends Component {
    use WireUiActions;

    public $listId;
    public $url = '';
    public $title = '';
    public $description = '';
    public $editingId = null;

    protected $listeners = ['editUrl' => 'startEdit'];

    public function mount($listId)
    {
        $this->listId = $listId;
    }

    public function updatedUrl()
    {
        $this->validateOnly('url');
        if ($this->url && filter_var($this->url, FILTER_VALIDATE_URL)) {
            try {
                $response = Http::timeout(5)->get($this->url);
                if ($response->successful()) {
                    $html = $response->body();
                    preg_match('/<title>(.*?)<\/title>/i', $html, $titleMatches);
                    preg_match('/<meta name="description" content="(.*?)">/i', $html, $descMatches);
                    
                    if (empty($this->title) && !empty($titleMatches[1])) {
                        $this->title = html_entity_decode($titleMatches[1], ENT_QUOTES);
                    }
                    if (empty($this->description) && !empty($descMatches[1])) {
                        $this->description = html_entity_decode($descMatches[1], ENT_QUOTES);
                    }
                }
            } catch (\Exception $e) {
                // Silent fail - we don't want to interrupt the user if metadata fetching fails
            }
        }
    }

    public function addUrl()
    {
        try {
            $this->validate([
                'url' => 'required|url|max:2048',
                'title' => 'nullable|string|max:255',
                'description' => 'nullable|string|max:1000',
            ]);

            $url = \App\Models\Url::create([
                'url_list_id' => $this->listId,
                'url' => $this->url,
                'title' => $this->title,
                'description' => $this->description,
            ]);

            $this->reset(['url', 'title', 'description']);
            $this->dispatch('urlAdded', url: $url->toArray());

            $this->notification()->success(
                title: 'URL Added',
                description: 'The URL was added to your list successfully.'
            );
        } catch (\Exception $e) {
            $this->notification()->error(
                title: 'Error',
                description: 'There was a problem adding the URL. Please try again.'
            );
        }
    }

    public function startEdit($id)
    {
        $url = \App\Models\Url::findOrFail($id);
        $this->editingId = $id;
        $this->url = $url->url;
        $this->title = $url->title;
        $this->description = $url->description;
    }

    public function updateUrl()
    {
        try {
            $this->validate([
                'url' => 'required|url|max:2048',
                'title' => 'nullable|string|max:255',
                'description' => 'nullable|string|max:1000',
            ]);

            $url = \App\Models\Url::findOrFail($this->editingId);
            $url->update([
                'url' => $this->url,
                'title' => $this->title,
                'description' => $this->description,
            ]);

            $this->reset(['url', 'title', 'description', 'editingId']);
            $this->dispatch('urlUpdated', url: $url->fresh()->toArray());

            $this->notification()->success(
                title: 'URL Updated',
                description: 'The URL was updated successfully.'
            );
        } catch (\Exception $e) {
            $this->notification()->error(
                title: 'Error',
                description: 'There was a problem updating the URL. Please try again.'
            );
        }
    }

    public function cancelEdit()
    {
        $this->reset(['url', 'title', 'description', 'editingId']);
        $this->notification()->info(
                title: 'Edit Cancelled',
            description: 'URL editing was cancelled.'
        );
    }
}; ?>

<div class="bg-gray-50 dark:bg-neutral-800 rounded-lg shadow p-6 mb-6">
    <h3 class="text-lg font-semibold mb-4 text-emerald-700 dark:text-emerald-400">{{ $editingId ? 'Edit URL' : 'Add URL' }}</h3>
    @if(session('error'))
        <div class="bg-red-100 text-red-700 p-2 rounded mb-4">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="bg-red-100 text-red-700 p-2 rounded mb-4">
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form wire:submit.prevent="{{ $editingId ? 'updateUrl' : 'addUrl' }}" class="space-y-4">
        <div>
            <label for="url" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">URL</label>
            <input type="text" id="url" wire:model.live="url" wire:change="updatedUrl" required class="w-full rounded-lg border border-gray-300 dark:border-gray-700 px-4 py-2 focus:ring-2 focus:ring-emerald-400 focus:outline-none bg-white dark:bg-neutral-900 text-gray-900 dark:text-gray-100">
            @error('url') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>
        <div>
            <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Title</label>
            <input type="text" id="title" wire:model.live="title" class="w-full rounded-lg border border-gray-300 dark:border-gray-700 px-4 py-2 focus:ring-2 focus:ring-emerald-400 focus:outline-none bg-white dark:bg-neutral-900 text-gray-900 dark:text-gray-100">
            @error('title') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>
        <div>
            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Description</label>
            <textarea id="description" wire:model.live="description" class="w-full rounded-lg border border-gray-300 dark:border-gray-700 px-4 py-2 focus:ring-2 focus:ring-emerald-400 focus:outline-none bg-white dark:bg-neutral-900 text-gray-900 dark:text-gray-100"></textarea>
            @error('description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>
        <div class="flex gap-2">
            <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-4 py-2 rounded-lg transition">{{ $editingId ? 'Update' : 'Add' }}</button>
            @if($editingId)
                <button type="button" wire:click="cancelEdit" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold px-4 py-2 rounded-lg transition">Cancel</button>
            @endif
        </div>
    </form>
</div>
