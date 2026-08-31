<?php

declare(strict_types=1);

namespace App\Mail;

use App\Enums\ReminderKind;
use App\Models\Sale;
use App\Support\Money;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class ChargeReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Sale $sale,
        public ReminderKind $kind,
    ) {}

    public function envelope(): Envelope
    {
        $when = $this->kind->label();

        return new Envelope(
            subject: "Aviso de cobro ({$when}) — {$this->sale->client?->name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.charge-reminder',
            with: [
                'client' => (string) $this->sale->client?->name,
                'service' => (string) $this->sale->serviceType?->name,
                'amount' => Money::formatUsd((string) $this->sale->amount),
                'chargeOn' => $this->sale->next_charge_at?->format('d/m/Y'),
                'kind' => $this->kind->label(),
            ],
        );
    }
}
