<?php

namespace App\Notifications;

use App\Models\InstallationJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class JobStageChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public InstallationJob $job) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $stage = ucfirst(str_replace('_', ' ', $this->job->current_stage->value));

        return (new MailMessage)
            ->subject('Your installation is now: '.$stage)
            ->line("Your solar installation at {$this->job->address} has moved to: {$stage}.")
            ->action('View Your Installation', route('portal.installation'));
    }
}
