<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\FactoryPhaseStatus;
use Revolution\Copilot\Enums\FactoryRunStatus;
use Revolution\Copilot\Types\Rpc\FactoryAgentSummary;
use Revolution\Copilot\Types\Rpc\FactoryCurrentPhase;
use Revolution\Copilot\Types\Rpc\FactoryDeclaredLimits;
use Revolution\Copilot\Types\Rpc\FactoryPhaseObservation;
use Revolution\Copilot\Types\Rpc\FactoryProgressLine;
use Revolution\Copilot\Types\Rpc\FactoryProgressPage;
use Revolution\Copilot\Types\Rpc\FactoryRunConsumed;
use Revolution\Copilot\Types\Rpc\FactoryRunDetail;
use Revolution\Copilot\Types\Rpc\FactoryRunSummary;
use Revolution\Copilot\Types\Rpc\FactoryRunTerminal;

describe('FactoryRunConsumed', function () {
    it('roundtrips', function () {
        $data = ['activeMs' => 120, 'subagents' => 3, 'nanoAiu' => 4200];

        expect(FactoryRunConsumed::fromArray($data)->toArray())->toBe($data);
    });
});

describe('FactoryDeclaredLimits', function () {
    it('roundtrips with all fields', function () {
        $data = [
            'maxConcurrentSubagents' => 2,
            'maxTotalSubagents' => 10,
            'timeoutSeconds' => 30.0,
            'maxAiCredits' => 5.0,
        ];

        expect(FactoryDeclaredLimits::fromArray($data)->toArray())->toBe($data);
    });

    it('drops null fields', function () {
        expect(FactoryDeclaredLimits::fromArray([])->toArray())->toBe([]);
    });
});

describe('FactoryCurrentPhase', function () {
    it('keeps nullable ordinal', function () {
        $phase = FactoryCurrentPhase::fromArray(['id' => 'plan', 'ordinal' => 0]);

        expect($phase->id)->toBe('plan')
            ->and($phase->ordinal)->toBe(0)
            ->and($phase->toArray())->toBe(['id' => 'plan', 'ordinal' => 0]);
    });
});

describe('FactoryRunTerminal', function () {
    it('roundtrips a completed run preview', function () {
        $terminal = FactoryRunTerminal::fromArray(['resultPreview' => 'done']);

        expect($terminal->resultPreview)->toBe('done')
            ->and($terminal->toArray())->toBe(['resultPreview' => 'done']);
    });
});

describe('FactoryAgentSummary', function () {
    it('creates from array and converts back', function () {
        $agent = FactoryAgentSummary::fromArray([
            'agentId' => 'a-1',
            'toolCallId' => 't-1',
            'runId' => 'run-1',
            'phaseId' => 'build',
            'label' => 'Builder',
            'agentType' => 'general',
            'status' => 'running',
            'activeMs' => 500,
            'requestedModel' => 'gpt',
        ]);

        expect($agent->agentId)->toBe('a-1')
            ->and($agent->phaseId)->toBe('build')
            ->and($agent->activeMs)->toBe(500)
            ->and($agent->toArray()['requestedModel'])->toBe('gpt');
    });
});

describe('FactoryPhaseObservation', function () {
    it('resolves the status enum', function () {
        $phase = FactoryPhaseObservation::fromArray([
            'id' => 'build',
            'ordinal' => 1,
            'title' => 'Build',
            'status' => 'active',
            'lastEnteredRunAttempt' => 1,
            'entryCount' => 1,
            'accumulatedActiveMs' => 10,
            'currentActiveMs' => 5,
            'totalAgentCount' => 2,
            'liveAgentCount' => 1,
        ]);

        expect($phase->status)->toBe(FactoryPhaseStatus::ACTIVE)
            ->and($phase->toArray()['status'])->toBe('active');
    });
});

describe('FactoryProgressLine and FactoryProgressPage', function () {
    it('nests progress lines', function () {
        $page = FactoryProgressPage::fromArray([
            'records' => [
                ['seq' => 1, 'attempt' => 1, 'phaseId' => null, 'recordedAt' => 100, 'kind' => 'log', 'text' => 'hi'],
            ],
            'oldestSeq' => 1,
            'newestSeq' => 1,
            'hasMoreOlder' => false,
            'hasMoreNewer' => true,
            'revision' => 7,
        ]);

        expect($page->records)->toHaveCount(1)
            ->and($page->records[0])->toBeInstanceOf(FactoryProgressLine::class)
            ->and($page->records[0]->text)->toBe('hi')
            ->and($page->toArray()['hasMoreNewer'])->toBeTrue();
    });
});

describe('FactoryRunSummary', function () {
    it('nests declared limits, consumed, and terminal', function () {
        $summary = FactoryRunSummary::fromArray([
            'runId' => 'run-1',
            'factoryName' => 'greeter',
            'description' => 'desc',
            'status' => 'running',
            'revision' => 3,
            'createdAt' => 1,
            'startedAt' => 2,
            'updatedAt' => 3,
            'completedAt' => null,
            'currentPhase' => ['id' => 'plan', 'ordinal' => 0],
            'declaredPhaseCount' => 2,
            'liveAgentCount' => 1,
            'totalSpawnedAgentCount' => 4,
            'consumed' => ['activeMs' => 10, 'subagents' => 1, 'nanoAiu' => 0],
            'declaredLimits' => ['maxTotalSubagents' => 10],
            'approved' => null,
            'observedAt' => 9,
            'activeSegmentStartedAt' => null,
            'terminal' => null,
        ]);

        expect($summary->status)->toBe(FactoryRunStatus::RUNNING)
            ->and($summary->currentPhase)->toBeInstanceOf(FactoryCurrentPhase::class)
            ->and($summary->consumed)->toBeInstanceOf(FactoryRunConsumed::class)
            ->and($summary->declaredLimits)->toBeInstanceOf(FactoryDeclaredLimits::class)
            ->and($summary->toArray()['consumed'])->toBe(['activeMs' => 10, 'subagents' => 1, 'nanoAiu' => 0]);
    });
});

describe('FactoryRunDetail', function () {
    it('carries phases, agents, and progress', function () {
        $detail = FactoryRunDetail::fromArray([
            'runId' => 'run-1',
            'factoryName' => 'greeter',
            'description' => 'desc',
            'status' => 'completed',
            'revision' => 3,
            'createdAt' => 1,
            'startedAt' => 2,
            'updatedAt' => 3,
            'completedAt' => 4,
            'currentPhase' => null,
            'declaredPhaseCount' => 1,
            'liveAgentCount' => 0,
            'totalSpawnedAgentCount' => 1,
            'consumed' => ['activeMs' => 10, 'subagents' => 1, 'nanoAiu' => 0],
            'declaredLimits' => [],
            'approved' => null,
            'observedAt' => 9,
            'activeSegmentStartedAt' => null,
            'terminal' => ['resultPreview' => 'ok'],
            'phases' => [[
                'id' => 'build', 'ordinal' => 0, 'title' => 'Build', 'status' => 'completed',
                'lastEnteredRunAttempt' => 1, 'entryCount' => 1, 'accumulatedActiveMs' => 1,
                'currentActiveMs' => 0, 'totalAgentCount' => 1, 'liveAgentCount' => 0,
            ]],
            'agents' => [[
                'agentId' => 'a-1', 'toolCallId' => 't-1', 'runId' => 'run-1', 'phaseId' => 'build',
                'label' => 'l', 'agentType' => 'g', 'status' => 'done', 'activeMs' => 1,
            ]],
            'progress' => ['records' => [], 'oldestSeq' => null, 'newestSeq' => null, 'hasMoreOlder' => false, 'hasMoreNewer' => false, 'revision' => 1],
        ]);

        expect($detail->phases[0])->toBeInstanceOf(FactoryPhaseObservation::class)
            ->and($detail->agents[0])->toBeInstanceOf(FactoryAgentSummary::class)
            ->and($detail->progress)->toBeInstanceOf(FactoryProgressPage::class)
            ->and($detail->terminal)->toBeInstanceOf(FactoryRunTerminal::class)
            ->and($detail->toArray()['terminal'])->toBe(['resultPreview' => 'ok']);
    });
});
