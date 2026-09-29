<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use InvalidArgumentException;
use Revolution\Copilot\Enums\InstallationDecision;

/** @experimental */
readonly class InstallationsConfirmResult implements Arrayable
{
    public function __construct(
        public string $confirmationId,
        public string $reviewFingerprint,
        public InstallationDecision|string $decision,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            confirmationId: (string) ($data['confirmationId'] ?? ''),
            reviewFingerprint: (string) ($data['reviewFingerprint'] ?? ''),
            decision: InstallationDecision::tryFrom((string) ($data['decision'] ?? '')) ?? (string) ($data['decision'] ?? ''),
        );
    }

    public function toArray(): array
    {
        $decision = $this->decision instanceof InstallationDecision ? $this->decision->value : $this->decision;
        if (! in_array($decision, ['confirm', 'decline', 'cancel'], true)) {
            throw new InvalidArgumentException('Invalid installation confirmation decision.');
        }

        return [
            'confirmationId' => $this->confirmationId,
            'reviewFingerprint' => $this->reviewFingerprint,
            'decision' => $decision,
        ];
    }
}
