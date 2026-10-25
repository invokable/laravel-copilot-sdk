<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Revolution\Copilot\Types\ResumeSessionConfig;
use Revolution\Copilot\Types\SessionConfig;

/**
 * Request to create a local session.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionsOpenCreateRequest extends SessionsOpenRequest
{
    public function __construct(
        public SessionOpenOptions|SessionConfig|ResumeSessionConfig|array|null $options = null,
        public ?bool $emitStart = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            options: self::optionsFromArray($data),
            emitStart: $data['emitStart'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'kind' => 'create',
            'options' => self::optionsToArray($this->options),
            'emitStart' => $this->emitStart,
        ], fn ($value) => $value !== null);
    }
}
