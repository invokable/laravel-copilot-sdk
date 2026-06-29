<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Parameters for session.extensions.sendAttachmentsToMessage.
 *
 * @experimental This type is experimental and may change or be removed.
 */
readonly class SendAttachmentsToMessageParams implements Arrayable
{
    /**
     * @param  array  $attachments  Attachments to push into the next user-message turn.
     * @param  ?string  $instanceId  Optional canvas instance binding the push for provenance.
     */
    public function __construct(
        public array $attachments = [],
        public ?string $instanceId = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            attachments: Arr::array($data, 'attachments', []),
            instanceId: $data['instanceId'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'attachments' => $this->attachments,
            'instanceId' => $this->instanceId,
        ], fn ($v) => $v !== null && $v !== []);
    }
}
