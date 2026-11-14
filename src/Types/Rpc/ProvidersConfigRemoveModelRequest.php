<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/** @experimental */
readonly class ProvidersConfigRemoveModelRequest implements Arrayable
{
    public function __construct(public string $providerName, public string $modelId) {}

    public static function fromArray(array $data): self
    {
        return new self(
            providerName: Arr::string($data, 'providerName', ''),
            modelId: Arr::string($data, 'modelId', ''),
        );
    }

    public function toArray(): array
    {
        return ['providerName' => $this->providerName, 'modelId' => $this->modelId];
    }
}
