<?php

namespace App\Notifications;

use App\Models\Note;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NoteShareNotification extends Notification
{
    use Queueable;

    private $user;

    private $note;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(User $user, Note $note)
    {
        $this->user = $user;
        $this->note = $note;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Note Share Notification')
            ->greeting('Hello, '.$this->user->name)
            ->line($this->note->user->name.' has been shared note '.$this->note->title.' with you.')
            ->action('Check now', url('/notes' . '/' . $this->note->uid))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable): array
    {
        return [
            //
        ];
    }
}
