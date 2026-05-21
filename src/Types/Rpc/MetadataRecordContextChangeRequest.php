<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Parameters for recording a session context change.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class MetadataRecordContextChangeRequest implements Arrayable
{
    public function __construct(
        public SessionWorkingDirectoryContext $context,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            context: isset($data['context']) && is_array($data['context'])
                ? SessionWorkingDirectoryContext::fromArray($data['context'])
                : new SessionWorkingDirectoryContext(cwd: ''),
        );
    }

    public function toArray(): array
    {
        return [
            'context' => $this->context->toArray(),
        ];
    }
}
