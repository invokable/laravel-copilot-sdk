<?php

declare(strict_types=1);

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Types\Rpc\AgentGetCurrentResult;
use Revolution\Copilot\Types\Rpc\AgentInfo;
use Revolution\Copilot\Types\Rpc\AgentList;
use Revolution\Copilot\Types\Rpc\AgentSelectRequest;
use Revolution\Copilot\Types\Rpc\AgentSelectResult;

describe('AgentInfo', function () {
    it('can be created from array', function () {
        $agent = AgentInfo::fromArray([
            'name' => 'test-agent',
            'displayName' => 'Test Agent',
            'description' => 'A test agent',
        ]);

        expect($agent->name)->toBe('test-agent')
            ->and($agent->displayName)->toBe('Test Agent')
            ->and($agent->description)->toBe('A test agent');
    });

    it('can convert to array', function () {
        $agent = new AgentInfo(
            name: 'test',
            displayName: 'Test',
            description: 'Testing',
        );

        expect($agent->toArray())->toBe([
            'name' => 'test',
            'displayName' => 'Test',
            'description' => 'Testing',
        ]);
    });

    it('implements Arrayable interface', function () {
        $agent = new AgentInfo(name: 'a', displayName: 'b', description: 'c');
        expect($agent)->toBeInstanceOf(Arrayable::class);
    });
});

describe('AgentList', function () {
    it('can be created from array', function () {
        $result = AgentList::fromArray([
            'agents' => [
                [
                    'name' => 'agent1',
                    'displayName' => 'Agent 1',
                    'description' => 'First agent',
                ],
            ],
        ]);

        expect($result->agents)->toHaveCount(1)
            ->and($result->agents[0])->toBeInstanceOf(AgentInfo::class)
            ->and($result->agents[0]->name)->toBe('agent1');
    });

    it('handles empty agents list', function () {
        $result = AgentList::fromArray([]);
        expect($result->agents)->toBe([]);
    });
});

describe('AgentGetCurrentResult', function () {
    it('can be created with agent', function () {
        $result = AgentGetCurrentResult::fromArray([
            'agent' => [
                'name' => 'test',
                'displayName' => 'Test',
                'description' => 'Testing',
            ],
        ]);

        expect($result->agent)->toBeInstanceOf(AgentInfo::class)
            ->and($result->agent->name)->toBe('test');
    });

    it('can be created with null agent', function () {
        $result = AgentGetCurrentResult::fromArray([]);
        expect($result->agent)->toBeNull();
    });
});

describe('AgentSelectRequest', function () {
    it('can be created and converted', function () {
        $params = new AgentSelectRequest(name: 'my-agent');
        expect($params->toArray())->toBe(['name' => 'my-agent']);
    });
});

describe('AgentSelectResult', function () {
    it('can be created from array', function () {
        $result = AgentSelectResult::fromArray([
            'agent' => [
                'name' => 'selected',
                'displayName' => 'Selected Agent',
                'description' => 'The selected agent',
            ],
        ]);

        expect($result->agent)->toBeInstanceOf(AgentInfo::class)
            ->and($result->agent->name)->toBe('selected');
    });
});
