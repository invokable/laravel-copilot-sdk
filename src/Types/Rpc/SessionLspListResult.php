<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Runtime-owned configuration inventory and configuration diagnostics.
 *
 * @experimental
 */
readonly class SessionLspListResult implements Arrayable
{
    /**
     * @param  LspServerSummary[]  $configs
     * @param  LspServerSummary[]  $userServers
     * @param  LspServerSummary[]  $pluginServers
     * @param  LspServerSummary[]  $projectServers
     * @param  LspConfigurationProblem[]  $projectProblems
     * @param  string[]  $userDisabledServers
     * @param  string[]  $warnings
     * @param  string[]  $pluginWarnings
     */
    public function __construct(
        public array $configs = [],
        public array $userServers = [],
        public array $pluginServers = [],
        public array $projectServers = [],
        public string $userConfigPath = '',
        public ?string $projectConfigPath = null,
        public ?string $userConfigError = null,
        public array $projectProblems = [],
        public ?string $projectInspectionError = null,
        public array $userDisabledServers = [],
        public array $warnings = [],
        public array $pluginWarnings = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            configs: array_map(
                static fn (array $config): LspServerSummary => LspServerSummary::fromArray($config),
                $data['configs'] ?? [],
            ),
            userServers: array_map(
                static fn (array $server): LspServerSummary => LspServerSummary::fromArray($server),
                $data['userServers'] ?? [],
            ),
            pluginServers: array_map(
                static fn (array $server): LspServerSummary => LspServerSummary::fromArray($server),
                $data['pluginServers'] ?? [],
            ),
            projectServers: array_map(
                static fn (array $server): LspServerSummary => LspServerSummary::fromArray($server),
                $data['projectServers'] ?? [],
            ),
            userConfigPath: Arr::string($data, 'userConfigPath', ''),
            projectConfigPath: isset($data['projectConfigPath']) ? Arr::string($data, 'projectConfigPath') : null,
            userConfigError: isset($data['userConfigError']) ? Arr::string($data, 'userConfigError') : null,
            projectProblems: array_map(
                static fn (array $problem): LspConfigurationProblem => LspConfigurationProblem::fromArray($problem),
                $data['projectProblems'] ?? [],
            ),
            projectInspectionError: isset($data['projectInspectionError']) ? Arr::string($data, 'projectInspectionError') : null,
            userDisabledServers: $data['userDisabledServers'] ?? [],
            warnings: $data['warnings'] ?? [],
            pluginWarnings: $data['pluginWarnings'] ?? [],
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'configs' => array_map(static fn (LspServerSummary $config): array => $config->toArray(), $this->configs),
            'userServers' => array_map(static fn (LspServerSummary $server): array => $server->toArray(), $this->userServers),
            'pluginServers' => array_map(static fn (LspServerSummary $server): array => $server->toArray(), $this->pluginServers),
            'projectServers' => array_map(static fn (LspServerSummary $server): array => $server->toArray(), $this->projectServers),
            'userConfigPath' => $this->userConfigPath,
            'projectConfigPath' => $this->projectConfigPath,
            'userConfigError' => $this->userConfigError,
            'projectProblems' => array_map(static fn (LspConfigurationProblem $problem): array => $problem->toArray(), $this->projectProblems),
            'projectInspectionError' => $this->projectInspectionError,
            'userDisabledServers' => $this->userDisabledServers,
            'warnings' => $this->warnings,
            'pluginWarnings' => $this->pluginWarnings,
        ], static fn ($value): bool => $value !== null);
    }
}
