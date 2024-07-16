<?php

namespace App\Events;
ini_set('memory_limit', '256M');

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AllBatchesProcessed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $userId;
    public $uuid;
    public $batchCount;

    /**
     * Create a new event instance.
     */
     public function __construct(int $userId, string $uuid, int $batchCount)
    {
        $this->userId = $userId;
        $this->uuid = $uuid;
        $this->batchCount = $batchCount;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
