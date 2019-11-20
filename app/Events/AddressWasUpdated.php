<?php

namespace App\Events;

use App\Models\Address;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AddressWasUpdated extends BaseEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /** @var Address */
    public $address;

    /**
     * Create a new event instance.
     *
     * @param Address $address
     * @return void
     */
    public function __construct(Address $address)
    {
        $this->address = $address;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
