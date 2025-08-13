<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Installment;
use App\Models\Loan;

/**
 * Event fired when an installment is paid.
 */
class InstallmentPaid implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var array
     */
    public array $loan;

    /**
     * Create a new event instance.
     *
     * @param Installment $installment
     */
    public function __construct(Installment $installment)
    {
        // Ensure loan with installments is fully loaded
        $loanModel = $installment->loan()->with('installments')->first();

        $this->loan = $loanModel
            ? $loanModel->toArray()
            : [];
    }

    /**
     * The channel the event should broadcast on.
     */
    public function broadcastOn(): Channel
    {
        return new Channel('loans');
    }

    /**
     * The data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'loan' => $this->loan,
        ];
    }

    /**
     * The event name to broadcast as.
     */
    public function broadcastAs(): string
    {
        return 'InstallmentPaid';
    }
}
