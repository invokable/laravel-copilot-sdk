<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use Revolution\Copilot\Types\ResumeSessionConfig;
use Revolution\Copilot\Types\SessionConfig;

/**
 * Base type for the sessions.open request union.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
abstract readonly class SessionsOpenRequest implements Arrayable
{
    public static function fromArray(array $data): self
    {
        return match (Arr::string($data, 'kind')) {
            'create' => SessionsOpenCreateRequest::fromArray($data),
            'resume' => SessionsOpenResumeRequest::fromArray($data),
            'resumeLast' => SessionsOpenResumeLastRequest::fromArray($data),
            'attach' => SessionsOpenAttachRequest::fromArray($data),
            'remote' => SessionsOpenRemoteRequest::fromArray($data),
            'cloud' => SessionsOpenCloudRequest::fromArray($data),
            'handoff' => SessionsOpenHandoffRequest::fromArray($data),
            default => throw new InvalidArgumentException('Unsupported sessions.open request kind.'),
        };
    }

    protected static function optionsToArray(
        SessionOpenOptions|SessionConfig|ResumeSessionConfig|array|null $options,
    ): ?array {
        if ($options === null) {
            return null;
        }

        return ($options instanceof SessionOpenOptions ? $options : new SessionOpenOptions($options))->toArray();
    }

    protected static function optionsFromArray(array $data): SessionOpenOptions|SessionConfig|ResumeSessionConfig|array|null
    {
        $options = $data['options'] ?? null;

        if ($options === null
            || $options instanceof SessionOpenOptions
            || $options instanceof SessionConfig
            || $options instanceof ResumeSessionConfig) {
            return $options;
        }

        return SessionOpenOptions::fromArray(Arr::array($data, 'options'));
    }
}
