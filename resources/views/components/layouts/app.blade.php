<x-layouts.app.sidebar :title="$title ?? null">
    <!-- WireUI Components -->
    <x-notifications z-index="z-50" position="top-end" />
    <x-dialog z-index="z-50" blur="md" align="center" />

    <flux:main>
        {{ $slot }}
    </flux:main>
</x-layouts.app.sidebar>
