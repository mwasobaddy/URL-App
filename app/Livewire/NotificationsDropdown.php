<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class NotificationsDropdown extends Component
{
    public $unreadCount = 0;
    public $showDropdown = false;

    protected $listeners = ['refreshNotifications' => 'loadNotifications'];

    public function mount()
    {
        $this->loadNotifications();
    }

    public function loadNotifications()
    {
        $this->unreadCount = Auth::user()->unreadNotifications()->count();
    }

    public function toggleDropdown()
    {
        $this->showDropdown = !$this->showDropdown;
        if ($this->showDropdown) {
            $this->dispatch('dropdown-opened');
        }
    }

    public function markAsRead($notificationId)
    {
        try {
            $notification = Auth::user()->notifications()->findOrFail($notificationId);
            $notification->markAsRead();
            
            $this->dispatch('swal:toast', [
                'type' => 'success',
                'title' => 'Notification marked as read'
            ]);
        } catch (\Exception $e) {
            $this->dispatch('swal:toast', [
                'type' => 'error',
                'title' => 'Could not mark notification as read'
            ]);
        }

        $this->loadNotifications();
    }

    public function markAllAsRead()
    {
        try {
            Auth::user()->unreadNotifications->markAsRead();
            
            $this->dispatch('swal:toast', [
                'type' => 'success',
                'title' => 'All notifications marked as read'
            ]);
        } catch (\Exception $e) {
            $this->dispatch('swal:toast', [
                'type' => 'error',
                'title' => 'Could not mark notifications as read'
            ]);
        }

        $this->loadNotifications();
    }

    public function render()
    {
        $notifications = Auth::user()
            ->notifications()
            ->latest()
            ->take(5)
            ->get();

        return view('livewire.notifications-dropdown', [
            'notifications' => $notifications
        ]);
    }
}
