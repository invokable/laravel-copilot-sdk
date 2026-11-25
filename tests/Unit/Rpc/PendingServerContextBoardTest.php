<?php

declare(strict_types=1);

use Revolution\Copilot\Exceptions\JsonRpcException;
use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\ServerRpc;
use Revolution\Copilot\Types\Rpc\ContextBoardGetConsolidationPromptResult;

describe('Server context board RPC', function () {
    it('returns the runtime consolidation prompt without parameters', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')->once()
            ->with('contextBoard.getConsolidationPrompt', [])
            ->andReturn(['prompt' => "Consolidate the context.\nKeep important details."]);

        $result = (new ServerRpc($client))->contextBoard()->getConsolidationPrompt();

        expect($result)->toBeInstanceOf(ContextBoardGetConsolidationPromptResult::class)
            ->and($result->prompt)->toBe("Consolidate the context.\nKeep important details.");
    });

    it('propagates JSON-RPC errors', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $exception = new JsonRpcException(-32000, 'Runtime error');
        $client->shouldReceive('request')->once()
            ->with('contextBoard.getConsolidationPrompt', [])
            ->andThrow($exception);

        try {
            (new ServerRpc($client))->contextBoard()->getConsolidationPrompt();
            test()->fail('Expected the JSON-RPC error to propagate.');
        } catch (JsonRpcException $caught) {
            expect($caught)->toBe($exception);
        }
    });
});
