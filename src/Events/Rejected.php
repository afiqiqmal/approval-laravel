<?php

namespace Afiqiqmal\Approval\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class Rejected
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $applicants;

    /**
     * Create a new event instance.
     *
     * @param $applicants
     */
    public function __construct($applicants)
    {
        $this->applicants = $applicants;
    }

}
