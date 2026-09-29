<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\InstallationDecision;

/**
 * Connection-owned request for explicit confirmation of an installation review.
 *
 * @experimental
 */
readonly class InstallationConfirmationRequest implements Arrayable
{
    public function __construct(
        public string $confirmationId,
        public string $operationId,
        public string $expiresAt,
        public string $reviewFingerprint,
        public array $review = [],
        public ?string $policySessionId = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            confirmationId: (string) ($data['confirmationId'] ?? ''),
            operationId: (string) ($data['operationId'] ?? ''),
            expiresAt: (string) ($data['expiresAt'] ?? ''),
            reviewFingerprint: (string) ($data['reviewFingerprint'] ?? ''),
            review: is_array($data['review'] ?? null) ? $data['review'] : [],
            policySessionId: isset($data['policySessionId']) ? (string) $data['policySessionId'] : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'policySessionId' => $this->policySessionId,
            'confirmationId' => $this->confirmationId,
            'operationId' => $this->operationId,
            'expiresAt' => $this->expiresAt,
            'reviewFingerprint' => $this->reviewFingerprint,
            'review' => $this->review,
        ], static fn ($value): bool => $value !== null);
    }

    public function decision(InstallationDecision|string $decision): InstallationsConfirmResult
    {
        return new InstallationsConfirmResult(
            confirmationId: $this->confirmationId,
            reviewFingerprint: $this->reviewFingerprint,
            decision: $decision,
        );
    }
}
