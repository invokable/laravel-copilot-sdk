<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Completes a catalog candidate selection interaction.
 *
 * @experimental
 */
readonly class CatalogSelectionRequest implements Arrayable
{
    public function __construct(
        public CatalogClientContract $contract,
        public string $sessionId,
        public string $selectionRef,
        public CatalogSelectionDecision|string $outcome,
    ) {}

    public static function fromArray(array $data): static
    {
        $outcome = (string) $data['outcome'];

        return new static(
            contract: CatalogClientContract::fromArray($data['contract']),
            sessionId: $data['sessionId'],
            selectionRef: $data['selectionRef'],
            outcome: CatalogSelectionDecision::tryFrom($outcome) ?? $outcome,
        );
    }

    public function toArray(): array
    {
        return [
            'contract' => $this->contract->toArray(),
            'sessionId' => $this->sessionId,
            'selectionRef' => $this->selectionRef,
            'outcome' => $this->outcome instanceof CatalogSelectionDecision
                ? $this->outcome->value
                : $this->outcome,
        ];
    }
}
