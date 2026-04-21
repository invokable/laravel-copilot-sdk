<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingInstructions;
use Revolution\Copilot\Types\Rpc\InstructionsGetSourcesResult;

describe('PendingInstructions', function () {
    it('calls session.instructions.getSources and returns result', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.instructions.getSources',
                Mockery::on(fn ($params) => $params['sessionId'] === 'session-abc'),
            )
            ->andReturn([
                'sources' => [
                    [
                        'id' => 'src-1',
                        'label' => 'Repo Instructions',
                        'content' => 'Be helpful and concise.',
                        'sourcePath' => '.copilot/instructions.md',
                        'type' => 'repo',
                        'location' => 'repository',
                    ],
                ],
            ]);

        $pending = new PendingInstructions($client, 'session-abc');
        $result = $pending->getSources();

        expect($result)->toBeInstanceOf(InstructionsGetSourcesResult::class)
            ->and($result->sources)->toHaveCount(1)
            ->and($result->sources[0]->id)->toBe('src-1')
            ->and($result->sources[0]->label)->toBe('Repo Instructions');
    });
});
