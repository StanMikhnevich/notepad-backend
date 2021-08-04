<?php

namespace App\Providers;

use App\Listeners\NoteShareListener;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;
use App\Events\NoteShareEvent;
use App\Notifications\NoteShareNotification;
use App\Notifications\NoteUnShareNotification;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [ SendEmailVerificationNotification::class, ],
        NoteShareEvent::class => [ NoteShareListener::class, ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
