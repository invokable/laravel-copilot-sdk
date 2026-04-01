<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Result of setting the session filesystem provider.
 */
readonly class SessionFsSetProviderResult implements Arrayable
{
    /**
     * @param  bool  $success  Whether the provider was set successfully
     */
    public function __construct(
        public bool $success,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            success: $data['success'] ?? false,
        );
    }

    public function toArray(): array
    {
        return [
            'success' => $this->success,
        ];
    }
}
