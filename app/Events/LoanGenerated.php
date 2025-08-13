<?php

namespace App\Events;

use App\Models\Loan;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Event fired when a loan is generated.
 */
class LoanGenerated implements ShouldBroadcast, ShouldQueue
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var Loan|null
     */
    public ?Loan $loan = null;

    /**
     * Create a new event instance.
     *
     * @param Loan $loan
     */
    public function __construct(Loan $loan)
    {
        try {
            $this->loan = $loan->load('installments');
        } catch (\Throwable $e) {
            Log::error('LoanGenerated broadcast failed: ' . $e->getMessage());
            $this->loan = null;
        }
    }

    /**
     * The channel the event should broadcast on.
     */
    public function broadcastOn(): Channel
    {
        return new Channel('loans');
    }

    /**
     * The event name to broadcast as.
     */
    public function broadcastAs(): string
    {
        return 'LoanGenerated';
    }

    /**
     * The data to broadcast.
     */
    public function broadcastWith(): array
    {
        if (!$this->loan) {
            return ['loan' => []];
        }

        return [
            'loan' => [
                'id'           => $this->loan->id,
                'amount'       => $this->loan->amount,
                'status'       => $this->loan->status,
                'installments' => $this->loan->installments->map(function ($installment) {
                    return [
                        'id'       => $installment->id,
                        'amount'   => $installment->amount,
                        'due_date' => $installment->due_date->toDateTimeString(),
                        'status'   => $installment->status,
                    ];
                })->toArray(),
            ],
        ];
    }
}
