<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\ModelProviderKind;

/** Neutral provider identity attached to models and usage records. */
readonly class ModelProviderRef implements Arrayable
{
    public function __construct(
        public string $id,
        public string $label,
        public ModelProviderKind|string $kind,
    ) {}

    public static function fromArray(array $data): self
    {
        $kind = $data['kind'] ?? 'copilot';

        return new self(
            id: $data['id'] ?? '',
            label: $data['label'] ?? '',
            kind: ModelProviderKind::tryFrom($kind) ?? $kind,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'label' => $this->label,
            'kind' => $this->kind instanceof ModelProviderKind ? $this->kind->value : $this->kind,
        ];
    }
}
