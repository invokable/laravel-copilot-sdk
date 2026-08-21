<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\PermissionDecisionOutcome;
use Revolution\Copilot\Enums\PermissionDecisionSource;
use Revolution\Copilot\Enums\PermissionDecisionSurface;
use Revolution\Copilot\Types\Rpc\PermissionDecisionContext;
use Revolution\Copilot\Types\Rpc\PermissionDecisionRequest;

describe('PermissionDecisionContext', function () {
    it('maps from and to array', function () {
        $context = PermissionDecisionContext::fromArray([
            'outcome' => 'auto_approved',
            'source' => 'assisted_approval',
            'surface' => 'sdk',
        ]);

        expect($context->outcome)->toBe(PermissionDecisionOutcome::AUTO_APPROVED)
            ->and($context->source)->toBe(PermissionDecisionSource::ASSISTED_APPROVAL)
            ->and($context->surface)->toBe(PermissionDecisionSurface::SDK)
            ->and($context->toArray())->toBe([
                'outcome' => 'auto_approved',
                'source' => 'assisted_approval',
                'surface' => 'sdk',
            ]);
    });
});

describe('PermissionDecisionRequest', function () {
    it('maps without a decision context', function () {
        $request = PermissionDecisionRequest::fromArray([
            'requestId' => 'req-1',
            'result' => ['kind' => 'approve-once'],
        ]);

        expect($request->requestId)->toBe('req-1')
            ->and($request->result)->toBe(['kind' => 'approve-once'])
            ->and($request->decisionContext)->toBeNull()
            ->and($request->toArray())->toBe([
                'requestId' => 'req-1',
                'result' => ['kind' => 'approve-once'],
            ]);
    });

    it('maps with a decision context', function () {
        $request = PermissionDecisionRequest::fromArray([
            'requestId' => 'req-1',
            'result' => ['kind' => 'approve-once'],
            'decisionContext' => [
                'outcome' => 'prompted_user',
                'source' => 'human_response',
                'surface' => 'tui',
            ],
        ]);

        expect($request->decisionContext)->toBeInstanceOf(PermissionDecisionContext::class)
            ->and($request->decisionContext->outcome)->toBe(PermissionDecisionOutcome::PROMPTED_USER)
            ->and($request->toArray())->toBe([
                'requestId' => 'req-1',
                'result' => ['kind' => 'approve-once'],
                'decisionContext' => [
                    'outcome' => 'prompted_user',
                    'source' => 'human_response',
                    'surface' => 'tui',
                ],
            ]);
    });

    it('can be constructed directly with a decision context object', function () {
        $request = new PermissionDecisionRequest(
            requestId: 'req-2',
            result: ['kind' => 'reject'],
            decisionContext: new PermissionDecisionContext(
                outcome: PermissionDecisionOutcome::AUTOPILOT_DENIED,
                source: PermissionDecisionSource::HOST_POLICY,
                surface: PermissionDecisionSurface::PROMPT_MODE,
            ),
        );

        expect($request->toArray())->toBe([
            'requestId' => 'req-2',
            'result' => ['kind' => 'reject'],
            'decisionContext' => [
                'outcome' => 'autopilot_denied',
                'source' => 'host_policy',
                'surface' => 'prompt_mode',
            ],
        ]);
    });
});
