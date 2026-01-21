<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcMessage;

describe('JsonRpcMessage', function () {
    describe('requests', function () {
        it('can create a request message', function () {
            $message = JsonRpcMessage::request('test-id', 'test.method', ['param1' => 'value1']);

            expect($message->method)->toBe('test.method')
                ->and($message->params)->toBe(['param1' => 'value1'])
                ->and($message->id)->toBe('test-id')
                ->and($message->isRequest())->toBeTrue()
                ->and($message->isNotification())->toBeFalse()
                ->and($message->isResponse())->toBeFalse();
        });
    });

    describe('notifications', function () {
        it('can create a notification message', function () {
            $message = JsonRpcMessage::notification('test.event', ['data' => 'value']);

            expect($message->method)->toBe('test.event')
                ->and($message->params)->toBe(['data' => 'value'])
                ->and($message->id)->toBeNull()
                ->and($message->isNotification())->toBeTrue()
                ->and($message->isRequest())->toBeFalse();
        });
    });

    describe('responses', function () {
        it('can create a success response', function () {
            $message = JsonRpcMessage::response('request-id', ['result' => 'success']);

            expect($message->id)->toBe('request-id')
                ->and($message->result)->toBe(['result' => 'success'])
                ->and($message->error)->toBeNull()
                ->and($message->isResponse())->toBeTrue()
                ->and($message->isRequest())->toBeFalse();
        });

        it('can create an error response', function () {
            $message = JsonRpcMessage::errorResponse('request-id', -32600, 'Invalid Request');

            expect($message->id)->toBe('request-id')
                ->and($message->error)->toHaveKey('code', -32600)
                ->and($message->error)->toHaveKey('message', 'Invalid Request')
                ->and($message->result)->toBeNull()
                ->and($message->isResponse())->toBeTrue()
                ->and($message->isError())->toBeTrue();
        });

        it('can create an error response with data', function () {
            $message = JsonRpcMessage::errorResponse('request-id', -32600, 'Invalid Request', ['details' => 'more info']);

            expect($message->error['data'])->toBe(['details' => 'more info']);
        });
    });

    describe('parsing', function () {
        it('can parse from array', function () {
            $data = [
                'jsonrpc' => '2.0',
                'id' => 'test-id',
                'result' => ['success' => true],
            ];
            $message = JsonRpcMessage::fromArray($data);

            expect($message->id)->toBe('test-id')
                ->and($message->result)->toBe(['success' => true]);
        });

        it('can parse request from array', function () {
            $data = [
                'jsonrpc' => '2.0',
                'id' => 'test-id',
                'method' => 'ping',
                'params' => ['message' => 'hello'],
            ];
            $message = JsonRpcMessage::fromArray($data);

            expect($message->id)->toBe('test-id')
                ->and($message->method)->toBe('ping')
                ->and($message->params)->toBe(['message' => 'hello']);
        });
    });

    describe('serialization', function () {
        it('can convert to array', function () {
            $message = JsonRpcMessage::request('req-1', 'test.method', ['key' => 'value']);
            $array = $message->toArray();

            expect($array)->toHaveKey('jsonrpc', '2.0')
                ->and($array)->toHaveKey('method', 'test.method')
                ->and($array)->toHaveKey('params')
                ->and($array)->toHaveKey('id', 'req-1');
        });

        it('can convert to JSON string', function () {
            $message = JsonRpcMessage::request('req-1', 'test.method', ['key' => 'value']);
            $json = $message->toJson();
            $decoded = json_decode($json, true);

            expect($decoded['jsonrpc'])->toBe('2.0')
                ->and($decoded['method'])->toBe('test.method')
                ->and($decoded['params'])->toBe(['key' => 'value']);
        });

        it('excludes null values from array', function () {
            $message = JsonRpcMessage::notification('test.method', []);
            $array = $message->toArray();

            expect($array)->not->toHaveKey('id')
                ->and($array)->not->toHaveKey('result')
                ->and($array)->not->toHaveKey('error');
        });
    });

    describe('content-length encoding', function () {
        it('can encode with content-length header', function () {
            $message = JsonRpcMessage::request('req-1', 'ping', []);
            $encoded = $message->encode();

            expect($encoded)->toStartWith('Content-Length: ')
                ->and($encoded)->toContain("\r\n\r\n")
                ->and($encoded)->toContain('"jsonrpc":"2.0"');
        });

        it('calculates correct content length', function () {
            $message = JsonRpcMessage::notification('test', []);
            $encoded = $message->encode();

            preg_match('/Content-Length: (\d+)/', $encoded, $matches);
            $length = (int) $matches[1];

            $body = substr($encoded, strpos($encoded, "\r\n\r\n") + 4);

            expect(strlen($body))->toBe($length);
        });
    });
});
