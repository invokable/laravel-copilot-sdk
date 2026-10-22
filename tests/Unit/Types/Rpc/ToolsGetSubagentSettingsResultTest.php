<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\AgentModelPolicy;
use Revolution\Copilot\Types\Rpc\SubagentSettings;
use Revolution\Copilot\Types\Rpc\SubagentSettingsEntry;
use Revolution\Copilot\Types\Rpc\ToolsGetSubagentSettingsResult;

describe('ToolsGetSubagentSettingsResult', function () {
    it('converts configured subagent settings and optional fields', function () {
        $result = ToolsGetSubagentSettingsResult::fromArray([
            'subagents' => [
                'agents' => [
                    'reviewer' => [
                        'model' => 'gpt-5',
                        'modelPolicy' => 'required',
                        'providerId' => 'provider-1',
                    ],
                ],
                'contextManagementTools' => false,
                'disabledSubagents' => ['planner'],
                'maxConcurrency' => 3,
                'maxDepth' => 2,
            ],
        ]);

        expect($result->subagents)->toBeInstanceOf(SubagentSettings::class)
            ->and($result->subagents->agents['reviewer'])->toBeInstanceOf(SubagentSettingsEntry::class)
            ->and($result->subagents->agents['reviewer']->modelPolicy)->toBe(AgentModelPolicy::REQUIRED)
            ->and($result->subagents->contextManagementTools)->toBeFalse()
            ->and($result->subagents->disabledSubagents)->toBe(['planner'])
            ->and($result->subagents->maxConcurrency)->toBe(3)
            ->and($result->subagents->maxDepth)->toBe(2)
            ->and($result->toArray())->toBe([
                'subagents' => [
                    'agents' => [
                        'reviewer' => [
                            'model' => 'gpt-5',
                            'modelPolicy' => 'required',
                            'providerId' => 'provider-1',
                        ],
                    ],
                    'disabledSubagents' => ['planner'],
                    'maxConcurrency' => 3,
                    'maxDepth' => 2,
                    'contextManagementTools' => false,
                ],
            ]);
    });

    it('preserves an empty settings configuration', function () {
        $result = ToolsGetSubagentSettingsResult::fromArray([
            'subagents' => ['agents' => []],
        ]);

        expect($result->subagents)->toBeInstanceOf(SubagentSettings::class)
            ->and($result->subagents->agents)->toBe([])
            ->and($result->toArray())->toBe(['subagents' => ['agents' => []]]);
    });

    it('returns null when no settings block exists', function () {
        $result = ToolsGetSubagentSettingsResult::fromArray(['subagents' => null]);

        expect($result->subagents)->toBeNull()
            ->and($result->toArray())->toBe([]);
    });
});
