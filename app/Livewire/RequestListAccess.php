<?php

namespace App\Livewire;

use App\Models\AccessRequest;
use App\Models\UrlList;
use App\Notifications\AccessRequestNotification;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class RequestListAccess extends Component
{
    public UrlList $urlList;
    public string $message = '';
    public bool $showRequestForm = false;
    
    public function mount(UrlList $urlList)
    {
        $this->urlList = $urlList;
    }
    
    public function toggleRequestForm()
    {
        $this->showRequestForm = !$this->showRequestForm;
    }
    
    public function submitRequest()
    {
        $this->validate([
            'message' => 'nullable|string|max:1000',
        ]);
        
        // Check if user already has a pending request
        if ($this->urlList->hasPendingAccessRequest(Auth::id())) {
            $this->dispatch('swal:toast', [
                'type' => 'error',
                'title' => 'You already have a pending request for this list.'
            ]);
            return;
        }
        
        // Check if user is already a collaborator
        if ($this->urlList->isCollaborator(Auth::id())) {
            $this->dispatch('swal:toast', [
                'type' => 'error',
                'title' => 'You are already a collaborator on this list.'
            ]);
            return;
        }
        
        // Create the access request
        $accessRequest = AccessRequest::create([
            'url_list_id' => $this->urlList->id,
            'requester_id' => Auth::id(),
            'message' => $this->message,
            'status' => 'pending',
        ]);
        
        // Notify the list owner
        $this->urlList->user->notify(new AccessRequestNotification($accessRequest));
        
        // Reset form
        $this->reset('message');
        $this->showRequestForm = false;
        
        $this->dispatch('access-requested');
        $this->dispatch('swal:toast', [
            'type' => 'success',
            'title' => 'Access request submitted successfully.'
        ]);
    }
    
    public function render()
    {
        return view('livewire.request-list-access', [
            'hasPendingRequest' => $this->urlList->hasPendingAccessRequest(Auth::id()),
            'isCollaborator' => $this->urlList->isCollaborator(Auth::id()),
        ]);
    }
}
