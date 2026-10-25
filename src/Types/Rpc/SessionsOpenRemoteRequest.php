<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Support\Arr;
use Revolution\Copilot\Types\ResumeSessionConfig;
use Revolution\Copilot\Types\SessionConfig;

/**
 * Request to connect to a live remote session.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionsOpenRemoteRequest extends SessionsOpenRequest
{
    public function __construct(
        public string $remoteSessionId,
        public ?array $repository = null,
        public SessionOpenOptions|SessionConfig|ResumeSessionConfig|array|null $options = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            remoteSessionId: Arr::string($data, 'remoteSessionId'),
            repository: isset($data['repository']) ? Arr::array($data, 'repository') : null,
            options: self::optionsFromArray($data),
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'kind' => 'remote',
            'remoteSessionId' => $this->remoteSessionId,
            'repository' => $this->repository,
            'options' => self::optionsToArray($this->options),
        ], fn ($value) => $value !== null);
    }
}
