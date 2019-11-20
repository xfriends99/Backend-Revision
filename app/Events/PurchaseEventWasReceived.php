<?php

namespace App\Events;

use App\Models\CheckpointCode;
use App\Models\Purchase;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PurchaseEventWasReceived extends BaseEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /** @var CheckpointCode */
    public $checkpointCode;

    /** @var Purchase */
    public $purchase;

    /** @var string|null */
    public $status;

    /**
     * Create a new event instance.
     *
     * @param CheckpointCode $checkpointCode
     * @param Purchase $purchase
     * @param string|null $status
     * @return void
     */
    public function __construct(CheckpointCode $checkpointCode, Purchase $purchase, $status = null)
    {
        $this->checkpointCode = $checkpointCode;
        $this->purchase = $purchase;
        $this->status = $status;
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
