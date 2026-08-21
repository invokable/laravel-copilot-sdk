<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\McpPlanInstallSourceCardKind;

/**
 * Plan from a card supplied directly by the caller.
 *
 * @experimental
 */
readonly class McpPlanInstallSourceCard implements Arrayable
{
    public function __construct(
        public McpPlanInstallSourceCardKind $kind,
        public McpServerCardUrl|McpServerCardEmbedded $card,
    ) {}

    public static function fromArray(array $data): static
    {
        $cardData = $data['card'];
        $card = ($cardData['kind'] ?? '') === 'url'
            ? McpServerCardUrl::fromArray($cardData)
            : McpServerCardEmbedded::fromArray($cardData);

        return new static(
            kind: McpPlanInstallSourceCardKind::from($data['kind']),
            card: $card,
        );
    }

    public function toArray(): array
    {
        return [
            'kind' => $this->kind->value,
            'card' => $this->card->toArray(),
        ];
    }
}
