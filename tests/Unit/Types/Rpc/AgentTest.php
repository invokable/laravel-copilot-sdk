<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\AgentInfo;
use Revolution\Copilot\Types\Rpc\SessionAgentGetCurrentResult;
use Revolution\Copilot\Types\Rpc\SessionAgentListResult;
use Revolution\Copilot\Types\Rpc\SessionAgentSelectParams;
use Revolution\Copilot\Types\Rpc\SessionAgentSelectResult;

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
        expect($agent)->toBeInstanceOf(\Illuminate\Contracts\Support\Arrayable::class);
    });
});

describe('SessionAgentListResult', function () {
    it('can be created from array', function () {
        $result = SessionAgentListResult::fromArray([
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
        $result = SessionAgentListResult::fromArray([]);
        expect($result->agents)->toBe([]);
    });
});

describe('SessionAgentGetCurrentResult', function () {
    it('can be created with agent', function () {
        $result = SessionAgentGetCurrentResult::fromArray([
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
        $result = SessionAgentGetCurrentResult::fromArray([]);
        expect($result->agent)->toBeNull();
    });
});

describe('SessionAgentSelectParams', function () {
    it('can be created and converted', function () {
        $params = new SessionAgentSelectParams(name: 'my-agent');
        expect($params->toArray())->toBe(['name' => 'my-agent']);
    });
});

describe('SessionAgentSelectResult', function () {
    it('can be created from array', function () {
        $result = SessionAgentSelectResult::fromArray([
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
