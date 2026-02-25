<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Parameters for switching session model.
 */
readonly class SessionModelSwitchToParams implements Arrayable
{
    public function __construct(
        public string $modelId,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            modelId: $data['modelId'],
        );
    }

    public function toArray(): array
    {
        return [
            'modelId' => $this->modelId,
        ];
    }
}
