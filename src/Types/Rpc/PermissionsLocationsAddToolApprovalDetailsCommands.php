<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Command approval stored for a permissions location.
 */
readonly class PermissionsLocationsAddToolApprovalDetailsCommands implements Arrayable
{
    public string $kind;

    /**
     * @param  string[]  $commandIdentifiers
     */
    public function __construct(
        public array $commandIdentifiers,
    ) {
        $this->kind = 'commands';
    }

    public static function fromArray(array $data): self
    {
        return new self(commandIdentifiers: $data['commandIdentifiers'] ?? []);
    }

    public function toArray(): array
    {
        return [
            'kind' => $this->kind,
            'commandIdentifiers' => $this->commandIdentifiers,
        ];
    }
}
