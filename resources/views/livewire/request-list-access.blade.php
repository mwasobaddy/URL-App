<?php

use function Livewire\Volt\{state, mount, computed};
use Illuminate\Support\Facades\Auth;
use App\Models\AccessRequest;
use App\Models\UrlList;
use App\Notifications\AccessRequestNotification;

state([
    'urlList' => null,
    'message' => '',
    'showRequestForm' => false,
]);

mount(function (UrlList $urlList) {
    $this->urlList = $urlList;
});

$toggleRequestForm = function () {
    $this->showRequestForm = !$this->showRequestForm;
};

$submitRequest = function () {
    $this->validate([
        'message' => 'nullable|string|max:1000',
    ]);
    
    if ($this->urlList->hasPendingAccessRequest(Auth::id())) {
        $this->dispatch('swal:toast', [
            'type' => 'error',
            'title' => 'You already have a pending request for this list.'
        ]);
        return;
    }
    
    if ($this->urlList->isCollaborator(Auth::id())) {
        $this->dispatch('swal:toast', [
            'type' => 'error',
            'title' => 'You are already a collaborator on this list.'
        ]);
        return;
    }
    
    $accessRequest = AccessRequest::create([
        'url_list_id' => $this->urlList->id,
        'requester_id' => Auth::id(),
        'message' => $this->message,
        'status' => 'pending',
    ]);
    
    $this->urlList->user->notify(new AccessRequestNotification($accessRequest));
    
    $this->message = '';
    $this->showRequestForm = false;
    
    $this->dispatch('access-requested');
    $this->dispatch('swal:toast', [
        'type' => 'success',
        'title' => 'Access request submitted successfully.'
    ]);
};

$hasPendingRequest = computed(function () {
    return $this->urlList->hasPendingAccessRequest(Auth::id());
});

$isCollaborator = computed(function () {
    return $this->urlList->isCollaborator(Auth::id());
});

?>

<div>
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
    
    @if (!$isCollaborator && !$hasPendingRequest)
        @if ($showRequestForm)
            <div class="mt-4 bg-white p-4 rounded-lg shadow-md dark:bg-gray-800">
                <h3 class="text-lg font-semibold mb-2">Request Edit Access</h3>
                <form wire:submit.prevent="submitRequest">
                    <div class="mb-4">
                        <label for="message" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Message (Optional)</label>
                        <textarea 
                            wire:model="message" 
                            id="message" 
                            rows="3" 
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                            placeholder="Explain why you need edit access to this list"
                        ></textarea>
                    </div>
                    
                    <div class="flex justify-end">
                        <button type="button" wire:click="toggleRequestForm" class="mr-2 px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 dark:bg-gray-700 dark:text-white dark:hover:bg-gray-600">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                            Submit Request
                        </button>
                    </div>
                </form>
            </div>
        @else
            <button type="button" wire:click="toggleRequestForm" class="mt-2 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                Request Edit Access
            </button>
        @endif
    @elseif ($hasPendingRequest)
        <div class="mt-2 py-2 px-4 bg-yellow-100 text-yellow-800 rounded-md inline-block dark:bg-yellow-200">
            <span class="font-medium">Pending:</span> Your access request is awaiting approval
        </div>
    @elseif ($isCollaborator)
        <div class="mt-2 py-2 px-4 bg-green-100 text-green-800 rounded-md inline-block dark:bg-green-200">
            <span class="font-medium">Access Granted:</span> You can edit this list
        </div>
    @endif
</div>
