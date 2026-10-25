<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Revolution\Copilot\Types\ResumeSessionConfig;
use Revolution\Copilot\Types\SessionConfig;
use Revolution\Copilot\Types\SessionContext;

/**
 * Request to resume the most relevant local session.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionsOpenResumeLastRequest extends SessionsOpenRequest
{
    public function __construct(
        public SessionContext|array|null $context = null,
        public SessionOpenOptions|SessionConfig|ResumeSessionConfig|array|null $options = null,
        public ?bool $suppressResumeWorkspaceMetadataWriteback = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            context: isset($data['context'])
                ? (is_array($data['context']) ? SessionContext::fromArray($data['context']) : $data['context'])
                : null,
            options: self::optionsFromArray($data),
            suppressResumeWorkspaceMetadataWriteback: $data['suppressResumeWorkspaceMetadataWriteback'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'kind' => 'resumeLast',
            'context' => $this->context instanceof SessionContext ? $this->context->toArray() : $this->context,
            'options' => self::optionsToArray($this->options),
            'suppressResumeWorkspaceMetadataWriteback' => $this->suppressResumeWorkspaceMetadataWriteback,
        ], fn ($value) => $value !== null);
    }
}
