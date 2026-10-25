<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Support\Arr;
use Revolution\Copilot\Types\ResumeSessionConfig;
use Revolution\Copilot\Types\SessionConfig;

/**
 * Request to resume a local session by ID or prefix.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionsOpenResumeRequest extends SessionsOpenRequest
{
    public function __construct(
        public string $sessionId,
        public SessionOpenOptions|SessionConfig|ResumeSessionConfig|array|null $options = null,
        public ?bool $resume = null,
        public ?bool $suppressResumeWorkspaceMetadataWriteback = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            sessionId: Arr::string($data, 'sessionId'),
            options: self::optionsFromArray($data),
            resume: $data['resume'] ?? null,
            suppressResumeWorkspaceMetadataWriteback: $data['suppressResumeWorkspaceMetadataWriteback'] ?? null,
        );
    }

    public function toArray(): array
    {
        $resume = $this->resume;
        if ($resume === null) {
            $resumeConfig = $this->options instanceof ResumeSessionConfig
                ? $this->options
                : ($this->options instanceof SessionOpenOptions && $this->options->options instanceof ResumeSessionConfig
                    ? $this->options->options
                    : null);
            $resume = $resumeConfig?->suppressResumeEvent === null
                ? null
                : ! $resumeConfig->suppressResumeEvent;
        }

        return array_filter([
            'kind' => 'resume',
            'sessionId' => $this->sessionId,
            'options' => self::optionsToArray($this->options),
            'resume' => $resume,
            'suppressResumeWorkspaceMetadataWriteback' => $this->suppressResumeWorkspaceMetadataWriteback,
        ], fn ($value) => $value !== null);
    }
}
