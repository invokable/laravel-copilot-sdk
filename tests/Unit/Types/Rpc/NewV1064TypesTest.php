<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\LlmInferenceHttpRequestStartTransport;
use Revolution\Copilot\Enums\McpOauthPendingRequestResponseKind;
use Revolution\Copilot\Types\Rpc\LlmInferenceHTTPRequestChunkRequest;
use Revolution\Copilot\Types\Rpc\LlmInferenceHTTPRequestChunkResult;
use Revolution\Copilot\Types\Rpc\LlmInferenceHTTPRequestStartRequest;
use Revolution\Copilot\Types\Rpc\LlmInferenceHTTPRequestStartResult;
use Revolution\Copilot\Types\Rpc\LlmInferenceHTTPResponseChunkError;
use Revolution\Copilot\Types\Rpc\LlmInferenceHTTPResponseChunkRequest;
use Revolution\Copilot\Types\Rpc\LlmInferenceHTTPResponseChunkResult;
use Revolution\Copilot\Types\Rpc\LlmInferenceHTTPResponseStartRequest;
use Revolution\Copilot\Types\Rpc\LlmInferenceHTTPResponseStartResult;
use Revolution\Copilot\Types\Rpc\LlmInferenceSetProviderResult;
use Revolution\Copilot\Types\Rpc\McpOauthHandlePendingRequest;
use Revolution\Copilot\Types\Rpc\McpOauthHandlePendingResult;
use Revolution\Copilot\Types\Rpc\McpOauthPendingRequestResponse;

describe('LlmInferenceHTTPRequestStartRequest', function () {
    it('can be created with required fields', function () {
        $request = new LlmInferenceHTTPRequestStartRequest(
            headers: ['Content-Type' => ['application/json']],
            method: 'POST',
            requestId: 'req-123',
            url: 'https://api.example.com/chat',
        );

        expect($request->headers)->toBe(['Content-Type' => ['application/json']])
            ->and($request->method)->toBe('POST')
            ->and($request->requestId)->toBe('req-123')
            ->and($request->url)->toBe('https://api.example.com/chat')
            ->and($request->sessionId)->toBeNull()
            ->and($request->transport)->toBeNull();
    });

    it('can be created from array', function () {
        $request = LlmInferenceHTTPRequestStartRequest::fromArray([
            'headers' => ['Authorization' => ['Bearer token']],
            'method' => 'POST',
            'requestId' => 'req-456',
            'url' => 'https://api.example.com/v1/messages',
            'sessionId' => 'sess-abc',
            'transport' => 'websocket',
        ]);

        expect($request->method)->toBe('POST')
            ->and($request->sessionId)->toBe('sess-abc')
            ->and($request->transport)->toBe(LlmInferenceHttpRequestStartTransport::Websocket);
    });

    it('converts to array excluding nulls', function () {
        $request = new LlmInferenceHTTPRequestStartRequest(
            headers: ['Content-Type' => ['application/json']],
            method: 'GET',
            requestId: 'req-789',
            url: 'https://api.example.com/',
        );

        $array = $request->toArray();

        expect($array)->toBe([
            'headers' => ['Content-Type' => ['application/json']],
            'method' => 'GET',
            'requestId' => 'req-789',
            'url' => 'https://api.example.com/',
        ])
            ->and($array)->not->toHaveKey('sessionId')
            ->and($array)->not->toHaveKey('transport');
    });

    it('converts transport enum to string in toArray', function () {
        $request = new LlmInferenceHTTPRequestStartRequest(
            headers: [],
            method: 'POST',
            requestId: 'req-1',
            url: 'wss://api.example.com/',
            transport: LlmInferenceHttpRequestStartTransport::Websocket,
        );

        expect($request->toArray()['transport'])->toBe('websocket');
    });
});

describe('LlmInferenceHTTPRequestStartResult', function () {
    it('can be created from empty array', function () {
        $result = LlmInferenceHTTPRequestStartResult::fromArray([]);

        expect($result)->toBeInstanceOf(LlmInferenceHTTPRequestStartResult::class);
    });

    it('converts to empty array', function () {
        expect((new LlmInferenceHTTPRequestStartResult)->toArray())->toBe([]);
    });
});

describe('LlmInferenceHTTPRequestChunkRequest', function () {
    it('can be created with required fields', function () {
        $request = new LlmInferenceHTTPRequestChunkRequest(
            data: 'chunk data',
            requestId: 'req-123',
        );

        expect($request->data)->toBe('chunk data')
            ->and($request->requestId)->toBe('req-123')
            ->and($request->binary)->toBeNull()
            ->and($request->cancel)->toBeNull()
            ->and($request->end)->toBeNull();
    });

    it('can be created from array with all fields', function () {
        $request = LlmInferenceHTTPRequestChunkRequest::fromArray([
            'data' => 'binary-encoded',
            'requestId' => 'req-456',
            'binary' => true,
            'cancel' => false,
            'cancelReason' => 'user aborted',
            'end' => true,
        ]);

        expect($request->data)->toBe('binary-encoded')
            ->and($request->binary)->toBeTrue()
            ->and($request->end)->toBeTrue()
            ->and($request->cancelReason)->toBe('user aborted');
    });

    it('converts to array excluding nulls', function () {
        $request = new LlmInferenceHTTPRequestChunkRequest(data: 'data', requestId: 'req-1', end: true);

        expect($request->toArray())->toBe([
            'data' => 'data',
            'requestId' => 'req-1',
            'end' => true,
        ]);
    });
});

describe('LlmInferenceHTTPRequestChunkResult', function () {
    it('can be created from empty array', function () {
        expect(LlmInferenceHTTPRequestChunkResult::fromArray([]))->toBeInstanceOf(LlmInferenceHTTPRequestChunkResult::class);
    });

    it('converts to empty array', function () {
        expect((new LlmInferenceHTTPRequestChunkResult)->toArray())->toBe([]);
    });
});

describe('LlmInferenceHTTPResponseChunkError', function () {
    it('can be created with message only', function () {
        $error = new LlmInferenceHTTPResponseChunkError(message: 'Connection reset');

        expect($error->message)->toBe('Connection reset')
            ->and($error->code)->toBeNull();
    });

    it('can be created from array', function () {
        $error = LlmInferenceHTTPResponseChunkError::fromArray([
            'message' => 'Timeout',
            'code' => 'ETIMEDOUT',
        ]);

        expect($error->message)->toBe('Timeout')
            ->and($error->code)->toBe('ETIMEDOUT');
    });

    it('converts to array excluding nulls', function () {
        expect((new LlmInferenceHTTPResponseChunkError(message: 'err'))->toArray())->toBe(['message' => 'err']);
    });
});

describe('LlmInferenceHTTPResponseChunkRequest', function () {
    it('can be created with required fields', function () {
        $request = new LlmInferenceHTTPResponseChunkRequest(data: 'chunk', requestId: 'req-1');

        expect($request->data)->toBe('chunk')
            ->and($request->requestId)->toBe('req-1')
            ->and($request->end)->toBeNull()
            ->and($request->error)->toBeNull();
    });

    it('can be created from array with error', function () {
        $request = LlmInferenceHTTPResponseChunkRequest::fromArray([
            'data' => '',
            'requestId' => 'req-2',
            'end' => true,
            'error' => ['message' => 'fatal error', 'code' => 'E001'],
        ]);

        expect($request->end)->toBeTrue()
            ->and($request->error)->toBeInstanceOf(LlmInferenceHTTPResponseChunkError::class)
            ->and($request->error->message)->toBe('fatal error');
    });

    it('converts error to array', function () {
        $request = new LlmInferenceHTTPResponseChunkRequest(
            data: '',
            requestId: 'req-3',
            error: new LlmInferenceHTTPResponseChunkError(message: 'err'),
        );

        expect($request->toArray()['error'])->toBe(['message' => 'err']);
    });
});

describe('LlmInferenceHTTPResponseChunkResult', function () {
    it('can be created from array', function () {
        $result = LlmInferenceHTTPResponseChunkResult::fromArray(['accepted' => true]);

        expect($result->accepted)->toBeTrue();
    });

    it('converts to array', function () {
        expect((new LlmInferenceHTTPResponseChunkResult(accepted: false))->toArray())->toBe(['accepted' => false]);
    });
});

describe('LlmInferenceHTTPResponseStartRequest', function () {
    it('can be created with required fields', function () {
        $request = new LlmInferenceHTTPResponseStartRequest(
            headers: ['Content-Type' => ['application/json']],
            requestId: 'req-1',
            status: 200,
        );

        expect($request->status)->toBe(200)
            ->and($request->statusText)->toBeNull();
    });

    it('can be created from array', function () {
        $request = LlmInferenceHTTPResponseStartRequest::fromArray([
            'headers' => ['Content-Type' => ['text/event-stream']],
            'requestId' => 'req-2',
            'status' => 200,
            'statusText' => 'OK',
        ]);

        expect($request->status)->toBe(200)
            ->and($request->statusText)->toBe('OK');
    });

    it('converts to array excluding nulls', function () {
        $request = new LlmInferenceHTTPResponseStartRequest(
            headers: [],
            requestId: 'req-3',
            status: 404,
        );

        $array = $request->toArray();
        expect($array['status'])->toBe(404)
            ->and($array)->not->toHaveKey('statusText');
    });
});

describe('LlmInferenceHTTPResponseStartResult', function () {
    it('can be created from array', function () {
        $result = LlmInferenceHTTPResponseStartResult::fromArray(['accepted' => true]);

        expect($result->accepted)->toBeTrue();
    });

    it('converts to array', function () {
        expect((new LlmInferenceHTTPResponseStartResult(accepted: true))->toArray())->toBe(['accepted' => true]);
    });
});

describe('LlmInferenceSetProviderResult', function () {
    it('can be created from array', function () {
        $result = LlmInferenceSetProviderResult::fromArray(['success' => true]);

        expect($result->success)->toBeTrue();
    });

    it('converts to array', function () {
        expect((new LlmInferenceSetProviderResult(success: false))->toArray())->toBe(['success' => false]);
    });
});

describe('McpOauthPendingRequestResponse', function () {
    it('can be created with token kind', function () {
        $response = new McpOauthPendingRequestResponse(
            kind: McpOauthPendingRequestResponseKind::Token,
            accessToken: 'tok_abc',
        );

        expect($response->kind)->toBe(McpOauthPendingRequestResponseKind::Token)
            ->and($response->accessToken)->toBe('tok_abc');
    });

    it('can be created from array', function () {
        $response = McpOauthPendingRequestResponse::fromArray([
            'kind' => 'token',
            'accessToken' => 'tok_xyz',
            'expiresIn' => 3600,
            'refreshToken' => 'ref_abc',
            'tokenType' => 'Bearer',
        ]);

        expect($response->kind)->toBe(McpOauthPendingRequestResponseKind::Token)
            ->and($response->accessToken)->toBe('tok_xyz')
            ->and($response->expiresIn)->toBe(3600);
    });

    it('can represent cancellation', function () {
        $response = McpOauthPendingRequestResponse::fromArray(['kind' => 'cancelled']);

        expect($response->kind)->toBe(McpOauthPendingRequestResponseKind::Cancelled)
            ->and($response->accessToken)->toBeNull();
    });

    it('converts to array excluding nulls', function () {
        $response = new McpOauthPendingRequestResponse(kind: McpOauthPendingRequestResponseKind::Cancelled);

        expect($response->toArray())->toBe(['kind' => 'cancelled']);
    });
});

describe('McpOauthHandlePendingRequest', function () {
    it('can be created with typed result', function () {
        $result = new McpOauthPendingRequestResponse(kind: McpOauthPendingRequestResponseKind::Token, accessToken: 'tok');
        $request = new McpOauthHandlePendingRequest(requestId: 'oauth-req-1', result: $result);

        expect($request->requestId)->toBe('oauth-req-1')
            ->and($request->result)->toBeInstanceOf(McpOauthPendingRequestResponse::class);
    });

    it('can be created from array', function () {
        $request = McpOauthHandlePendingRequest::fromArray([
            'requestId' => 'oauth-req-2',
            'result' => ['kind' => 'cancelled'],
        ]);

        expect($request->requestId)->toBe('oauth-req-2')
            ->and($request->result)->toBeInstanceOf(McpOauthPendingRequestResponse::class)
            ->and($request->result->kind)->toBe(McpOauthPendingRequestResponseKind::Cancelled);
    });

    it('converts to array', function () {
        $request = new McpOauthHandlePendingRequest(
            requestId: 'oauth-req-3',
            result: new McpOauthPendingRequestResponse(kind: McpOauthPendingRequestResponseKind::Token, accessToken: 'tok'),
        );

        $array = $request->toArray();
        expect($array['requestId'])->toBe('oauth-req-3')
            ->and($array['result']['kind'])->toBe('token')
            ->and($array['result']['accessToken'])->toBe('tok');
    });
});

describe('McpOauthHandlePendingResult', function () {
    it('can be created from array', function () {
        $result = McpOauthHandlePendingResult::fromArray(['success' => true]);

        expect($result->success)->toBeTrue();
    });

    it('converts to array', function () {
        expect((new McpOauthHandlePendingResult(success: false))->toArray())->toBe(['success' => false]);
    });
});
