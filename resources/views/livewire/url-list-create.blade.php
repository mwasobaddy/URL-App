<?php

use Livewire\Volt\Component;
use Illuminate\Support\Str;
use WireUi\Traits\WireUiActions;

new class extends Component {
    use WireUiActions;

    public string $name = '';
    public string $custom_url = '';
    public ?string $generatedUrl = null;
    public string $placeholderUrl = '';

    public function mount()
    {
        $this->placeholderUrl = $this->generateUniqueUrl();
    }

    public function updatingCustomUrl($value)
    {
        if (empty($value)) {
            $this->placeholderUrl = $this->generateUniqueUrl();
        }
    }

    public function createList()
    {
        try {
            // If custom_url is empty, use the placeholder
            if (empty($this->custom_url)) {
                $this->custom_url = $this->placeholderUrl;
            } else {
                // Sanitize custom_url: replace spaces with dashes and lowercase
                $this->custom_url = strtolower(preg_replace('/\s+/', '-', $this->custom_url));
            }

            $this->validate([
                'name' => 'required|string|max:255',
                'custom_url' => 'required|alpha_dash|unique:url_lists,custom_url',
            ]);

            $list = \App\Models\UrlList::create([
                'user_id' => auth()->id(),
                'name' => $this->name,
                'custom_url' => $this->custom_url,
                'published' => false,
            ]);
            
            $this->generatedUrl = $list->custom_url;
            $this->reset(['name', 'custom_url']);
            $this->placeholderUrl = $this->generateUniqueUrl();
            
            $this->notification()->success(
                title: 'List Created',
                description: 'Your new URL list was created successfully!'
            );
        } catch (\Exception $e) {
            $this->notification()->error(
                title: 'Error',
                description: 'There was a problem creating your list. Please try again.'
            );
        }
    }

    private function generateUniqueUrl(): string
    {
        do {
            $url = Str::random(8);
        } while (\App\Models\UrlList::where('custom_url', $url)->exists());
        return $url;
    }
}; ?>

<!-- Main container with glass morphism effect -->
<div class="max-w-2xl mx-auto backdrop-blur-sm bg-white/90 dark:bg-neutral-800/90 shadow-xl rounded-3xl p-8 mt-8 border border-gray-100/40 dark:border-neutral-700/50">
    <!-- Header with modern typography and micro-interaction -->
    <div class="relative mb-8">
        <h2 class="text-3xl md:text-4xl font-extrabold tracking-tight">
            <span class="bg-clip-text text-transparent bg-gradient-to-br from-emerald-500 to-teal-400">
                Create New List
            </span>
        </h2>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 max-w-md">
            Start organizing your links by creating a new collection
        </p>
        <!-- Decorative element -->
        <div class="absolute -bottom-3 left-0 h-1 w-16 bg-gradient-to-r from-emerald-500 to-teal-400 rounded-full"></div>
    </div>

    <!-- Enhanced error notifications -->
    @if(session('error'))
        <div class="mb-6 bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400 p-4 rounded-xl border border-red-100 dark:border-red-800/50 flex items-start">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3 mt-0.5 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400 p-4 rounded-xl border border-red-100 dark:border-red-800/50">
            <div class="flex items-center mb-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                <span class="font-medium">Please fix the following issues:</span>
            </div>
            <ul class="list-disc pl-10 text-sm space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Modern form with enhanced styling -->
    <form wire:submit.prevent="createList" class="space-y-6">
        <!-- List name input with floating label -->
        <div class="relative group">
            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 group-focus-within:text-emerald-500 transition-colors duration-200">
                List Name
            </label>
            <div class="relative">
                <input 
                    type="text" 
                    id="name" 
                    wire:model.defer="name" 
                    required 
                    class="w-full h-12 pl-4 pr-10 rounded-xl border border-gray-200 dark:border-gray-700 focus:ring-2 focus:ring-emerald-400/40 focus:border-emerald-400 focus:outline-none bg-white dark:bg-neutral-800/50 text-gray-900 dark:text-gray-100 transition-all duration-200 placeholder-gray-400 dark:placeholder-gray-500"
                    placeholder="Enter a name for your list"
                >
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
            </div>
            @error('name') 
                <p class="mt-1 text-sm text-red-600 dark:text-red-500 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    {{ $message }}
                </p>
            @enderror
        </div>

        <!-- Custom URL input with advanced features -->
        <div class="relative group">
            <label for="custom_url" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 group-focus-within:text-emerald-500 transition-colors duration-200">
                Custom URL <span class="text-gray-400 dark:text-gray-500">(optional)</span>
            </label>
            <div class="relative flex items-center">
                <span class="left-4 text-gray-400 dark:text-gray-500 select-none">{{ url('/lists/') }}/</span>
                <input 
                    type="text" 
                    id="custom_url" 
                    wire:model.live="custom_url" 
                    class="w-full h-12 pl-4 pr-10 rounded-xl border border-gray-200 dark:border-gray-700 focus:ring-2 focus:ring-emerald-400/40 focus:border-emerald-400 focus:outline-none bg-white dark:bg-neutral-800/50 text-gray-900 dark:text-gray-100 transition-all duration-200 placeholder-gray-400 dark:placeholder-gray-500"
                    placeholder="{{ $placeholderUrl }}"
                >
                <div class="absolute right-3 flex items-center pointer-events-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                    </svg>
                </div>
            </div>
            <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">
                A random URL will be used if left empty
            </p>
            @error('custom_url')
                <p class="mt-1 text-sm text-red-600 dark:text-red-500 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    {{ $message }}
                </p>
            @enderror
        </div>

        <!-- Enhanced submit button with loading state -->
        <div class="pt-4">
            <button 
                type="submit" 
                class="relative w-full h-12 bg-gradient-to-br from-emerald-500 to-teal-400 hover:from-emerald-600 hover:to-teal-500 text-white font-medium rounded-xl transition-all duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-emerald-400/40 overflow-hidden group disabled:opacity-70 disabled:cursor-not-allowed"
                wire:loading.attr="disabled"
                wire:target="createList"
            >
                <span class="relative z-10 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor" wire:loading.remove wire:target="createList">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    <span class="hidden" wire:loading.class.remove="hidden" wire:target="createList" >Creating...</span>
                    <svg class="animate-spin h-5 w-5 mr-2 hidden" wire:loading.class.remove="hidden" wire:target="createList" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span wire:loading.remove wire:target="createList">Create List</span>
                </span>
                <!-- Button shine effect -->
                <span class="absolute top-0 right-full w-12 h-full bg-white/30 transform rotate-12 translate-x-0 transition-transform duration-1000 ease-out group-hover:translate-x-[400%]"></span>
            </button>
        </div>
    </form>

    <!-- Success message with modern styling -->
    @if($generatedUrl)
        <div class="mt-8 relative">
            <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/5 to-teal-400/5 rounded-xl"></div>
            <div class="relative p-6 bg-white/40 dark:bg-neutral-800/40 backdrop-blur-sm rounded-xl border border-emerald-100/50 dark:border-emerald-800/50">
                <div class="flex items-center text-emerald-700 dark:text-emerald-300 mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <span class="font-medium">List created successfully!</span>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-300 mb-4">Your list is ready to share:</p>
                <div class="relative group">
                    <div class="flex items-center bg-emerald-50 dark:bg-emerald-900/20 rounded-lg p-3 pr-12 transition-colors duration-200">
                        <a 
                            href="{{ url('/lists/' . $generatedUrl . '/manage') }}" 
                            target="_blank" 
                            class="text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300 font-medium truncate" 
                            wire:navigate
                        >
                            {{ url('/lists/' . $generatedUrl . '/manage') }}
                        </a>
                        <button 
                            type="button"
                            class="absolute right-3 p-1.5 text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300 bg-emerald-100 dark:bg-emerald-800/50 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200"
                            onclick="navigator.clipboard.writeText('{{ url('/lists/' . $generatedUrl . '/manage') }}').then(() => $wireui.notify({
                                title: 'URL Copied!',
                                description: 'The URL has been copied to your clipboard.',
                                icon: 'success'
                            }))"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M8 3a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z" />
                                <path d="M6 3a2 2 0 00-2 2v11a2 2 0 002 2h8a2 2 0 002-2V5a2 2 0 00-2-2 3 3 0 01-3 3H9a3 3 0 01-3-3z" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
