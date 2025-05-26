<?php

namespace App\Livewire;

use App\Models\AccessRequest;
use App\Models\ListCollaborator;
use App\Models\UrlList;
use App\Models\User;
use App\Notifications\AccessResponseNotification;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class ManageListAccess extends Component
{
    use WithPagination;
    
    public UrlList $urlList;
    public bool $allowAccessRequests; // Track sharing toggle
    public $emailSearch = '';
    public $showInviteForm = false;
    
    protected $listeners = ['refreshComponent' => '$refresh'];
    
    public function mount(UrlList $urlList)
    {
        $this->urlList = $urlList;
        $this->allowAccessRequests = $urlList->allow_access_requests; // initialize toggle
        
        // Check if the current user is the owner of this list
        if (Auth::id() !== $this->urlList->user_id) {
            abort(403, 'You are not authorized to manage access for this list.');
        }
    }
    
    public function toggleInviteForm()
    {
        $this->showInviteForm = !$this->showInviteForm;
    }
    
    public function toggleAccessRequests()
    {
        $this->urlList->allow_access_requests = !$this->urlList->allow_access_requests;
        $this->urlList->save();
        $this->allowAccessRequests = $this->urlList->allow_access_requests;

        $this->dispatch('swal:toast', [
            'type' => 'success',
            'title' => 'Sharing settings updated successfully.'
        ]);
        $this->dispatch('refreshComponent');
    }
    
    public function approveRequest($requestId)
    {
        $request = AccessRequest::findOrFail($requestId);
        
        // Ensure this request is for the current list
        if ($request->url_list_id !== $this->urlList->id) {
            return;
        }
        
        // Update the request status
        $request->update(['status' => 'approved']);
        
        // Create a collaborator record
        ListCollaborator::create([
            'url_list_id' => $this->urlList->id,
            'user_id' => $request->requester_id,
        ]);
        
        // Notify the requester
        $request->requester->notify(new AccessResponseNotification($request, true));
        
        $this->dispatch('swal:toast', [
            'type' => 'success',
            'title' => 'Access request approved successfully.'
        ]);
    }
    
    public function denyRequest($requestId)
    {
        $request = AccessRequest::findOrFail($requestId);
        
        // Ensure this request is for the current list
        if ($request->url_list_id !== $this->urlList->id) {
            return;
        }
        
        // Update the request status to rejected
        $request->update(['status' => 'rejected']);
        
        // Notify the requester about rejection
        $request->requester->notify(new AccessResponseNotification($request, false));
        
        $this->dispatch('swal:toast', [
            'type' => 'success',
            'title' => 'Access request denied.'
        ]);
    }
    
    public function removeCollaborator($collaboratorId)
    {
        $collaborator = ListCollaborator::findOrFail($collaboratorId);
        
        // Ensure this collaborator is for the current list
        if ($collaborator->url_list_id !== $this->urlList->id) {
            return;
        }
        
        // Remove the collaborator
        $collaborator->delete();
        
        $this->dispatch('swal:toast', [
            'type' => 'success',
            'title' => 'Collaborator removed successfully.'
        ]);
    }
    
    public function inviteUser()
    {
        $this->validate([
            'emailSearch' => 'required|email',
        ]);
        
        $user = User::where('email', $this->emailSearch)->first();
        
        if (!$user) {
            $this->dispatch('swal:toast', [
                'type' => 'error',
                'title' => 'No user found with this email address.'
            ]);
            return;
        }
        
        // Make sure this isn't the list owner
        if ($user->id === $this->urlList->user_id) {
            $this->dispatch('swal:toast', [
                'type' => 'error',
                'title' => 'You are already the owner of this list.'
            ]);
            return;
        }
        
        // Check if user is already a collaborator
        if ($this->urlList->isCollaborator($user->id)) {
            $this->dispatch('swal:toast', [
                'type' => 'error',
                'title' => 'This user is already a collaborator.'
            ]);
            return;
        }
        
        // Create a collaborator record
        ListCollaborator::create([
            'url_list_id' => $this->urlList->id,
            'user_id' => $user->id,
        ]);
        
        $this->reset('emailSearch');
        $this->showInviteForm = false;
        
        $this->dispatch('swal:toast', [
            'type' => 'success',
            'title' => 'User added as collaborator successfully.'
        ]);
    }
    
    public function render()
    {
        $pendingRequests = $this->urlList->accessRequests()
                            ->where('status', 'pending')
                            ->with('requester')
                            ->get();
        
        $collaborators = $this->urlList->collaborators()
                            ->with('user')
                            ->get();
        
        return view('livewire.manage-list-access', [
            'pendingRequests' => $pendingRequests,
            'collaborators' => $collaborators,
        ]);
    }
}
