<?php

namespace App\Events;

use App\Models\TransferRecord;
use App\Models\WalletAddress;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TransferRecordSaved implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var TransferRecord
     */
    protected TransferRecord $transferRecord;

    /**
     * Create a new event instance.
     *
     * @param  TransferRecord  $transferRecord
     */
    public function __construct(TransferRecord $transferRecord)
    {
        $this->transferRecord = $transferRecord;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array
     */
    public function broadcastOn(): array
    {
        $channels = [new PrivateChannel($this->transferRecord->walletAccount->user)];

        if ($walletAddress = $this->transferRecord->walletAddress) {
            $channels[] = new Channel("Public.WalletAddress.$walletAddress->address");
        }

        return $channels;
    }

    /**
     * Get associated wallet address
     *
     * @return WalletAddress|null
     */
    public function getWalletAddress(): ?WalletAddress
    {
        return $this->transferRecord->walletAddress;
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith(): array
    {
        return ['confirmed' => $this->transferRecord->confirmed];
    }
}
