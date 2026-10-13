<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Types\SessionContext;

/**
 * Optional working-directory context for selecting the most relevant session.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionsGetLastForContextRequest implements Arrayable
{
    public function __construct(
        public SessionContext|array|null $context = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $context = $data['context'] ?? null;
        if (is_array($context)) {
            $context = SessionContext::fromArray($context);
        }

        return new self(context: $context);
    }

    public function toArray(): array
    {
        return array_filter([
            'context' => $this->context instanceof SessionContext ? $this->context->toArray() : $this->context,
        ], fn ($value): bool => $value !== null);
    }
}
