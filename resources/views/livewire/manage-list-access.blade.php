<div class="bg-white rounded-lg shadow-md dark:bg-gray-800 p-6">
    <h2 class="text-2xl font-bold mb-6 text-gray-800 dark:text-white">Manage List Access</h2>
    
    @if (session()->has('success'))
        <div class="p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-lg dark:bg-green-200 dark:text-green-800" role="alert">
            {{ session('success') }}
        </div>
    @endif
    
    @if (session()->has('error'))
        <div class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg dark:bg-red-200 dark:text-red-800" role="alert">
            {{ session('error') }}
        </div>
    @endif
    
    <div class="mb-8">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-semibold text-gray-700 dark:text-gray-300">Collaborators</h3>
            <button 
                wire:click="toggleInviteForm" 
                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
            >
                {{ $showInviteForm ? 'Cancel' : 'Add Collaborator' }}
            </button>
        </div>
        
        @if ($showInviteForm)
            <div class="mb-6 bg-gray-50 p-4 rounded-lg dark:bg-gray-700">
                <form wire:submit.prevent="inviteUser" class="flex">
                    <div class="flex-1 mr-4">
                        <label for="emailSearch" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email Address</label>
                        <input 
                            wire:model="emailSearch" 
                            type="email" 
                            id="emailSearch" 
                            placeholder="Enter email address" 
                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white"
                            required
                        >
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                            Add User
                        </button>
                    </div>
                </form>
            </div>
        @endif
        
        @if ($collaborators->count() > 0)
            <div class="bg-white rounded-md shadow-sm overflow-hidden dark:bg-gray-700">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">
                                User
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">
                                Email
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-700 dark:divide-gray-600">
                        @foreach ($collaborators as $collaborator)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $collaborator->user->name }}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-500 dark:text-gray-300">
                                        {{ $collaborator->user->email }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button 
                                        wire:click="removeCollaborator({{ $collaborator->id }})" 
                                        class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                    >
                                        Remove
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-4 text-gray-500 dark:text-gray-400">
                No collaborators yet
            </div>
        @endif
    </div>
    
    <div>
        <h3 class="text-xl font-semibold mb-4 text-gray-700 dark:text-gray-300">Pending Access Requests</h3>
        
        @if ($pendingRequests->count() > 0)
            <div class="space-y-4">
                @foreach ($pendingRequests as $request)
                    <div class="bg-gray-50 p-4 rounded-lg dark:bg-gray-700">
                        <div class="flex justify-between items-start">
                            <div>
                                <h4 class="font-medium text-gray-900 dark:text-white">{{ $request->requester->name }}</h4>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $request->requester->email }}</p>
                                @if ($request->message)
                                    <div class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                                        <p class="font-medium">Message:</p>
                                        <p class="mt-1">{{ $request->message }}</p>
                                    </div>
                                @endif
                                <p class="text-xs text-gray-400 mt-2 dark:text-gray-500">
                                    Requested {{ $request->created_at->diffForHumans() }}
                                </p>
                            </div>
                            <div class="flex space-x-2">
                                <button 
                                    wire:click="rejectRequest({{ $request->id }})" 
                                    class="px-4 py-2 bg-white border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 dark:bg-gray-600 dark:text-white dark:border-gray-500 dark:hover:bg-gray-500"
                                >
                                    Reject
                                </button>
                                <button 
                                    wire:click="approveRequest({{ $request->id }})" 
                                    class="px-4 py-2 bg-blue-600 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-blue-700"
                                >
                                    Approve
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-4 text-gray-500 dark:text-gray-400">
                No pending requests
            </div>
        @endif
    </div>
</div>
