<?php

declare(strict_types=1);

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\AutopilotEvaluateIdleContinuationOutcome;
use Revolution\Copilot\Enums\DiagnosticCursorStatus;
use Revolution\Copilot\Enums\QueuedCommandWaitRequirement;
use Revolution\Copilot\Types\Rpc\AutopilotEvaluateIdleContinuationRequest;
use Revolution\Copilot\Types\Rpc\AutopilotEvaluateIdleContinuationResult;
use Revolution\Copilot\Types\Rpc\CommandsReportQueuedCommandWaitRequest;
use Revolution\Copilot\Types\Rpc\CommandsReportQueuedCommandWaitResult;
use Revolution\Copilot\Types\Rpc\LifecycleDiagnosticEvent;
use Revolution\Copilot\Types\Rpc\LifecycleDiagnosticsReadRequest;
use Revolution\Copilot\Types\Rpc\LifecycleDiagnosticsReadResult;

describe('internal session RPC types', function () {
    it('serializes the empty Autopilot request and parses its result', function () {
        $request = AutopilotEvaluateIdleContinuationRequest::fromArray([]);
        $result = AutopilotEvaluateIdleContinuationResult::fromArray(['outcome' => 'continued']);

        expect($request)->toBeInstanceOf(Arrayable::class)
            ->and($request->toArray())->toBe([])
            ->and($result->outcome)->toBe(AutopilotEvaluateIdleContinuationOutcome::Continued)
            ->and($result->toArray())->toBe(['outcome' => 'continued']);
    });

    it('round-trips queued command wait request and result types', function () {
        $request = CommandsReportQueuedCommandWaitRequest::fromArray([
            'requestId' => 'request-1',
            'accountIdentity' => null,
            'requirement' => 'managed-policy-published',
            'waiting' => true,
        ]);
        $result = CommandsReportQueuedCommandWaitResult::fromArray(['recorded' => true]);

        expect($request)->toBeInstanceOf(Arrayable::class)
            ->and($request->requirement)->toBe(QueuedCommandWaitRequirement::ManagedPolicyPublished)
            ->and($request->toArray())->toBe([
                'requestId' => 'request-1',
                'accountIdentity' => null,
                'requirement' => 'managed-policy-published',
                'waiting' => true,
            ])
            ->and($result->recorded)->toBeTrue()
            ->and($result->toArray())->toBe(['recorded' => true]);
    });

    it('round-trips lifecycle diagnostic read requests and results', function () {
        $request = LifecycleDiagnosticsReadRequest::fromArray([
            'cursor' => 'cursor-1',
            'max' => 25,
            'waitMs' => 250,
        ]);
        $result = LifecycleDiagnosticsReadResult::fromArray([
            'events' => [[
                'data' => ['system' => 'mcp'],
                'timestamp' => '2026-10-31T12:00:00Z',
                'type' => 'runtime.lifecycle.system_state',
            ]],
            'cursor' => 'cursor-2',
            'cursorStatus' => 'ok',
            'droppedCount' => 2,
            'hasMore' => true,
        ]);

        expect($request)->toBeInstanceOf(Arrayable::class)
            ->and($request->toArray())->toBe([
                'cursor' => 'cursor-1',
                'max' => 25,
                'waitMs' => 250,
            ])
            ->and($result->events[0])->toBeInstanceOf(LifecycleDiagnosticEvent::class)
            ->and($result->cursorStatus)->toBe(DiagnosticCursorStatus::Ok)
            ->and($result->droppedCount)->toBe(2)
            ->and($result->toArray())->toBe([
                'events' => [[
                    'data' => ['system' => 'mcp'],
                    'timestamp' => '2026-10-31T12:00:00Z',
                    'type' => 'runtime.lifecycle.system_state',
                ]],
                'cursor' => 'cursor-2',
                'cursorStatus' => 'ok',
                'droppedCount' => 2,
                'hasMore' => true,
            ]);
    });
});
