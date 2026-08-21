<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\McpPlanPolicySource;

/**
 * Registry or enterprise policy refused the operation.
 *
 * @experimental
 */
readonly class CatalogPolicyRejectedError implements Arrayable
{
    public string $kind;

    public function __construct(
        public McpPlanPolicySource $source,
        public string $message,
    ) {
        $this->kind = 'policy-rejected';
    }

    public static function fromArray(array $data): static
    {
        return new static(
            source: McpPlanPolicySource::from($data['source']),
            message: $data['message'],
        );
    }

    public function toArray(): array
    {
        return [
            'kind' => $this->kind,
            'source' => $this->source->value,
            'message' => $this->message,
        ];
    }
}
