<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionRenewalNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        protected Subscription $subscription,
        protected string $renewalType // 'upcoming' or 'completed'
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $preferences = $notifiable->notification_preferences ?? [];
        $channels = ['database'];
        
        if ($preferences['email_subscription_updates'] ?? true) {
            $channels[] = 'mail';
        }
        
        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject($this->renewalType === 'upcoming' 
                ? 'Your Subscription Will Renew Soon' 
                : 'Subscription Renewal Completed')
            ->greeting('Hello ' . $notifiable->name);

        if ($this->renewalType === 'upcoming') {
            $daysUntil = now()->diffInDays($this->subscription->current_period_ends_at);
            $amount = $this->subscription->interval === 'yearly' 
                ? $this->subscription->planVersion->yearly_price 
                : $this->subscription->planVersion->monthly_price;
                
            $message->line('Your subscription to the ' . $this->subscription->planVersion->name . ' plan will renew in ' . $daysUntil . ' days.')
                   ->line('You will be charged ' . number_format($amount, 2) . ' ' . config('paypal.currency') . ' for your ' . $this->subscription->interval . ' subscription.')
                   ->action('View Subscription Details', url('/subscription/dashboard'));
        } else {
            $nextRenewal = $this->subscription->interval === 'yearly' 
                ? now()->addYear() 
                : now()->addMonth();
                
            $message->line('Your subscription to the ' . $this->subscription->planVersion->name . ' plan has been renewed successfully.')
                   ->line('Your next renewal is scheduled for ' . $nextRenewal->format('F j, Y') . '.')
                   ->action('View Subscription Details', url('/subscription/dashboard'));
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
            'type' => 'subscription_renewal',
            'renewal_type' => $this->renewalType,
            'plan_name' => $this->subscription->planVersion->name,
            'interval' => $this->subscription->interval,
            'amount' => $this->subscription->interval === 'yearly' 
                ? $this->subscription->planVersion->yearly_price 
                : $this->subscription->planVersion->monthly_price,
            'next_renewal' => $this->renewalType === 'upcoming'
                ? $this->subscription->current_period_ends_at->toISOString()
                : ($this->subscription->interval === 'yearly' 
                    ? now()->addYear()->toISOString() 
                    : now()->addMonth()->toISOString())
        ];
    }
}
