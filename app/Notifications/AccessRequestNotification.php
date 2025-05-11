<?php

namespace App\Notifications;

use App\Models\AccessRequest;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccessRequestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(protected AccessRequest $accessRequest)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('New Access Request for Your URL List')
                    ->greeting('Hello ' . $notifiable->name)
                    ->line($this->accessRequest->requester->name . ' has requested edit access to your URL list: ' . $this->accessRequest->urlList->name)
                    ->line($this->accessRequest->message ? 'Message: ' . $this->accessRequest->message : '')
                    ->action('Review Request', url('/dashboard/lists/' . $this->accessRequest->url_list_id . '/access-requests'))
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'access_request',
            'access_request_id' => $this->accessRequest->id,
            'requester_name' => $this->accessRequest->requester->name,
            'requester_id' => $this->accessRequest->requester_id,
            'list_id' => $this->accessRequest->url_list_id,
            'list_name' => $this->accessRequest->urlList->name,
            'message' => $this->accessRequest->message,
        ];
    }
}
