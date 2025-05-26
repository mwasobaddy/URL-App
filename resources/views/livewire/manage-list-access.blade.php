<?php

use function Livewire\Volt\{state, mount, computed};
use App\Models\AccessRequest;
use App\Models\ListCollaborator;
use App\Models\UrlList;
use App\Models\User;
use App\Notifications\AccessResponseNotification;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;

state([
    'urlList' => null,
    'allowAccessRequests' => false,
    'emailSearch' => '',
    'showInviteForm' => false,
]);

mount(function (UrlList $urlList) {
    $this->urlList = $urlList;
    $this->allowAccessRequests = $urlList->allow_access_requests;
    
    if (Auth::id() !== $this->urlList->user_id) {
        abort(403);
    }
});

$toggleInviteForm = function () {
    $this->showInviteForm = !$this->showInviteForm;
};

$toggleAccessRequests = function () {
    $this->urlList->allow_access_requests = !$this->urlList->allow_access_requests;
    $this->urlList->save();
    $this->allowAccessRequests = $this->urlList->allow_access_requests;

    $this->dispatch('swal:toast', [
        'title' => 'Sharing settings updated successfully.'
    ]);
    $this->dispatch('refreshComponent');
};

$approveRequest = function ($requestId) {
    $request = AccessRequest::findOrFail($requestId);
    
    if ($request->url_list_id !== $this->urlList->id) {
        abort(403);
    }
    
    $request->update(['status' => 'approved']);
    
    ListCollaborator::create([
        'user_id' => $request->requester_id,
    ]);
    
    $request->requester->notify(new AccessResponseNotification($request, true));
    
    $this->dispatch('swal:toast', [
        'title' => 'Access request approved successfully.'
    ]);
};

$denyRequest = function ($requestId) {
    $request = AccessRequest::findOrFail($requestId);
    
    if ($request->url_list_id !== $this->urlList->id) {
        abort(403);
    }
    
    $request->update(['status' => 'rejected']);
    $request->requester->notify(new AccessResponseNotification($request, false));
};

$collaborators = computed(function () {
    return $this->urlList->collaborators;
});

$pendingRequests = computed(function () {
    return $this->urlList->accessRequests()
        ->where('status', 'pending')
        ->with('requester')
        ->get();
});

?>

<div class="max-w-4xl mx-auto my-8 backdrop-blur-sm bg-white/90 dark:bg-neutral-800/90 shadow-xl rounded-3xl p-6 border border-gray-100/40 dark:border-neutral-700/50 transition-all duration-300 relative overflow-hidden">
    <!-- Decorative elements - subtle background patterns -->
    <div class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-bl from-emerald-400/10 to-transparent rounded-full blur-3xl -z-10"></div>
    <div class="absolute bottom-0 left-0 w-80 h-80 bg-gradient-to-tr from-teal-400/10 to-transparent rounded-full blur-3xl -z-10"></div>
    
    <!-- Header Section -->
    <div class="relative mb-8">
        <h2 class="text-3xl md:text-4xl font-extrabold tracking-tight">
            <span class="bg-clip-text text-transparent bg-gradient-to-br from-emerald-500 to-teal-400">
                Manage Access
            </span>
        </h2>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 max-w-md">
            Control who can access and collaborate on your URL list
        </p>
        <!-- Decorative element -->
        <div class="absolute -bottom-3 left-0 h-1 w-16 bg-gradient-to-r from-emerald-500 to-teal-400 rounded-full"></div>
    </div>

    <!-- Current Collaborators Section -->
    <div class="mb-12">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Current Collaborators</h3>
        <div class="bg-white dark:bg-zinc-800/50 rounded-xl border border-gray-100 dark:border-neutral-700/50 overflow-hidden">
            @forelse($collaborators as $collaborator)
                <div class="flex items-center justify-between p-4 {{ !$loop->last ? 'border-b border-gray-100 dark:border-neutral-700/50' : '' }}">
                    <div class="flex items-center space-x-4">
                        <div class="flex-shrink-0">
                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-900/50">
                                <span class="text-sm font-medium text-emerald-600 dark:text-emerald-400">
                                    {{ strtoupper(substr($collaborator->user->name, 0, 2)) }}
                                </span>
                            </span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ $collaborator->user->name }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $collaborator->user->email }}
                            </p>
                        </div>
                    </div>
                    <button
                        wire:click="removeCollaborator({{ $collaborator->id }})"
                        wire:confirm="Are you sure you want to remove this collaborator?"
                        class="inline-flex items-center px-3 py-1.5 bg-red-50 hover:bg-red-100 dark:bg-red-900/30 dark:hover:bg-red-900/50 text-red-700 dark:text-red-300 rounded-lg text-sm font-medium transition-colors duration-200"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                        Remove
                    </button>
                </div>
            @empty
                <div class="p-6 text-center">
                    <span class="text-sm text-gray-500 dark:text-gray-400">No collaborators yet</span>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Pending Requests Section -->
    <div class="mb-12">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Pending Access Requests</h3>
        <div class="space-y-4">
            @forelse($pendingRequests as $request)
                <div class="bg-white dark:bg-zinc-800/50 rounded-xl border border-gray-100 dark:border-neutral-700/50 p-4 sm:p-6">
                    <div class="flex items-start justify-between">
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0">
                                <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900/50">
                                    <span class="text-sm font-medium text-blue-600 dark:text-blue-400">
                                        {{ strtoupper(substr($request->user->name, 0, 2)) }}
                                    </span>
                                </span>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $request->user->name }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    {{ $request->user->email }}
                                </p>
                                @if($request->message)
                                    <div class="mt-2 text-sm text-gray-600 dark:text-gray-300 bg-gray-50 dark:bg-black/20 rounded-lg p-3 border border-gray-100 dark:border-gray-800">
                                        "{{ $request->message }}"
                                    </div>
                                @endif
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                                    Requested {{ $request->created_at->diffForHumans() }}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <button
                                wire:click="denyRequest({{ $request->id }})"
                                wire:confirm="Are you sure you want to deny this access request?"
                                class="inline-flex items-center px-3 py-1.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium transition-colors duration-200"
                            >
                                Deny
                            </button>
                            <button
                                wire:click="approveRequest({{ $request->id }})"
                                class="inline-flex items-center px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-900/30 dark:hover:bg-emerald-900/50 text-emerald-700 dark:text-emerald-300 rounded-lg text-sm font-medium transition-colors duration-200"
                            >
                                Approve
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white dark:bg-zinc-800/50 rounded-xl border border-gray-100 dark:border-neutral-700/50 p-6 text-center">
                    <span class="text-sm text-gray-500 dark:text-gray-400">No pending requests</span>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Sharing Settings -->
    <div>
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Sharing Settings</h3>
        <div class="bg-white dark:bg-zinc-800/50 rounded-xl border border-gray-100 dark:border-neutral-700/50 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">Allow Access Requests</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        When enabled, users can request access to this list
                    </p>
                </div>
                <button
                    wire:click="toggleAccessRequests"
                    class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200 ease-in-out {{ $allowAccessRequests ? 'bg-emerald-500' : 'bg-gray-200 dark:bg-gray-700' }}"
                    role="switch"
                >
                    <span
                        class="inline-block h-4 w-4 transform rounded-full bg-white shadow-sm ring-1 ring-gray-900/5 transition-transform duration-200 ease-in-out {{ $allowAccessRequests ? 'translate-x-6' : 'translate-x-1' }}"
                    ></span>
                </button>
            </div>
        </div>
    </div>
</div>
