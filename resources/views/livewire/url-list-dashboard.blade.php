<?php

use Livewire\Volt\Component;

new class extends Component {
    public $lists;

    public function mount()
    {
        $this->lists = \App\Models\UrlList::where('user_id', auth()->id())->latest()->get();
    }

    public function deleteList($id)
    {
        $list = \App\Models\UrlList::where('user_id', auth()->id())->findOrFail($id);
        $list->delete();
        $this->lists = \App\Models\UrlList::where('user_id', auth()->id())->latest()->get();
    }

    public function togglePublish($id)
    {
        $list = \App\Models\UrlList::where('user_id', auth()->id())->findOrFail($id);
        $list->published = !$list->published;
        $list->save();
        $this->lists = \App\Models\UrlList::where('user_id', auth()->id())->latest()->get();
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
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-neutral-800">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wider">Name</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wider">Custom URL</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wider">Published</th>
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
