<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * The protocol version and capability set a caller requires, supplied on every catalog request.
 *
 * @experimental
 */
readonly class CatalogClientContract implements Arrayable
{
    /**
     * @param  int  $protocolVersion  SDK protocol version the caller was generated against.
     * @param  string[]  $requiredCapabilities  Wire features the caller requires the runtime to understand.
     */
    public function __construct(
        public int $protocolVersion,
        public array $requiredCapabilities,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            protocolVersion: (int) ($data['protocolVersion'] ?? 0),
            requiredCapabilities: $data['requiredCapabilities'] ?? [],
        );
    }

    public function toArray(): array
    {
        return [
            'protocolVersion' => $this->protocolVersion,
            'requiredCapabilities' => $this->requiredCapabilities,
        ];
    }
}
