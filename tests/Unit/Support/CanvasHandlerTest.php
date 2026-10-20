<?php

declare(strict_types=1);

use Revolution\Copilot\Support\CanvasError;
use Revolution\Copilot\Support\CanvasHandler;
use Revolution\Copilot\Types\Rpc\CanvasProviderCloseRequest;
use Revolution\Copilot\Types\Rpc\CanvasProviderInvokeActionRequest;
use Revolution\Copilot\Types\Rpc\CanvasProviderOpenRequest;
use Revolution\Copilot\Types\Rpc\CanvasProviderOpenResult;

describe('CanvasHandler', function () {
    $makeHandler = static fn (): CanvasHandler => new class extends CanvasHandler
    {
        public function open(CanvasProviderOpenRequest $request): CanvasProviderOpenResult|array
        {
            return [];
        }
    };

    it('allows close to be omitted by providers', function () use ($makeHandler) {
        $handler = $makeHandler();
        $request = new CanvasProviderCloseRequest(
            sessionId: 'session-1',
            extensionId: 'example:editor',
            canvasId: 'editor',
            instanceId: 'instance-1',
        );

        expect(fn () => $handler->close($request))->not->toThrow(Throwable::class);
    });

    it('throws a structured no-handler error for unimplemented actions', function () use ($makeHandler) {
        $handler = $makeHandler();
        $request = new CanvasProviderInvokeActionRequest(
            sessionId: 'session-1',
            extensionId: 'example:editor',
            canvasId: 'editor',
            instanceId: 'instance-1',
            actionName: 'format',
        );

        try {
            $handler->invoke($request);
        } catch (CanvasError $exception) {
            expect($exception->errorCode)->toBe('canvas_action_no_handler')
                ->and($exception->getMessage())->toBe('No handler implemented for this canvas action')
                ->and($exception->toEnvelope())->toBe([
                    'code' => 'canvas_action_no_handler',
                    'message' => 'No handler implemented for this canvas action',
                ]);

            return;
        }

        test()->fail('Expected the default invoke method to throw a CanvasError.');
    });
});
