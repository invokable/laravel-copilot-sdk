<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingPlan;
use Revolution\Copilot\Types\Rpc\SessionPlanReadResult;
use Revolution\Copilot\Types\Rpc\SessionPlanUpdateParams;

describe('PendingPlan', function () {
    it('calls session.plan.read and returns result', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.plan.read',
                Mockery::on(fn ($params) => $params['sessionId'] === 'session-plan'),
            )
            ->andReturn([
                'exists' => true,
                'content' => '# My Plan',
                'path' => '/workspace/plan.md',
            ]);

        $pending = new PendingPlan($client, 'session-plan');
        $result = $pending->read();

        expect($result)->toBeInstanceOf(SessionPlanReadResult::class)
            ->and($result->exists)->toBeTrue()
            ->and($result->content)->toBe('# My Plan')
            ->and($result->path)->toBe('/workspace/plan.md');
    });

    it('calls session.plan.read when plan does not exist', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.plan.read', Mockery::any())
            ->andReturn(['exists' => false]);

        $pending = new PendingPlan($client, 'session-plan');
        $result = $pending->read();

        expect($result)->toBeInstanceOf(SessionPlanReadResult::class)
            ->and($result->exists)->toBeFalse()
            ->and($result->content)->toBeNull()
            ->and($result->path)->toBeNull();
    });

    it('calls session.plan.update with typed params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.plan.update',
                Mockery::on(fn ($params) => $params['sessionId'] === 'session-plan'
                    && $params['content'] === '# Updated Plan'),
            )
            ->andReturn(['updated' => true]);

        $pending = new PendingPlan($client, 'session-plan');
        $result = $pending->update(new SessionPlanUpdateParams(content: '# Updated Plan'));

        expect($result)->toBe(['updated' => true]);
    });

    it('calls session.plan.update with array params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.plan.update',
                Mockery::on(fn ($params) => $params['sessionId'] === 'session-plan'
                    && $params['content'] === '# Plan v2'),
            )
            ->andReturn([]);

        $pending = new PendingPlan($client, 'session-plan');
        $result = $pending->update(['content' => '# Plan v2']);

        expect($result)->toBe([]);
    });

    it('calls session.plan.delete and returns result', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.plan.delete',
                Mockery::on(fn ($params) => $params['sessionId'] === 'session-plan'),
            )
            ->andReturn(['deleted' => true]);

        $pending = new PendingPlan($client, 'session-plan');
        $result = $pending->delete();

        expect($result)->toBe(['deleted' => true]);
    });
});
