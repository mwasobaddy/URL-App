<x-layouts.app.sidebar>
    {{-- Admin-specific sidebar content --}}
    <x-slot:sidebar>
        <flux:nav>
            <flux:nav-section-label>Administration</flux:nav-section-label>
            
            <flux:nav-link 
                href="{{ route('admin.subscriptions') }}" 
                :active="request()->routeIs('admin.subscriptions*')"
                icon="document-text"
            >
                Subscriptions
            </flux:nav-link>

            <flux:nav-link 
                href="{{ route('admin.users') }}" 
                :active="request()->routeIs('admin.users*')"
                icon="users"
            >
                Users & Roles
            </flux:nav-link>

            <flux:nav-link 
                href="{{ route('admin.plans') }}" 
                :active="request()->routeIs('admin.plans*')"
                icon="tag"
            >
                Plans
            </flux:nav-link>

            <flux:nav-link 
                href="{{ route('admin.analytics') }}" 
                :active="request()->routeIs('admin.analytics*')"
                icon="chart-bar"
            >
                Analytics
            </flux:nav-link>

            <flux:nav-link 
                href="{{ route('admin.logs') }}" 
                :active="request()->routeIs('admin.logs*')"
                icon="clipboard-document-list"
            >
                System Logs
            </flux:nav-link>

            <flux:nav-section-label>System Health</flux:nav-section-label>

            <flux:nav-link 
                href="{{ route('admin.health') }}" 
                :active="request()->routeIs('admin.health*')"
                icon="server"
            >
                Server Status
            </flux:nav-link>

            <flux:nav-link 
                href="{{ route('admin.webhooks') }}" 
                :active="request()->routeIs('admin.webhooks*')"
                icon="arrow-path"
            >
                PayPal Webhooks
            </flux:nav-link>
        </flux:nav>
    </x-slot:sidebar>

    <flux:main container>
        <div class="space-y-6">
            {{ $slot }}
        </div>
    </flux:main>
</x-layouts.app.sidebar>
