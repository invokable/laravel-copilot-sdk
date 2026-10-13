<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingCanvas;
use Revolution\Copilot\Rpc\PendingCanvasAction;
use Revolution\Copilot\Rpc\SessionRpc;
use Revolution\Copilot\Types\Rpc\CanvasActionInvokeResult;
use Revolution\Copilot\Types\Rpc\CanvasList;
use Revolution\Copilot\Types\Rpc\CanvasListOpenResult;
use Revolution\Copilot\Types\Rpc\OpenCanvasInstance;

describe('PendingCanvas', function () {
    it('sends session canvas requests and maps their results', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.canvas.list', ['sessionId' => 'session-canvas'])
            ->andReturn(['canvases' => [[
                'canvasId' => 'editor',
                'description' => 'A code editor',
                'displayName' => 'Editor',
                'extensionId' => 'example',
            ]]]);
        $client->shouldReceive('request')
            ->once()
            ->with('session.canvas.listOpen', ['sessionId' => 'session-canvas'])
            ->andReturn(['openCanvases' => [[
                'canvasId' => 'editor',
                'extensionId' => 'example',
                'instanceId' => 'instance-1',
            ]]]);
        $client->shouldReceive('request')
            ->once()
            ->with('session.canvas.open', [
                'canvasId' => 'editor',
                'instanceId' => 'instance-2',
                'input' => ['file' => 'README.md'],
                'sessionId' => 'session-canvas',
            ])
            ->andReturn([
                'canvasId' => 'editor',
                'extensionId' => 'example',
                'instanceId' => 'instance-2',
            ]);
        $client->shouldReceive('request')
            ->once()
            ->with('session.canvas.close', [
                'instanceId' => 'instance-2',
                'sessionId' => 'session-canvas',
            ])
            ->andReturn([]);
        $client->shouldReceive('request')
            ->once()
            ->with('session.canvas.action.invoke', [
                'instanceId' => 'instance-2',
                'actionName' => 'format',
                'input' => ['style' => 'compact'],
                'sessionId' => 'session-canvas',
            ])
            ->andReturn(['result' => ['formatted' => true]]);

        $canvas = (new SessionRpc($client, 'session-canvas'))->canvas();
        $listed = $canvas->list();
        $open = $canvas->listOpen();
        $opened = $canvas->open([
            'canvasId' => 'editor',
            'instanceId' => 'instance-2',
            'input' => ['file' => 'README.md'],
        ]);
        $canvas->close(['instanceId' => 'instance-2']);
        $action = $canvas->action()->invoke([
            'instanceId' => 'instance-2',
            'actionName' => 'format',
            'input' => ['style' => 'compact'],
        ]);

        expect($canvas)->toBeInstanceOf(PendingCanvas::class)
            ->and($listed)->toBeInstanceOf(CanvasList::class)
            ->and($listed->canvases[0]->canvasId)->toBe('editor')
            ->and($open)->toBeInstanceOf(CanvasListOpenResult::class)
            ->and($open->openCanvases[0])->toBeInstanceOf(OpenCanvasInstance::class)
            ->and($opened->instanceId)->toBe('instance-2')
            ->and($canvas->action())->toBeInstanceOf(PendingCanvasAction::class)
            ->and($action)->toBeInstanceOf(CanvasActionInvokeResult::class)
            ->and($action->toArray())->toBe(['result' => ['formatted' => true]]);
    });
});
