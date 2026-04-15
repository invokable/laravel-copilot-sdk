<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingSkills;
use Revolution\Copilot\Types\Rpc\SkillList;
use Revolution\Copilot\Types\Rpc\SkillsDisableRequest;
use Revolution\Copilot\Types\Rpc\SkillsEnableRequest;

describe('PendingSkills', function () {
    it('calls session.skills.list and returns result', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.skills.list', ['sessionId' => 'session-abc'])
            ->andReturn([
                'skills' => [
                    [
                        'name' => 'code-review',
                        'description' => 'Reviews code',
                        'source' => 'project',
                        'userInvocable' => true,
                        'enabled' => true,
                    ],
                ],
            ]);

        $pending = new PendingSkills($client, 'session-abc');
        $result = $pending->list();

        expect($result)->toBeInstanceOf(SkillList::class)
            ->and($result->skills)->toHaveCount(1)
            ->and($result->skills[0]->name)->toBe('code-review');
    });

    it('calls session.skills.list with empty result', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.skills.list', ['sessionId' => 'session-abc'])
            ->andReturn([]);

        $pending = new PendingSkills($client, 'session-abc');
        $result = $pending->list();

        expect($result)->toBeInstanceOf(SkillList::class)
            ->and($result->skills)->toBe([]);
    });

    it('calls session.skills.enable with typed params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.skills.enable',
                Mockery::on(fn ($params) => $params['sessionId'] === 'session-abc'
                    && $params['name'] === 'code-review'),
            )
            ->andReturn([]);

        $pending = new PendingSkills($client, 'session-abc');
        $result = $pending->enable(new SkillsEnableRequest(name: 'code-review'));

        expect($result)->toBe([]);
    });

    it('calls session.skills.enable with array params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.skills.enable',
                Mockery::on(fn ($params) => $params['sessionId'] === 'session-abc'
                    && $params['name'] === 'testing'),
            )
            ->andReturn([]);

        $pending = new PendingSkills($client, 'session-abc');
        $result = $pending->enable(['name' => 'testing']);

        expect($result)->toBe([]);
    });

    it('calls session.skills.disable with typed params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.skills.disable',
                Mockery::on(fn ($params) => $params['sessionId'] === 'session-abc'
                    && $params['name'] === 'code-review'),
            )
            ->andReturn([]);

        $pending = new PendingSkills($client, 'session-abc');
        $result = $pending->disable(new SkillsDisableRequest(name: 'code-review'));

        expect($result)->toBe([]);
    });

    it('calls session.skills.disable with array params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.skills.disable',
                Mockery::on(fn ($params) => $params['sessionId'] === 'session-abc'
                    && $params['name'] === 'testing'),
            )
            ->andReturn([]);

        $pending = new PendingSkills($client, 'session-abc');
        $result = $pending->disable(['name' => 'testing']);

        expect($result)->toBe([]);
    });

    it('calls session.skills.reload', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.skills.reload', ['sessionId' => 'session-abc'])
            ->andReturn([]);

        $pending = new PendingSkills($client, 'session-abc');
        $result = $pending->reload();

        expect($result)->toBe([]);
    });
});
