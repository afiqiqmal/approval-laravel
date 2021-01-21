<?php

namespace Afiqiqmal\Approval\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class Approved
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

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
