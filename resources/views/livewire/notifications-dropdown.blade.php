<div class="relative">
    <button
        wire:click="toggleDropdown"
        class="relative flex items-center p-2 text-sm font-medium text-gray-700 rounded-full hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-gray-300 dark:hover:bg-gray-700"
    >
        <span class="sr-only">View notifications</span>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        @if ($unreadCount > 0)
            <span class="absolute top-0 right-0 block h-4 w-4 rounded-full bg-red-500 text-xs text-white text-center">
                {{ $unreadCount }}
            </span>
        @endif
    </button>

    @if ($showDropdown)
        <div 
            class="absolute right-0 z-10 mt-2 w-80 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none dark:bg-gray-800 dark:ring-gray-700"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="transform opacity-0 scale-95"
            x-transition:enter-end="transform opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="transform opacity-100 scale-100"
            x-transition:leave-end="transform opacity-0 scale-95"
        >
            <div class="p-3 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Notifications</h3>
                    @if ($unreadCount > 0)
                        <button 
                            wire:click="markAllAsRead" 
                            class="text-xs text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                        >
                            Mark all as read
                        </button>
                    @endif
                </div>
            </div>
            <div class="max-h-60 overflow-y-auto">
                @forelse ($notifications as $notification)
                    <div 
                        class="p-3 border-b border-gray-100 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700 {{ $notification->read_at ? 'bg-white dark:bg-gray-800' : 'bg-blue-50 dark:bg-gray-700' }}"
                    >
                        <div class="flex items-start">
                            <div class="flex-1">
                                <div class="flex justify-between">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                                        @if ($notification->data['type'] === 'access_request')
                                            Access Request
                                        @elseif ($notification->data['type'] === 'access_response')
                                            Access Response
                                        @else
                                            Notification
                                        @endif
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $notification->created_at->diffForHumans() }}</p>
                                </div>
                                
                                <div class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                                    @if ($notification->data['type'] === 'access_request')
                                        <p><strong>{{ $notification->data['requester_name'] }}</strong> has requested edit access to your list <strong>{{ $notification->data['list_name'] }}</strong>.</p>
                                        <div class="mt-2">
                                            <a href="{{ route('lists.access', $notification->data['list_id']) }}" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                                View Request
                                            </a>
                                        </div>
                                    @elseif ($notification->data['type'] === 'access_response')
                                        <p>
                                            Your access request for <strong>{{ $notification->data['list_name'] }}</strong> has been 
                                            <strong class="{{ $notification->data['status'] === 'approved' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                                {{ $notification->data['status'] }}
                                            </strong>.
                                        </p>
                                        @if ($notification->data['status'] === 'approved')
                                            <div class="mt-2">
                                                <a href="{{ route('lists.show', $notification->data['list_id']) }}" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                                    View List
                                                </a>
                                            </div>
                                        @endif
                                    @endif
                                </div>
                                
                                @if (!$notification->read_at)
                                    <div class="mt-2 text-right">
                                        <button
                                            wire:click="markAsRead('{{ $notification->id }}')"
                                            class="text-xs text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                                        >
                                            Mark as read
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-4 text-center text-gray-500 dark:text-gray-400">
                        No notifications yet
                    </div>
                @endforelse
            </div>
        </div>
    @endif
</div>
