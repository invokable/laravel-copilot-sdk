<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\SessionUiSnapshotResult;
use Revolution\Copilot\Types\Rpc\UIBackgroundAgentTask;
use Revolution\Copilot\Types\Rpc\UIElicitationPromptForm;
use Revolution\Copilot\Types\Rpc\UIElicitationPromptUrl;
use Revolution\Copilot\Types\Rpc\UIExitPlanModeAction;
use Revolution\Copilot\Types\Rpc\UIPluginActivationDecision;
use Revolution\Copilot\Types\Rpc\UIPluginActivationPolicy;
use Revolution\Copilot\Types\Rpc\UIPluginActivationSnapshot;
use Revolution\Copilot\Types\Rpc\UIPluginActivationWarning;
use Revolution\Copilot\Types\Rpc\UIServiceTask;

describe('SessionUiSnapshotResult', function () {
    it('round-trips the complete typed UI snapshot', function () {
        $data = [
            'startupPrompts' => ['Welcome'],
            'hasPendingRepoHooks' => false,
            'pendingUserInputRequests' => [
                [
                    'requestId' => 'input-1',
                    'request' => [
                        'question' => 'Choose a color',
                        'choices' => ['blue', 'green'],
                        'allowFreeform' => false,
                        'toolCallId' => 'tool-1',
                    ],
                ],
            ],
            'pendingElicitationRequests' => [
                [
                    'requestId' => 'elicitation-form',
                    'request' => [
                        'mode' => 'form',
                        'message' => 'Enter your name',
                        'requestedSchema' => [
                            'type' => 'object',
                            'properties' => ['name' => ['type' => 'string']],
                            'required' => ['name'],
                        ],
                        '_meta' => ['request' => 'form'],
                        'task' => ['ttl' => 1200],
                    ],
                    'elicitationSource' => 'mcp',
                    'elicitationSourceDisplayName' => 'Example MCP',
                ],
                [
                    'requestId' => 'elicitation-url',
                    'request' => [
                        'mode' => 'url',
                        'message' => 'Open this page',
                        'url' => 'https://example.test/authorize',
                        'elicitationId' => 'elicitation-2',
                        '_meta' => ['request' => 'url'],
                        'task' => ['ttl' => 2400],
                    ],
                ],
            ],
            'pendingExitPlanModeRequests' => [
                [
                    'requestId' => 'plan-1',
                    'request' => [
                        'requestId' => 'plan-1',
                        'summary' => 'Update the app',
                        'planContent' => '1. Update code',
                        'actions' => ['interactive', 'future_action'],
                        'recommendedAction' => 'autopilot',
                        'model' => 'gpt-test',
                    ],
                ],
            ],
            'serviceTasks' => [
                [
                    'type' => 'service',
                    'id' => 'service-1',
                    'serviceKind' => 'lsp',
                    'serviceId' => 'php',
                    'description' => 'PHP language server',
                    'status' => 'starting',
                    'ready' => false,
                    'phase' => 'initialize',
                    'percentage' => 35.5,
                    'startedAt' => 100,
                    'completedAt' => 200,
                    'log' => [
                        ['timestamp' => 150, 'message' => 'Starting'],
                    ],
                ],
            ],
            'initializingServiceCount' => 1,
            'sidekickBackgroundTasks' => [
                [
                    'type' => 'agent',
                    'id' => 'agent-1',
                    'toolCallId' => 'tool-agent',
                    'displayName' => 'Research',
                    'description' => 'Research the API',
                    'status' => 'running',
                    'startedAt' => 300,
                    'completedAt' => 350,
                    'activeTimeMs' => 50,
                    'activeStartedAt' => 300,
                    'error' => 'warning',
                    'agentType' => 'explore',
                    'prompt' => 'Find the API',
                    'result' => 'Found it',
                    'modelOverride' => 'gpt-fast',
                    'resolvedModel' => 'gpt-resolved',
                    'executionMode' => 'background',
                    'canPromoteToBackground' => false,
                    'latestResponse' => 'Working',
                    'idleSince' => 340,
                    'source' => 'history',
                ],
            ],
            'agentTurnActive' => true,
            'processing' => false,
            'isExperimentalMode' => false,
            'isSessionTelemetryEnabled' => true,
            'hasActiveResumeWork' => false,
            'primaryEventCounts' => ['assistant.message' => 3],
            'workingDirectoryRevision' => 7,
            'supportsMcpApps' => true,
            'pluginActivationSnapshot' => [
                'plugins' => [
                    [
                        'name' => 'sample',
                        'marketplace' => 'example',
                        'version' => '1.0',
                        'installed_at' => '2026-01-01',
                        'enabled' => true,
                    ],
                ],
                'inventory' => [
                    [
                        'plugin' => [
                            'name' => 'sample',
                            'marketplace' => 'example',
                            'version' => '1.0',
                            'installed_at' => '2026-01-01',
                            'enabled' => true,
                        ],
                        'active' => true,
                        'source' => 'settings',
                        'reason' => 'enabled',
                        'persisted' => true,
                    ],
                ],
                'warnings' => [
                    [
                        'kind' => 'missing',
                        'layer' => 'repository',
                        'marketplace' => 'example',
                        'marketplaceDir' => '/plugins',
                        'plugin' => 'sample',
                        'expectedDir' => '/plugins/sample',
                        'message' => 'Plugin directory is missing',
                    ],
                ],
                'context' => [
                    'requestedWorkingDirectory' => '/repo',
                    'resolvedWorkingDirectory' => '/repo',
                    'settingsRoot' => '/settings',
                    'gitRoot' => '/repo',
                    'trustRoot' => '/repo',
                    'trusted' => true,
                    'trustSource' => 'user',
                ],
                'fingerprint' => 'activation-1',
                'repositoryLivePluginPaths' => ['/repo/.plugins/sample'],
                'resolutionFailure' => 'partial',
            ],
            'pluginActivationPolicy' => null,
        ];

        $result = SessionUiSnapshotResult::fromArray($data);

        expect($result->toArray())->toBe($data)
            ->and($result->pendingElicitationRequests[0]->request)->toBeInstanceOf(UIElicitationPromptForm::class)
            ->and($result->pendingElicitationRequests[1]->request)->toBeInstanceOf(UIElicitationPromptUrl::class)
            ->and($result->pendingExitPlanModeRequests[0]->request->actions[0])->toBe(UIExitPlanModeAction::Interactive)
            ->and($result->pendingExitPlanModeRequests[0]->request->actions[1])->toBe('future_action')
            ->and($result->serviceTasks[0])->toBeInstanceOf(UIServiceTask::class)
            ->and($result->sidekickBackgroundTasks[0])->toBeInstanceOf(UIBackgroundAgentTask::class)
            ->and($result->pluginActivationSnapshot)->toBeInstanceOf(UIPluginActivationSnapshot::class)
            ->and($result->pluginActivationSnapshot->inventory[0])->toBeInstanceOf(UIPluginActivationDecision::class)
            ->and($result->pluginActivationSnapshot->warnings[0])->toBeInstanceOf(UIPluginActivationWarning::class)
            ->and($result->pluginActivationPolicy)->toBeNull();
    });

    it('preserves explicit null plugin activation fields but omits absent fields', function () {
        $explicitNulls = SessionUiSnapshotResult::fromArray([
            'pluginActivationSnapshot' => null,
            'pluginActivationPolicy' => null,
        ])->toArray();
        $absent = SessionUiSnapshotResult::fromArray([])->toArray();

        expect(array_key_exists('pluginActivationSnapshot', $explicitNulls))->toBeTrue()
            ->and($explicitNulls['pluginActivationSnapshot'])->toBeNull()
            ->and(array_key_exists('pluginActivationPolicy', $explicitNulls))->toBeTrue()
            ->and($explicitNulls['pluginActivationPolicy'])->toBeNull()
            ->and(array_key_exists('pluginActivationSnapshot', $absent))->toBeFalse()
            ->and(array_key_exists('pluginActivationPolicy', $absent))->toBeFalse();
    });

    it('parses plugin activation policy as typed plugin and trust data', function () {
        $policy = UIPluginActivationPolicy::fromArray([
            'includeAmbient' => true,
            'explicitPlugins' => [
                [
                    'name' => 'explicit',
                    'marketplace' => 'example',
                    'installed_at' => '2026-01-01',
                    'enabled' => true,
                ],
            ],
            'trust' => [
                'mode' => 'trusted',
                'trusted' => true,
                'source' => 'user',
            ],
            'ambientPlugins' => [],
            'baseEnabledPlugins' => ['explicit' => true],
            'defaultEnabledPlugins' => ['ambient' => false],
        ]);

        expect($policy->explicitPlugins[0]->name)->toBe('explicit')
            ->and($policy->trust->trusted)->toBeTrue()
            ->and($policy->toArray()['defaultEnabledPlugins'])->toBe(['ambient' => false]);
    });
});
