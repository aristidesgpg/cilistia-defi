<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class UserPresenceChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var User
     */
    protected User $user;

    /**
     * @var string
     */
    public string $presence;

    /**
     * @var Carbon|null
     */
    public ?Carbon $lastSeen;

    /**
     * Create a new event instance.
     *
     * @param  User  $user
     */
    public function __construct(User $user)
    {
        $this->presence = $user->presence;
        $this->lastSeen = $user->last_seen_at;
        $this->user = $user;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel
     */
    public function broadcastOn(): Channel
    {
        return new Channel("Public.User.{$this->user->id}");
    }
}
