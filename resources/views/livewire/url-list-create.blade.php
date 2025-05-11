<?php

use Livewire\Volt\Component;
use Illuminate\Support\Str;

new class extends Component {
    public string $name = '';
    public string $custom_url = '';
    public ?string $generatedUrl = null;

    public function createList()
    {
        // Sanitize custom_url: replace spaces with dashes and lowercase
        if ($this->custom_url) {
            $this->custom_url = strtolower(preg_replace('/\s+/', '-', $this->custom_url));
        }
        $this->validate([
            'name' => 'required|string|max:255',
            'custom_url' => 'nullable|alpha_dash|unique:url_lists,custom_url',
        ]);

        $customUrl = $this->custom_url ?: $this->generateUniqueUrl();

        $list = \App\Models\UrlList::create([
            'user_id' => auth()->id(),
            'name' => $this->name,
            'custom_url' => $customUrl,
            'published' => false,
        ]);

        $this->generatedUrl = $list->custom_url;
        $this->reset(['name', 'custom_url']);
    }

    private function generateUniqueUrl(): string
    {
        do {
            $url = Str::random(8);
        } while (\App\Models\UrlList::where('custom_url', $url)->exists());
        return $url;
    }
}; ?>

<div class="max-w-lg mx-auto bg-white dark:bg-neutral-900 shadow-lg rounded-xl p-8 mt-8">
    <h2 class="text-2xl font-bold mb-6 text-emerald-600">Create a New URL List</h2>
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
    <form wire:submit.prevent="createList" class="space-y-5">
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">List Name</label>
            <input type="text" id="name" wire:model.defer="name" required class="w-full rounded-lg border border-gray-300 dark:border-gray-700 px-4 py-2 focus:ring-2 focus:ring-emerald-400 focus:outline-none bg-gray-50 dark:bg-neutral-800 text-gray-900 dark:text-gray-100">
            @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>
        <div>
            <label for="custom_url" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Custom URL (optional)</label>
            <input type="text" id="custom_url" wire:model.defer="custom_url" class="w-full rounded-lg border border-gray-300 dark:border-gray-700 px-4 py-2 focus:ring-2 focus:ring-emerald-400 focus:outline-none bg-gray-50 dark:bg-neutral-800 text-gray-900 dark:text-gray-100">
            @error('custom_url') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>
        <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2 rounded-lg transition">Create List</button>
    </form>
    @if($generatedUrl)
        <div class="mt-4 p-3 bg-emerald-50 dark:bg-emerald-900 text-emerald-700 dark:text-emerald-200 rounded-lg text-center">
            List created! Shareable URL:
            <a href="{{ url('/l/' . $generatedUrl) }}" target="_blank" class="underline font-medium" wire:navigate>{{ url('/l/' . $generatedUrl) }}</a>
        </div>
    @endif
</div>
