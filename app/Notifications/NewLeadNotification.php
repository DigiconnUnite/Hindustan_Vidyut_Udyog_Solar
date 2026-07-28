<?php

namespace App\Notifications;

use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewLeadNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Lead $lead) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Lead: '.$this->lead->name)
            ->line("A new lead came in from {$this->lead->name} ({$this->lead->phone}).")
            ->line('Source: '.ucfirst(str_replace('_', ' ', $this->lead->source->value)))
            ->action('View Lead', route('admin.leads.show', $this->lead));
    }
}
