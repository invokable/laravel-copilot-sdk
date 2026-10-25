<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Revolution\Copilot\Enums\SessionsOpenHandoffTaskType;
use Revolution\Copilot\Types\ResumeSessionConfig;
use Revolution\Copilot\Types\SessionConfig;

/**
 * Request to hand off a remote session to a new local session.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionsOpenHandoffRequest extends SessionsOpenRequest
{
    public function __construct(
        public array $metadata,
        public SessionOpenOptions|SessionConfig|ResumeSessionConfig|array|null $options = null,
        public SessionsOpenHandoffTaskType|string|null $taskType = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            metadata: $data['metadata'],
            options: self::optionsFromArray($data),
            taskType: isset($data['taskType'])
                ? SessionsOpenHandoffTaskType::tryFrom($data['taskType']) ?? $data['taskType']
                : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'kind' => 'handoff',
            'metadata' => $this->metadata,
            'options' => self::optionsToArray($this->options),
            'taskType' => $this->taskType instanceof SessionsOpenHandoffTaskType
                ? $this->taskType->value
                : $this->taskType,
        ], fn ($value) => $value !== null);
    }
}
