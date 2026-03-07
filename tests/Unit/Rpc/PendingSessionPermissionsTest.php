<?php

declare(strict_types=1);

use Revolt\EventLoop;
use Revolution\Copilot\Contracts\Transport;
use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\JsonRpc\JsonRpcMessage;
use Revolution\Copilot\Rpc\PendingSessionPermissions;
use Revolution\Copilot\Types\Rpc\SessionPermissionsHandlePendingPermissionRequestParams;
use Revolution\Copilot\Types\Rpc\SessionPermissionsHandlePendingPermissionRequestResult;

/**
 * Build a started JsonRpcClient backed by a mock transport.
 * Captured outgoing messages are appended to $sentMessages.
 * Each request automatically receives a successful {"success":true} response.
 */
function makePermissionsClient(array &$sentMessages): JsonRpcClient
{
    $handler = null;

    $transport = Mockery::mock(Transport::class);
    $transport->shouldReceive('onReceive')->andReturnUsing(function ($callback) use (&$handler) {
        $handler = $callback;
    });
    $transport->shouldReceive('start')->once();
    $transport->shouldReceive('send')->andReturnUsing(function ($message) use (&$sentMessages, &$handler) {
        $sentMessages[] = $message;
        if (preg_match('/\{.*\}/s', $message, $matches)) {
            $request = json_decode($matches[0], true);
            if (isset($request['id'])) {
                $response = JsonRpcMessage::response($request['id'], ['success' => true]);
                EventLoop::queue(function () use (&$handler, $response) {
                    if ($handler !== null) {
                        ($handler)($response->toJson());
                    }
                });
            }
        }
    });

    $client = new JsonRpcClient($transport);
    $client->start();

    return $client;
}

/**
 * Extract and decode the JSON payload from a Content-Length framed message.
 */
function decodePermissionsJsonMessage(string $message): array
{
    preg_match('/\{.*\}/s', $message, $matches);

    return json_decode($matches[0], true);
}

describe('PendingSessionPermissions', function () {
    it('sends correct method name for handlePendingPermissionRequest', function () {
        $sentMessages = [];
        $client = makePermissionsClient($sentMessages);

        $pending = new PendingSessionPermissions($client, 'session-abc');
        $pending->handlePendingPermissionRequest(new SessionPermissionsHandlePendingPermissionRequestParams(
            requestId: 'req-1',
            result: ['kind' => 'approved'],
        ));

        expect($sentMessages)->toHaveCount(1)
            ->and($sentMessages[0])->toContain('"method":"session.permissions.handlePendingPermissionRequest"');
    });

    it('injects sessionId into request params', function () {
        $sentMessages = [];
        $client = makePermissionsClient($sentMessages);

        $pending = new PendingSessionPermissions($client, 'session-abc');
        $pending->handlePendingPermissionRequest(new SessionPermissionsHandlePendingPermissionRequestParams(
            requestId: 'req-1',
            result: ['kind' => 'approved'],
        ));

        $decoded = decodePermissionsJsonMessage($sentMessages[0]);
        expect($decoded['params']['sessionId'])->toBe('session-abc');
    });

    it('includes requestId and result in request params', function () {
        $sentMessages = [];
        $client = makePermissionsClient($sentMessages);

        $pending = new PendingSessionPermissions($client, 'session-abc');
        $pending->handlePendingPermissionRequest(new SessionPermissionsHandlePendingPermissionRequestParams(
            requestId: 'perm-req-42',
            result: ['kind' => 'denied-interactively-by-user'],
        ));

        $decoded = decodePermissionsJsonMessage($sentMessages[0]);
        expect($decoded['params']['requestId'])->toBe('perm-req-42')
            ->and($decoded['params']['result'])->toBe(['kind' => 'denied-interactively-by-user']);
    });

    it('maps the response to SessionPermissionsHandlePendingPermissionRequestResult', function () {
        $sentMessages = [];
        $client = makePermissionsClient($sentMessages);

        $pending = new PendingSessionPermissions($client, 'session-abc');
        $result = $pending->handlePendingPermissionRequest(new SessionPermissionsHandlePendingPermissionRequestParams(
            requestId: 'req-1',
            result: ['kind' => 'approved'],
        ));

        expect($result)->toBeInstanceOf(SessionPermissionsHandlePendingPermissionRequestResult::class)
            ->and($result->success)->toBeTrue();
    });

    it('accepts array params instead of class instance', function () {
        $sentMessages = [];
        $client = makePermissionsClient($sentMessages);

        $pending = new PendingSessionPermissions($client, 'session-xyz');
        $result = $pending->handlePendingPermissionRequest([
            'requestId' => 'req-array',
            'result' => ['kind' => 'approved'],
        ]);

        $decoded = decodePermissionsJsonMessage($sentMessages[0]);
        expect($result)->toBeInstanceOf(SessionPermissionsHandlePendingPermissionRequestResult::class)
            ->and($decoded['params']['sessionId'])->toBe('session-xyz')
            ->and($decoded['params']['requestId'])->toBe('req-array');
    });
});
