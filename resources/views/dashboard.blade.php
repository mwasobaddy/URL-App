<x-layouts.app :title="__('Dashboard')">
    <div class="max-w-3xl mx-auto mt-12">
        <div class="bg-white dark:bg-neutral-900 shadow-lg rounded-xl p-8 flex flex-col items-center">
            <h1 class="text-3xl font-bold text-emerald-600 mb-4">Welcome to Your Dashboard</h1>
            <p class="text-gray-600 dark:text-gray-300 mb-8 text-center">
                Manage your URL lists, create new ones, and share your favorite resources with ease.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 w-full justify-center mb-6">
                <a href="{{ route('lists.dashboard') }}" class="w-full sm:w-auto bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-3 px-6 rounded-lg text-center transition" wire:navigate>My URL Lists</a>
                <a href="{{ route('lists.create') }}" class="w-full sm:w-auto bg-emerald-100 hover:bg-emerald-200 text-emerald-700 font-semibold py-3 px-6 rounded-lg text-center transition" wire:navigate>Create New List</a>
            </div>
        </div>
    </div>
</x-layouts.app>
