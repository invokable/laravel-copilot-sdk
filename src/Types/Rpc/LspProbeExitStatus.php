<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Process exit information from a startup probe.
 *
 * @experimental
 */
readonly class LspProbeExitStatus implements Arrayable
{
    public function __construct(
        public ?int $code,
        public ?string $signal,
        public ?string $error = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            code: isset($data['code']) ? Arr::integer($data, 'code') : null,
            signal: isset($data['signal']) ? Arr::string($data, 'signal') : null,
            error: isset($data['error']) ? Arr::string($data, 'error') : null,
        );
    }

    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'signal' => $this->signal,
            ...($this->error === null ? [] : ['error' => $this->error]),
        ];
    }
}
