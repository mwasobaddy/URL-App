<?php

use Livewire\Volt\Component;

new class extends Component {
    public $list;
    public $urls;

    public function mount($custom_url)
    {
        $this->list = \App\Models\UrlList::where('custom_url', $custom_url)
            ->firstOrFail();
        // Only allow access if published or user is owner
        if (!$this->list->published && (!auth()->check() || $this->list->user_id !== auth()->id())) {
            abort(403, 'You are not authorized to view this list.');
        }
        $this->urls = $this->list->urls()->latest()->get();
    }

    public function deleteUrl($id)
    {
        $url = $this->list->urls()->findOrFail($id);
        $url->delete();
        $this->urls = $this->list->urls()->latest()->get();
    }

    public function editUrl($id)
    {
        $this->dispatch('editUrl', $id);
    }

    public function refreshUrls()
    {
        $this->urls = $this->list->urls()->latest()->get();
    }
}; ?>

<div class="max-w-4xl mx-auto bg-white dark:bg-neutral-900 shadow-lg rounded-xl p-8 mt-8">
    <h2 class="text-2xl font-bold mb-6 text-emerald-600">List: {{ $list->name }}</h2>
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
    {{-- URL Manager (add/edit form) --}}
    <div class="mb-8">
        @livewire('url-manager', ['listId' => $list->id], key('url-manager-' . $list->id))
    </div>
    {{-- Listen for urlUpdated event to refresh URLs --}}
    <script>
        Livewire.on('urlUpdated', () => Livewire.dispatchSelf('refreshUrls'));
    </script>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-neutral-800">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wider">URL</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wider">Title</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wider">Description</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-neutral-900 divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($urls as $url)
                    <tr class="hover:bg-emerald-50 dark:hover:bg-emerald-900/30 transition">
                        <td class="px-4 py-3">
                            <a href="{{ $url->url }}" target="_blank" class="text-emerald-600 underline hover:text-emerald-800 break-all" wire:navigate>{{ $url->url }}</a>
                        </td>
                        <td class="px-4 py-3">{{ $url->title }}</td>
                        <td class="px-4 py-3">{{ $url->description }}</td>
                        <td class="px-4 py-3 flex gap-2 flex-wrap">
                            <button wire:click="editUrl({{ $url->id }})" class="px-3 py-1 rounded bg-blue-500 hover:bg-blue-600 text-white text-xs font-semibold transition">Edit</button>
                            <button wire:click="deleteUrl({{ $url->id }})" onclick="return confirm('Delete this URL?')" class="px-3 py-1 rounded bg-red-500 hover:bg-red-600 text-white text-xs font-semibold transition">Delete</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">No URLs found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
