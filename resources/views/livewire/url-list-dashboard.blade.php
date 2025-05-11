<?php

use Livewire\Volt\Component;
use WireUi\Traits\WireUiActions;
use Livewire\WithPagination;

new class extends Component {
    use WireUiActions, WithPagination;

    public $lists;
    public $search = '';
    public $sortBy = 'created_at';
    public $sortDirection = 'desc';
    protected $queryString = ['search', 'sortBy', 'sortDirection'];

    public function mount()
    {
        $this->loadLists();
    }

    public function loadLists()
    {
        $query = \App\Models\UrlList::where('user_id', auth()->id());
        
        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('custom_url', 'like', '%' . $this->search . '%');
            });
        }

        $query->orderBy($this->sortBy, $this->sortDirection);

        $this->lists = $query->get();
    }

    public function updatedSearch()
    {
        $this->loadLists();
    }

    public function deleteList($id)
    {
        try {
            $list = \App\Models\UrlList::where('user_id', auth()->id())->findOrFail($id);
            $list->delete();
            $this->loadLists();
            
            $this->notification()->success(
                title: 'List Deleted',
                description: 'The URL list was deleted successfully.'
            );
        } catch (\Exception $e) {
            $this->notification()->error(
                title: 'Error',
                description: 'There was a problem deleting the list. Please try again.'
            );
        }
    }

    public function togglePublish($id)
    {
        try {
            $list = \App\Models\UrlList::where('user_id', auth()->id())->findOrFail($id);
            $list->published = !$list->published;
            $list->save();
            $this->loadLists();
            
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
        $this->loadLists();
    }
}; ?>

<div class="max-w-4xl mx-auto bg-white dark:bg-neutral-900 shadow-lg rounded-xl p-8 mt-8">
    <h2 class="text-2xl font-bold mb-6 text-emerald-600">Your URL Lists</h2>
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

    <div class="mb-4">
        <input type="text" 
               wire:model.live="search" 
               placeholder="Search lists by name or URL..." 
               class="w-full rounded-lg border border-gray-300 dark:border-gray-700 px-4 py-2 focus:ring-2 focus:ring-emerald-400 focus:outline-none bg-white dark:bg-neutral-900 text-gray-900 dark:text-gray-100">
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-neutral-800">
                <tr>
                    <th wire:click="sort('name')" class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wider cursor-pointer group">
                        Name
                        @if($sortBy === 'name')
                            <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                        @endif
                    </th>
                    <th wire:click="sort('custom_url')" class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wider cursor-pointer group">
                        Custom URL
                        @if($sortBy === 'custom_url')
                            <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                        @endif
                    </th>
                    <th wire:click="sort('published')" class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wider cursor-pointer group">
                        Published
                        @if($sortBy === 'published')
                            <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                        @endif
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-neutral-900 divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($lists as $list)
                    <tr class="hover:bg-emerald-50 dark:hover:bg-emerald-900/30 transition">
                        <td class="px-4 py-3 font-medium">{{ $list->name }}</td>
                        <td class="px-4 py-3">
                            <a href="{{ url('/l/' . $list->custom_url) }}" target="_blank" class="text-emerald-600 underline hover:text-emerald-800" wire:navigate>{{ $list->custom_url }}</a>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-block px-2 py-1 rounded text-xs font-semibold {{ $list->published ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-200 text-gray-600' }}">
                                {{ $list->published ? 'Yes' : 'No' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 flex gap-2 flex-wrap">
                            <button wire:click="togglePublish({{ $list->id }})" class="px-3 py-1 rounded bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-semibold transition">
                                {{ $list->published ? 'Unpublish' : 'Publish' }}
                            </button>
                            <a href="{{ route('lists.show', $list->custom_url) }}" class="px-3 py-1 rounded bg-blue-500 hover:bg-blue-600 text-white text-xs font-semibold transition" wire:navigate>View</a>
                            <a href="{{ route('lists.share', $list->custom_url) }}" class="px-3 py-1 rounded bg-amber-500 hover:bg-amber-600 text-white text-xs font-semibold transition" wire:navigate>Share</a>
                            <button wire:click="deleteList({{ $list->id }})" onclick="return confirm('Delete this list?')" class="px-3 py-1 rounded bg-red-500 hover:bg-red-600 text-white text-xs font-semibold transition">Delete</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">No lists found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
