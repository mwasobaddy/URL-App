<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class NotificationsDropdown extends Component
{
    public $unreadCount = 0;
    
    public function mount()
    {
        $this->loadUnreadCount();
    }
    
    public function loadUnreadCount()
    {
        $this->unreadCount = Auth::user()->unreadNotifications()->count();
    }
    
    public function getListeners()
    {
        return [
            'notification-received' => 'loadUnreadCount'
        ];
    }
    
    public function render()
    {
        return view('livewire.notifications-dropdown');
    }
}
