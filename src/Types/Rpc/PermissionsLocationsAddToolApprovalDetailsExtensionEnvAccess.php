<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Extension environment-access approval stored for a permissions location.
 */
readonly class PermissionsLocationsAddToolApprovalDetailsExtensionEnvAccess implements Arrayable
{
    public string $kind;

    /**
     * @param  non-empty-list<string>  $environmentVariables
     */
    public function __construct(
        public string $extensionName,
        public array $environmentVariables,
    ) {
        $this->kind = 'extension-env-access';
    }

    public static function fromArray(array $data): self
    {
        return new self(
            extensionName: Arr::string($data, 'extensionName'),
            environmentVariables: $data['environmentVariables'] ?? [],
        );
    }

    public function toArray(): array
    {
        return [
            'kind' => $this->kind,
            'extensionName' => $this->extensionName,
            'environmentVariables' => $this->environmentVariables,
        ];
    }
}
