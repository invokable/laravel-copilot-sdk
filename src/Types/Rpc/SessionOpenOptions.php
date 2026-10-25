<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Closure;
use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Types\ResumeSessionConfig;
use Revolution\Copilot\Types\SessionConfig;

/**
 * Options accepted by the sessions.open wire method.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionOpenOptions implements Arrayable
{
    /**
     * @var list<string>
     */
    private const WIRE_FIELDS = [
        'sessionId',
        'name',
        'model',
        'reasoningEffort',
        'reasoningSummary',
        'verbosity',
        'clientName',
        'authClientIdMetadataUrl',
        'clientKind',
        'lspClientName',
        'integrationId',
        'expAssignments',
        'enableManagedSettings',
        'excludedManagedSettings',
        'enforceManagedModelDefaults',
        'managedSettings',
        'interactiveUser',
        'enableFileChangeTracking',
        'featureFlags',
        'isExperimentalMode',
        'authInfo',
        'provider',
        'capi',
        'autoTierIsExplicit',
        'providers',
        'models',
        'workingDirectory',
        'additionalDirectories',
        'workingDirectoryContext',
        'remoteSteerable',
        'remoteExporting',
        'remoteDefaultedOn',
        'detachedFromSpawningParentSessionId',
        'detachedFromSpawningParentEngagementId',
        'availableTools',
        'excludedTools',
        'includedBuiltinAgents',
        'excludedBuiltinAgents',
        'enableScriptSafety',
        'shell',
        'shellInitProfile',
        'shellProcessFlags',
        'sandboxConfig',
        'sandboxConfigSource',
        'logInteractiveShells',
        'envValueMode',
        'disabledMcpServers',
        'managedMcpServers',
        'allowAllMcpServerInstructions',
        'skillDirectories',
        'agentDirectories',
        'instructionDirectories',
        'hookDirectories',
        'ignoredSkillsLocations',
        'enableSkills',
        'hasSkillProvider',
        'includedBuiltinSkills',
        'disabledSkills',
        'installedPlugins',
        'customAgentsLocalOnly',
        'skipCustomInstructions',
        'refreshCustomInstructions',
        'disabledInstructionSources',
        'coauthorEnabled',
        'trajectoryFile',
        'enableStreaming',
        'enableCitations',
        'copilotUrl',
        'askUserDisabled',
        'continueOnAutoMode',
        'runningInInteractiveMode',
        'enableOnDemandInstructionDiscovery',
        'maxInlineBinaryBytes',
        'modelCapabilitiesOverrides',
        'sessionLimits',
        'autopilot',
        'agentContext',
        'eventsLogDirectory',
        'eventsLogIncludesSubagents',
        'configDir',
        'additionalContentExclusionPolicies',
        'enableFileHooks',
        'memory',
        'sessionCapabilities',
    ];

    public function __construct(
        public SessionConfig|ResumeSessionConfig|array $options,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static($data);
    }

    public function toArray(): array
    {
        $options = $this->options instanceof Arrayable
            ? $this->options->toArray()
            : $this->options;

        if (array_key_exists('enableExperimentalMode', $options)) {
            $options['isExperimentalMode'] ??= $options['enableExperimentalMode'];
            unset($options['enableExperimentalMode']);
        }

        if (array_key_exists('modelCapabilities', $options)) {
            $options['modelCapabilitiesOverrides'] ??= $options['modelCapabilities'];
            unset($options['modelCapabilities']);
        }

        if (array_key_exists('streaming', $options)) {
            $options['enableStreaming'] ??= $options['streaming'];
            unset($options['streaming']);
        }

        $options = array_intersect_key($options, array_flip(self::WIRE_FIELDS));

        foreach ($options as $key => $value) {
            if ($value instanceof Closure) {
                unset($options[$key]);
            } else {
                $options[$key] = self::normalizeValue($value);
            }
        }

        return $options;
    }

    private static function normalizeValue(mixed $value): mixed
    {
        if ($value instanceof Arrayable) {
            return self::normalizeValue($value->toArray());
        }

        if (! is_array($value)) {
            return $value;
        }

        foreach ($value as $key => $nestedValue) {
            if ($nestedValue instanceof Closure) {
                unset($value[$key]);
            } else {
                $value[$key] = self::normalizeValue($nestedValue);
            }
        }

        return $value;
    }
}
