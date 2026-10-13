<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Session;
use Revolution\Copilot\Types\Rpc\OpenCanvasInstance;
use Revolution\Copilot\Types\SessionEvent;

it('tracks opened and closed canvas events without exposing mutable state', function () {
    $session = new Session('canvas-events', Mockery::mock(JsonRpcClient::class));

    $session->dispatchEvent(SessionEvent::fromArray([
        'type' => 'session.canvas.opened',
        'data' => [
            'canvasId' => 'editor',
            'extensionId' => 'example:editor',
            'instanceId' => 'instance-1',
            'title' => 'README.md',
        ],
    ]));
    $opened = $session->openCanvases();

    $session->dispatchEvent(SessionEvent::fromArray([
        'type' => 'session.canvas.opened',
        'data' => [
            'canvasId' => 'editor',
            'extensionId' => 'example:editor',
            'instanceId' => 'instance-1',
            'title' => 'Updated title',
        ],
    ]));
    $session->dispatchEvent(SessionEvent::fromArray([
        'type' => 'session.canvas.opened',
        'data' => [
            'canvasId' => 'editor',
            'extensionId' => 'example:editor',
            'instanceId' => 'instance-2',
        ],
    ]));

    expect($opened)->toHaveCount(1)
        ->and($opened[0])->toBeInstanceOf(OpenCanvasInstance::class)
        ->and($session->openCanvases())->toHaveCount(2)
        ->and($session->openCanvases()[0]->title)->toBe('Updated title');

    $opened = $session->openCanvases();
    $opened[] = new OpenCanvasInstance(
        canvasId: 'external',
        extensionId: 'example:external',
        instanceId: 'instance-external',
    );

    expect($session->openCanvases())->toHaveCount(2);

    $session->dispatchEvent(SessionEvent::fromArray([
        'type' => 'session.canvas.closed',
        'data' => ['instanceId' => 'instance-1'],
    ]));

    expect($session->openCanvases())->toHaveCount(1)
        ->and($session->openCanvases()[0]->instanceId)->toBe('instance-2');
});
