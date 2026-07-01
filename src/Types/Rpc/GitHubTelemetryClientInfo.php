<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Client environment metadata describing the process that produced a telemetry event.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class GitHubTelemetryClientInfo implements Arrayable
{
    /**
     * @param  string  $cliVersion  Copilot CLI version string.
     * @param  string  $osPlatform  Operating system platform (e.g. darwin, linux, win32).
     * @param  string  $osVersion  Operating system version string.
     * @param  string  $osArch  Operating system architecture (e.g. arm64, x64).
     * @param  string  $nodeVersion  Node.js runtime version string.
     * @param  ?string  $copilotPlan  Copilot subscription plan, when known.
     * @param  ?string  $clientType  Type of client.
     * @param  ?string  $clientName  Name of the client application.
     * @param  ?bool  $isStaff  Whether the user is a GitHub/Microsoft staff member.
     * @param  ?string  $devDeviceId  Stable machine identifier for the device.
     */
    public function __construct(
        public string $cliVersion,
        public string $osPlatform,
        public string $osVersion,
        public string $osArch,
        public string $nodeVersion,
        public ?string $copilotPlan = null,
        public ?string $clientType = null,
        public ?string $clientName = null,
        public ?bool $isStaff = null,
        public ?string $devDeviceId = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            cliVersion: Arr::string($data, 'cli_version', ''),
            osPlatform: Arr::string($data, 'os_platform', ''),
            osVersion: Arr::string($data, 'os_version', ''),
            osArch: Arr::string($data, 'os_arch', ''),
            nodeVersion: Arr::string($data, 'node_version', ''),
            copilotPlan: $data['copilot_plan'] ?? null,
            clientType: $data['client_type'] ?? null,
            clientName: $data['client_name'] ?? null,
            isStaff: $data['is_staff'] ?? null,
            devDeviceId: $data['dev_device_id'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'cli_version' => $this->cliVersion,
            'os_platform' => $this->osPlatform,
            'os_version' => $this->osVersion,
            'os_arch' => $this->osArch,
            'node_version' => $this->nodeVersion,
            'copilot_plan' => $this->copilotPlan,
            'client_type' => $this->clientType,
            'client_name' => $this->clientName,
            'is_staff' => $this->isStaff,
            'dev_device_id' => $this->devDeviceId,
        ], fn ($v) => $v !== null);
    }
}
