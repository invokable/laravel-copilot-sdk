<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\FetchResponseMode;
use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\ServerRpc;
use Revolution\Copilot\Types\Rpc\FetchCancelResult;
use Revolution\Copilot\Types\Rpc\FetchCreateResult;
use Revolution\Copilot\Types\Rpc\FetchHeader;
use Revolution\Copilot\Types\Rpc\FetchReadResult;
use Revolution\Copilot\Types\Rpc\FetchSendResult;

describe('PendingServerFetch', function () {
    it('dispatches connection-owned fetch operations and converts each response mode', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('fetch.create', [])
            ->andReturn(['requestId' => 'request-1']);
        $client->shouldReceive('request')
            ->once()
            ->with('fetch.send', [
                'requestId' => 'request-1',
                'url' => 'https://example.com/stream',
                'responseMode' => 'stream',
                'method' => 'GET',
                'headers' => [['name' => 'accept', 'value' => 'application/json']],
                'redirect' => 'sameOrigin',
                'timeoutMs' => 0,
                'readTimeoutMs' => 1000,
                'bodyLimitBytes' => 0,
                'disableConnectionReuse' => false,
                'proxyEnv' => ['httpsProxy' => 'http://proxy.example'],
                'pinnedAddresses' => ['192.0.2.1'],
                'allowLocalhost' => false,
                'proxyNegotiateAuth' => true,
                'proxyNegotiateSpn' => 'HTTP/proxy.example',
            ])
            ->andReturn([
                'responseMode' => 'stream',
                'url' => 'https://example.com/stream',
                'status' => 200,
                'statusText' => 'OK',
                'headers' => [['name' => 'content-type', 'value' => 'text/plain']],
            ]);
        $client->shouldReceive('request')
            ->once()
            ->with('fetch.send', [
                'requestId' => 'request-1',
                'url' => 'https://example.com/text',
                'responseMode' => 'text',
            ])
            ->andReturn([
                'responseMode' => 'text',
                'url' => 'https://example.com/text',
                'status' => 404,
                'statusText' => 'Not Found',
                'headers' => [],
                'bodyText' => 'missing',
                'endedAtUnverifiedEof' => false,
            ]);
        $client->shouldReceive('request')
            ->once()
            ->with('fetch.send', [
                'requestId' => 'request-1',
                'url' => 'https://example.com/file',
                'responseMode' => 'file',
                'destinationPath' => '/tmp/response.bin',
            ])
            ->andReturn([
                'responseMode' => 'file',
                'url' => 'https://example.com/file',
                'status' => 200,
                'statusText' => 'OK',
                'headers' => [],
                'path' => '/tmp/response.bin',
                'sizeBytes' => 64,
                'endedAtUnverifiedEof' => true,
            ]);
        $client->shouldReceive('request')
            ->once()
            ->with('fetch.read', ['requestId' => 'request-1'])
            ->andReturn(['done' => false, 'body' => 'Y2h1bms=']);
        $client->shouldReceive('request')
            ->once()
            ->with('fetch.cancel', ['requestId' => 'request-1'])
            ->andReturn(['canceled' => true]);

        $fetch = (new ServerRpc($client))->fetch();
        $created = $fetch->create();
        $stream = $fetch->send([
            'requestId' => $created->requestId,
            'url' => 'https://example.com/stream',
            'responseMode' => 'stream',
            'method' => 'GET',
            'headers' => [['name' => 'accept', 'value' => 'application/json']],
            'redirect' => 'sameOrigin',
            'timeoutMs' => 0,
            'readTimeoutMs' => 1000,
            'bodyLimitBytes' => 0,
            'disableConnectionReuse' => false,
            'proxyEnv' => ['httpsProxy' => 'http://proxy.example'],
            'pinnedAddresses' => ['192.0.2.1'],
            'allowLocalhost' => false,
            'proxyNegotiateAuth' => true,
            'proxyNegotiateSpn' => 'HTTP/proxy.example',
        ]);
        $text = $fetch->send([
            'requestId' => $created->requestId,
            'url' => 'https://example.com/text',
            'responseMode' => 'text',
        ]);
        $file = $fetch->send([
            'requestId' => $created->requestId,
            'url' => 'https://example.com/file',
            'responseMode' => 'file',
            'destinationPath' => '/tmp/response.bin',
        ]);
        $read = $fetch->read(['requestId' => $created->requestId]);
        $canceled = $fetch->cancel(['requestId' => $created->requestId]);

        expect($created)->toBeInstanceOf(FetchCreateResult::class)
            ->and($created->requestId)->toBe('request-1')
            ->and($stream)->toBeInstanceOf(FetchSendResult::class)
            ->and($stream->responseMode)->toBe(FetchResponseMode::Stream)
            ->and($stream->headers[0])->toBeInstanceOf(FetchHeader::class)
            ->and($text->responseMode)->toBe(FetchResponseMode::Text)
            ->and($text->bodyText)->toBe('missing')
            ->and($file->responseMode)->toBe(FetchResponseMode::File)
            ->and($file->path)->toBe('/tmp/response.bin')
            ->and($file->sizeBytes)->toBe(64)
            ->and($read)->toBeInstanceOf(FetchReadResult::class)
            ->and($read->body)->toBe('Y2h1bms=')
            ->and($canceled)->toBeInstanceOf(FetchCancelResult::class)
            ->and($canceled->canceled)->toBeTrue();
    });
});
