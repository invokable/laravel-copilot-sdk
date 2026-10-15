<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Tool approval to persist for a permissions location.
 */
readonly class PermissionLocationAddToolApprovalParams implements Arrayable
{
    public function __construct(
        public PermissionsLocationsAddToolApprovalDetailsCommands
            |PermissionsLocationsAddToolApprovalDetailsRead
            |PermissionsLocationsAddToolApprovalDetailsWrite
            |PermissionsLocationsAddToolApprovalDetailsMcp
            |PermissionsLocationsAddToolApprovalDetailsMcpSampling
            |PermissionsLocationsAddToolApprovalDetailsMemory
            |PermissionsLocationsAddToolApprovalDetailsCustomTool
            |PermissionsLocationsAddToolApprovalDetailsExtensionManagement
            |PermissionsLocationsAddToolApprovalDetailsWorkflow
            |PermissionsLocationsAddToolApprovalDetailsExtensionPermissionAccess
            |PermissionsLocationsAddToolApprovalDetailsExtensionEnvAccess
            |array $approval,
        public string $locationKey,
    ) {}

    public static function fromArray(array $data): self
    {
        $approval = $data['approval'] ?? [];
        if (is_array($approval)) {
            $approval = match ($approval['kind'] ?? '') {
                'commands' => PermissionsLocationsAddToolApprovalDetailsCommands::fromArray($approval),
                'read' => PermissionsLocationsAddToolApprovalDetailsRead::fromArray($approval),
                'write' => PermissionsLocationsAddToolApprovalDetailsWrite::fromArray($approval),
                'mcp' => PermissionsLocationsAddToolApprovalDetailsMcp::fromArray($approval),
                'mcp-sampling' => PermissionsLocationsAddToolApprovalDetailsMcpSampling::fromArray($approval),
                'memory' => PermissionsLocationsAddToolApprovalDetailsMemory::fromArray($approval),
                'custom-tool' => PermissionsLocationsAddToolApprovalDetailsCustomTool::fromArray($approval),
                'extension-management' => PermissionsLocationsAddToolApprovalDetailsExtensionManagement::fromArray($approval),
                'workflow' => PermissionsLocationsAddToolApprovalDetailsWorkflow::fromArray($approval),
                'extension-permission-access' => PermissionsLocationsAddToolApprovalDetailsExtensionPermissionAccess::fromArray($approval),
                'extension-env-access' => PermissionsLocationsAddToolApprovalDetailsExtensionEnvAccess::fromArray($approval),
            };
        }

        return new self(
            approval: $approval,
            locationKey: $data['locationKey'],
        );
    }

    public function toArray(): array
    {
        return [
            'approval' => $this->approval instanceof Arrayable ? $this->approval->toArray() : $this->approval,
            'locationKey' => $this->locationKey,
        ];
    }
}
