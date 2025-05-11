<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class NotificationsDropdown extends Component
{
    public $showDropdown = false;
    public $unreadCount = 0;
    public $notifications = [];
    
    public function mount()
    {
        $this->loadNotifications();
    }
    
    public function loadNotifications()
    {
        if (!Auth::check()) {
            return;
        }
        
        $user = Auth::user();
        $this->unreadCount = $user->unreadNotifications->count();
        $this->notifications = $user->notifications()->latest()->take(5)->get();
    }
    
    public function toggleDropdown()
    {
        $this->showDropdown = !$this->showDropdown;
    }
    
    public function markAsRead($notificationId)
    {
        $notification = Auth::user()->notifications()->where('id', $notificationId)->first();
        
        if ($notification) {
            $notification->markAsRead();
            $this->loadNotifications();
        }
    }
    
    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
        $this->loadNotifications();
    }
    
    #[On('notification-received')]
    public function refreshNotifications()
    {
        $this->loadNotifications();
    }
    
    public function render()
    {
        return view('livewire.notifications-dropdown');
    }
}
