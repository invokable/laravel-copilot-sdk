<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * @experimental
 */
readonly class SkillsLoadRequest implements Arrayable
{
    /**
     * @param  ?array<int, InstalledPlugin>  $additionalPlugins
     * @param  ?array<string, bool>  $enabledPlugins
     * @param  ?array<string, bool>  $teamDefaultEnabledPlugins
     * @param  ?array<int, string>  $ignoredSkillLocations
     */
    public function __construct(
        public string $cwd,
        public ?string $configDir = null,
        public ?string $homeDirectory = null,
        public ?string $environmentSkillDirectories = null,
        public ?string $builtinDirectory = null,
        public ?SkillScanInputs $inputs = null,
        public ?array $additionalPlugins = null,
        public ?array $enabledPlugins = null,
        public ?array $teamDefaultEnabledPlugins = null,
        public ?bool $enableConfigDiscovery = null,
        public ?bool $enableSkills = null,
        public ?array $ignoredSkillLocations = null,
        public ?bool $blockUserAndWorkspaceSkills = null,
        public ?bool $useCache = null,
        public ?bool $checkSources = null,
        public ?bool $checkOnly = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            cwd: Arr::string($data, 'cwd'),
            configDir: $data['configDir'] ?? null,
            homeDirectory: $data['homeDirectory'] ?? null,
            environmentSkillDirectories: $data['environmentSkillDirectories'] ?? null,
            builtinDirectory: $data['builtinDirectory'] ?? null,
            inputs: isset($data['inputs']) ? SkillScanInputs::fromArray($data['inputs']) : null,
            additionalPlugins: isset($data['additionalPlugins'])
                ? array_map(fn (array $plugin) => InstalledPlugin::fromArray($plugin), Arr::array($data, 'additionalPlugins'))
                : null,
            enabledPlugins: $data['enabledPlugins'] ?? null,
            teamDefaultEnabledPlugins: $data['teamDefaultEnabledPlugins'] ?? null,
            enableConfigDiscovery: $data['enableConfigDiscovery'] ?? null,
            enableSkills: $data['enableSkills'] ?? null,
            ignoredSkillLocations: $data['ignoredSkillLocations'] ?? null,
            blockUserAndWorkspaceSkills: $data['blockUserAndWorkspaceSkills'] ?? null,
            useCache: $data['useCache'] ?? null,
            checkSources: $data['checkSources'] ?? null,
            checkOnly: $data['checkOnly'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'cwd' => $this->cwd,
            'configDir' => $this->configDir,
            'homeDirectory' => $this->homeDirectory,
            'environmentSkillDirectories' => $this->environmentSkillDirectories,
            'builtinDirectory' => $this->builtinDirectory,
            'inputs' => $this->inputs?->toArray(),
            'additionalPlugins' => $this->additionalPlugins === null ? null : array_map(
                fn (InstalledPlugin $plugin) => $plugin->toArray(),
                $this->additionalPlugins,
            ),
            'enabledPlugins' => $this->enabledPlugins,
            'teamDefaultEnabledPlugins' => $this->teamDefaultEnabledPlugins,
            'enableConfigDiscovery' => $this->enableConfigDiscovery,
            'enableSkills' => $this->enableSkills,
            'ignoredSkillLocations' => $this->ignoredSkillLocations,
            'blockUserAndWorkspaceSkills' => $this->blockUserAndWorkspaceSkills,
            'useCache' => $this->useCache,
            'checkSources' => $this->checkSources,
            'checkOnly' => $this->checkOnly,
        ], fn ($value) => $value !== null);
    }
}
