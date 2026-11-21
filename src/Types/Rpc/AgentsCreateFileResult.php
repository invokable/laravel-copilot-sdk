<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\AgentsCreateFileResultKind;

/** The outcome of creating an agent definition file. */
readonly class AgentsCreateFileResult implements Arrayable
{
    public function __construct(
        public AgentsCreateFileResultKind $kind,
        public ?string $filePath = null,
        public ?string $message = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            kind: AgentsCreateFileResultKind::from(Arr::string($data, 'kind')),
            filePath: isset($data['filePath']) ? Arr::string($data, 'filePath') : null,
            message: isset($data['message']) ? Arr::string($data, 'message') : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'kind' => $this->kind->value,
            'filePath' => $this->filePath,
            'message' => $this->message,
        ], static fn ($value): bool => $value !== null);
    }
}
