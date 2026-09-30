<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Host-managed model selection ids to withdraw from the session's BYOK registry.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class ProviderWithdrawRequest implements Arrayable
{
    /**
     * @param  list<string>  $models  Provider-qualified selection ids to withdraw. Unregistered ids are ignored.
     */
    public function __construct(
        public array $models = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(models: array_values(Arr::array($data, 'models', [])));
    }

    public function toArray(): array
    {
        return ['models' => $this->models];
    }
}
