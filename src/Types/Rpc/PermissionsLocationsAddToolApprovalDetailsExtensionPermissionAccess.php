<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Extension permission-access approval stored for a permissions location.
 */
readonly class PermissionsLocationsAddToolApprovalDetailsExtensionPermissionAccess implements Arrayable
{
    public string $kind;

    public function __construct(
        public string $extensionName,
    ) {
        $this->kind = 'extension-permission-access';
    }

    public static function fromArray(array $data): self
    {
        return new self(extensionName: Arr::string($data, 'extensionName'));
    }

    public function toArray(): array
    {
        return [
            'kind' => $this->kind,
            'extensionName' => $this->extensionName,
        ];
    }
}
