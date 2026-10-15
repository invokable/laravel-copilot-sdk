<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingCompletions;
use Revolution\Copilot\Types\Rpc\CompletionsGetTriggerCharactersResult;
use Revolution\Copilot\Types\Rpc\CompletionsRequestRequest;
use Revolution\Copilot\Types\Rpc\CompletionsRequestResult;
use Revolution\Copilot\Types\Rpc\SessionCompletionItem;

describe('PendingCompletions', function () {
    it('gets trigger characters for the session', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.completions.getTriggerCharacters', ['sessionId' => 'session-abc'])
            ->andReturn(['triggerCharacters' => ['@', '#']]);

        $result = (new PendingCompletions($client, 'session-abc'))->getTriggerCharacters();

        expect($result)->toBeInstanceOf(CompletionsGetTriggerCharactersResult::class)
            ->and($result->triggerCharacters)->toBe(['@', '#']);
    });

    it('requests completions with typed params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.completions.request', [
                'text' => '@lar',
                'offset' => 4,
                'sessionId' => 'session-abc',
            ])
            ->andReturn(['items' => [[
                'insertText' => '@laravel/framework',
                'rangeStart' => 0,
                'rangeEnd' => 4,
                'label' => 'Laravel Framework',
                'kind' => 'package',
            ]]]);

        $result = (new PendingCompletions($client, 'session-abc'))->request(
            new CompletionsRequestRequest(text: '@lar', offset: 4),
        );

        expect($result)->toBeInstanceOf(CompletionsRequestResult::class)
            ->and($result->items)->toHaveCount(1)
            ->and($result->items[0])->toBeInstanceOf(SessionCompletionItem::class)
            ->and($result->items[0]->insertText)->toBe('@laravel/framework')
            ->and($result->items[0]->label)->toBe('Laravel Framework');
    });

    it('requests completions with array params and handles no items', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.completions.request', [
                'text' => 'plain text',
                'offset' => 5,
                'sessionId' => 'session-abc',
            ])
            ->andReturn(['items' => []]);

        $result = (new PendingCompletions($client, 'session-abc'))->request([
            'text' => 'plain text',
            'offset' => 5,
        ]);

        expect($result)->toBeInstanceOf(CompletionsRequestResult::class)
            ->and($result->items)->toBe([]);
    });
});
