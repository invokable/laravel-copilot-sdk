<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\FactoryDurableOperation;
use Revolution\Copilot\Enums\FactoryRunFailureType;
use Revolution\Copilot\Types\Rpc\AgentInfo;
use Revolution\Copilot\Types\Rpc\EventLogReadRequest;
use Revolution\Copilot\Types\Rpc\FactoryExecuteResult;
use Revolution\Copilot\Types\Rpc\FactoryRunFailure;
use Revolution\Copilot\Types\Rpc\HistoryTruncateResult;
use Revolution\Copilot\Types\Rpc\ModelSwitchToRequest;
use Revolution\Copilot\Types\Rpc\ModelSwitchToResult;
use Revolution\Copilot\Types\Rpc\PermissionsResetSessionApprovalsRequest;

describe('AgentInfo prompt field (copilot-sdk #2140)', function () {
    it('carries the optional prompt', function () {
        $info = AgentInfo::fromArray([
            'name' => 'reviewer',
            'displayName' => 'Reviewer',
            'description' => 'Reviews code',
            'prompt' => 'You are a reviewer.',
        ]);

        expect($info->prompt)->toBe('You are a reviewer.')
            ->and($info->toArray()['prompt'])->toBe('You are a reviewer.');
    });

    it('omits prompt when absent', function () {
        $info = AgentInfo::fromArray(['name' => 'a', 'displayName' => 'A', 'description' => 'd']);

        expect($info->prompt)->toBeNull()
            ->and($info->toArray())->not->toHaveKey('prompt');
    });
});

describe('EventLogReadRequest includeEphemeral (copilot-sdk #2140)', function () {
    it('forwards includeEphemeral', function () {
        $request = EventLogReadRequest::fromArray(['includeEphemeral' => true]);

        expect($request->includeEphemeral)->toBeTrue()
            ->and($request->toArray())->toBe(['includeEphemeral' => true]);
    });
});

describe('ModelSwitchToRequest deferIfModelChangeQueued (copilot-sdk #2140)', function () {
    it('forwards the flag', function () {
        $request = ModelSwitchToRequest::fromArray([
            'modelId' => 'gpt',
            'deferIfModelChangeQueued' => true,
        ]);

        expect($request->deferIfModelChangeQueued)->toBeTrue()
            ->and($request->toArray())->toBe(['modelId' => 'gpt', 'deferIfModelChangeQueued' => true]);
    });
});

describe('ModelSwitchToResult deferred (copilot-sdk #2140)', function () {
    it('forwards the deferred flag', function () {
        $result = ModelSwitchToResult::fromArray(['modelId' => 'gpt', 'deferred' => true]);

        expect($result->deferred)->toBeTrue()
            ->and($result->toArray())->toBe(['modelId' => 'gpt', 'deferred' => true]);
    });
});

describe('HistoryTruncateResult checkpoint cleanup (copilot-sdk #2140)', function () {
    it('carries checkpoint cleanup metadata', function () {
        $result = HistoryTruncateResult::fromArray([
            'eventsRemoved' => 4,
            'checkpointCleanupFailed' => true,
            'checkpointCleanupError' => 'disk full',
        ]);

        expect($result->checkpointCleanupFailed)->toBeTrue()
            ->and($result->toArray())->toBe([
                'eventsRemoved' => 4,
                'checkpointCleanupFailed' => true,
                'checkpointCleanupError' => 'disk full',
            ]);
    });

    it('stays minimal without cleanup metadata', function () {
        expect(HistoryTruncateResult::fromArray(['eventsRemoved' => 2])->toArray())->toBe(['eventsRemoved' => 2]);
    });
});

describe('PermissionsResetSessionApprovalsRequest includeLocation (copilot-sdk #2140)', function () {
    it('forwards includeLocation', function () {
        $request = PermissionsResetSessionApprovalsRequest::fromArray(['includeLocation' => true]);

        expect($request->includeLocation)->toBeTrue()
            ->and($request->toArray())->toBe(['includeLocation' => true]);
    });

    it('stays empty when omitted', function () {
        expect(PermissionsResetSessionApprovalsRequest::fromArray([])->toArray())->toBe([]);
    });
});
