<?php

namespace App\Notifications;

use App\Models\AccessRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccessResponseNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        protected AccessRequest $accessRequest,
        protected bool $isApproved
    ) {
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
        $status = $this->isApproved ? 'approved' : 'rejected';
        $message = (new MailMessage)
                    ->subject("URL List Access Request {$status}")
                    ->greeting('Hello ' . $notifiable->name);

        if ($this->isApproved) {
            $message->line('Your request for edit access to "' . $this->accessRequest->urlList->name . '" has been approved.')
                   ->action('Go to List', url('/dashboard/lists/' . $this->accessRequest->url_list_id));
        } else {
            $message->line('Your request for edit access to "' . $this->accessRequest->urlList->name . '" has been rejected.');
        }

        return $message->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'access_response',
            'access_request_id' => $this->accessRequest->id,
            'status' => $this->isApproved ? 'approved' : 'rejected',
            'list_id' => $this->accessRequest->url_list_id,
            'list_name' => $this->accessRequest->urlList->name,
            'owner_name' => $this->accessRequest->urlList->user->name,
        ];
    }
}
