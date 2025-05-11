<?php

use Livewire\Volt\Component;
use WireUi\Traits\WireUiActions;

new class extends Component {
    use WireUiActions;

    public $list;
    public $shareUrl;

    public function mount($custom_url)
    {
        $this->list = \App\Models\UrlList::where('custom_url', $custom_url)
            ->where('user_id', auth()->id())
            ->firstOrFail();
        $this->shareUrl = url('/lists/' . $this->list->custom_url);
    }

    public function togglePublish()
    {
        try {
            $this->list->published = !$this->list->published;
            $this->list->save();
            $this->list = $this->list->fresh();
            
            $this->notification()->success(
                title: $this->list->published ? 'List Published' : 'List Unpublished',
                description: $this->list->published 
                    ? 'Your list is now publicly accessible.'
                    : 'Your list is now private.'
            );
        } catch (\Exception $e) {
            $this->notification()->error(
                title: 'Error',
                description: 'There was a problem updating the list.'
            );
        }
    }

    public function copyUrl()
    {
        $this->notification()->success(
            
            title: 'URL Copied',
            description: 'The shareable URL has been copied to your clipboard.'
        );
    }
}; ?>

<div class="max-w-lg mx-auto bg-white dark:bg-neutral-900 shadow-lg rounded-xl p-8 mt-8">
    <h3 class="text-xl font-bold mb-6 text-emerald-600">Share List: {{ $list->name }}</h3>
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
    <div class="mb-6">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Shareable URL</label>
        <div class="flex gap-2">
            <input type="text" value="{{ $shareUrl }}" readonly id="share-url" class="flex-1 rounded-lg border border-gray-300 dark:border-gray-700 px-4 py-2 bg-gray-50 dark:bg-neutral-800 text-gray-900 dark:text-gray-100">
            <button type="button" onclick="navigator.clipboard.writeText('{{ $shareUrl }}'); @this.call('copyUrl')" class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-4 py-2 rounded-lg transition">Copy</button>
        </div>
    </div>
    <div class="flex items-center gap-4">
        <button wire:click="togglePublish" class="px-4 py-2 rounded-lg font-semibold transition {{ $list->published ? 'bg-emerald-500 hover:bg-emerald-600 text-white' : 'bg-gray-300 hover:bg-gray-400 text-gray-800' }}">
            {{ $list->published ? 'Unpublish' : 'Publish' }}
        </button>
        <span class="text-sm {{ $list->published ? 'text-emerald-700 dark:text-emerald-300' : 'text-gray-500 dark:text-gray-400' }}">
            {{ $list->published ? 'List is public.' : 'List is private.' }}
        </span>
    </div>
</div>
