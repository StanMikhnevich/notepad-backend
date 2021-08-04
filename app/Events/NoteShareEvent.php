<?php

namespace App\Events;

use App\Models\NoteUser;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

use App\Models\Note;

class NoteShareEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $sharing;

    public $share;

    /**
     * NoteShared constructor.
     * @param NoteUser $sharing
     * @param bool $share
     */
    public function __construct(NoteUser $sharing, bool $share = true)
    {
        $this->sharing = $sharing;
        $this->share = $share;
    }

//    /**
//     * Get the channels the event should broadcast on.
//     *
//     * @return \Illuminate\Broadcasting\Channel|array
//     */
//    public function broadcastOn()
//    {
//        return new PrivateChannel('channel-name');
//    }
}
