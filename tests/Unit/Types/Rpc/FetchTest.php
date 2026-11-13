<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\FetchFileErrorKind;
use Revolution\Copilot\Enums\FetchFileOperation;
use Revolution\Copilot\Enums\FetchRedirect;
use Revolution\Copilot\Enums\FetchResponseMode;
use Revolution\Copilot\Enums\FetchTransportErrorKind;
use Revolution\Copilot\Types\Rpc\FetchFileErrorData;
use Revolution\Copilot\Types\Rpc\FetchHeader;
use Revolution\Copilot\Types\Rpc\FetchProxyEnv;
use Revolution\Copilot\Types\Rpc\FetchSendParams;
use Revolution\Copilot\Types\Rpc\FetchSendResult;
use Revolution\Copilot\Types\Rpc\FetchTransportErrorData;

describe('FetchSendParams', function () {
    it('round-trips optional transport settings and nested types', function () {
        $params = new FetchSendParams(
            requestId: 'request-1',
            url: 'https://example.com',
            responseMode: FetchResponseMode::File,
            destinationPath: '/tmp/response.bin',
            credentialContextId: 'credential-1',
            method: 'POST',
            headers: [new FetchHeader('content-type', 'application/json')],
            body: 'eyJvayI6dHJ1ZX0=',
            redirect: FetchRedirect::SameOrigin,
            timeoutMs: 0,
            readTimeoutMs: 150,
            bodyLimitBytes: 2048,
            disableConnectionReuse: false,
            proxyEnv: new FetchProxyEnv(httpsProxy: 'http://proxy.example'),
            pinnedAddresses: ['192.0.2.1'],
            allowLocalhost: false,
            proxyNegotiateAuth: true,
            proxyNegotiateSpn: 'HTTP/proxy.example',
        );

        $decoded = FetchSendParams::fromArray($params->toArray());

        expect($decoded->toArray())->toBe($params->toArray())
            ->and($decoded->responseMode)->toBe(FetchResponseMode::File)
            ->and($decoded->redirect)->toBe(FetchRedirect::SameOrigin)
            ->and($decoded->headers[0])->toBeInstanceOf(FetchHeader::class)
            ->and($decoded->proxyEnv)->toBeInstanceOf(FetchProxyEnv::class);
    });

    it('omits unset optional transport settings', function () {
        $params = FetchSendParams::fromArray([
            'requestId' => 'request-2',
            'url' => 'https://example.com',
            'responseMode' => 'stream',
        ]);

        expect($params->toArray())->toBe([
            'requestId' => 'request-2',
            'url' => 'https://example.com',
            'responseMode' => 'stream',
        ]);
    });
});

describe('FetchSendResult', function () {
    it('round-trips stream, text, and file response representations', function () {
        $stream = FetchSendResult::fromArray([
            'responseMode' => 'stream',
            'url' => 'https://example.com/stream',
            'status' => 200,
            'statusText' => 'OK',
            'headers' => [['name' => 'content-type', 'value' => 'text/plain']],
        ]);
        $text = FetchSendResult::fromArray([
            'responseMode' => 'text',
            'url' => 'https://example.com/text',
            'status' => 404,
            'statusText' => 'Not Found',
            'headers' => [],
            'bodyText' => 'missing',
            'endedAtUnverifiedEof' => false,
        ]);
        $file = FetchSendResult::fromArray([
            'responseMode' => 'file',
            'url' => 'https://example.com/file',
            'status' => 200,
            'statusText' => 'OK',
            'headers' => [],
            'path' => '/tmp/response.bin',
            'sizeBytes' => 64,
            'endedAtUnverifiedEof' => true,
        ]);

        expect($stream->responseMode)->toBe(FetchResponseMode::Stream)
            ->and($stream->headers[0])->toBeInstanceOf(FetchHeader::class)
            ->and($stream->toArray())->toBe([
                'responseMode' => 'stream',
                'url' => 'https://example.com/stream',
                'status' => 200,
                'statusText' => 'OK',
                'headers' => [['name' => 'content-type', 'value' => 'text/plain']],
            ]);
        expect($text->toArray())->toBe([
            'responseMode' => 'text',
            'url' => 'https://example.com/text',
            'status' => 404,
            'statusText' => 'Not Found',
            'headers' => [],
            'bodyText' => 'missing',
            'endedAtUnverifiedEof' => false,
        ]);
        expect($file->toArray())->toBe([
            'responseMode' => 'file',
            'url' => 'https://example.com/file',
            'status' => 200,
            'statusText' => 'OK',
            'headers' => [],
            'path' => '/tmp/response.bin',
            'sizeBytes' => 64,
            'endedAtUnverifiedEof' => true,
        ]);
    });
});

describe('Fetch error data', function () {
    it('round-trips transport and filesystem error classifications', function () {
        $transport = FetchTransportErrorData::fromArray([
            'kind' => 'response_body_limit',
            'code' => 'ERR_BODY_LIMIT',
            'permanentRequestFailure' => true,
            'networkError' => false,
        ]);
        $file = FetchFileErrorData::fromArray([
            'kind' => 'fileSystem',
            'operation' => 'create',
            'osErrorCode' => 17,
        ]);

        expect($transport->kind)->toBe(FetchTransportErrorKind::ResponseBodyLimit)
            ->and($transport->toArray())->toBe([
                'kind' => 'response_body_limit',
                'code' => 'ERR_BODY_LIMIT',
                'permanentRequestFailure' => true,
                'networkError' => false,
            ]);
        expect($file->kind)->toBe(FetchFileErrorKind::FileSystem)
            ->and($file->operation)->toBe(FetchFileOperation::Create)
            ->and($file->toArray())->toBe([
                'kind' => 'fileSystem',
                'operation' => 'create',
                'osErrorCode' => 17,
            ]);
    });
});
