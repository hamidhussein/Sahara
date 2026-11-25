<?php

namespace App\Payments;

final readonly class GatewayResult
{
    /** @param array<string, mixed> $payload the provider's data, kept for reconciliation */
    public function __construct(
        public string $reference,
        public bool $successful,
        public int $amount,
        public ?string $transactionId = null,
        public array $payload = [],
    ) {}
}
