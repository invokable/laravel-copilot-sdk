<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Support\Arr;
use Revolution\Copilot\Types\ResumeSessionConfig;
use Revolution\Copilot\Types\SessionConfig;

/**
 * Request to create a cloud session.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionsOpenCloudRequest extends SessionsOpenRequest
{
    public function __construct(
        public ?array $repository = null,
        public ?string $owner = null,
        public SessionOpenOptions|SessionConfig|ResumeSessionConfig|array|null $options = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            repository: isset($data['repository']) ? Arr::array($data, 'repository') : null,
            owner: $data['owner'] ?? null,
            options: self::optionsFromArray($data),
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'kind' => 'cloud',
            'repository' => $this->repository,
            'owner' => $this->owner,
            'options' => self::optionsToArray($this->options),
        ], fn ($value) => $value !== null);
    }
}
