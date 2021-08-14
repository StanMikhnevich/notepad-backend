<?php

namespace App\Listeners;

use App\Events\NoteShareEvent;
use App\Notifications\NoteShareNotification;
use App\Notifications\NoteUnShareNotification;
use Illuminate\Support\Facades\Notification;

class NoteShareListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  NoteShareEvent  $event
     * @return void
     */
    public function handle(NoteShareEvent $event)
    {
        if ($event->share) {
            Notification::send($event->sharing->user, new NoteShareNotification($event->sharing->user, $event->sharing->note));
        } else {
            Notification::send($event->sharing->user, new NoteUnShareNotification($event->sharing->user, $event->sharing->note));
        }
    }
}
