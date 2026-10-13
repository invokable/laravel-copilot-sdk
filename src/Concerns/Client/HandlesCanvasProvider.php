<?php

declare(strict_types=1);

namespace Revolution\Copilot\Concerns\Client;

use Revolution\Copilot\Exceptions\JsonRpcException;
use Revolution\Copilot\Session;
use Revolution\Copilot\Support\CanvasError;
use Revolution\Copilot\Types\Rpc\CanvasProviderCloseRequest;
use Revolution\Copilot\Types\Rpc\CanvasProviderInvokeActionRequest;
use Revolution\Copilot\Types\Rpc\CanvasProviderOpenRequest;
use RuntimeException;
use Throwable;

trait HandlesCanvasProvider
{
    private function handleCanvasOpen(array $params): array
    {
        $request = CanvasProviderOpenRequest::fromArray($params);
        $session = $this->sessionForCanvasRequest($request->sessionId, $request->canvasId);

        try {
            return $session->handleCanvasOpen($request)->toArray();
        } catch (CanvasError $e) {
            throw new JsonRpcException(-32603, $e->getMessage(), $e->toEnvelope());
        } catch (Throwable $e) {
            throw new JsonRpcException(-32603, $e->getMessage(), [
                'code' => 'canvas_handler_error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    private function handleCanvasClose(array $params): void
    {
        $request = CanvasProviderCloseRequest::fromArray($params);
        $session = $this->sessionForCanvasRequest($request->sessionId, $request->canvasId);

        try {
            $session->handleCanvasClose($request);
        } catch (CanvasError $e) {
            throw new JsonRpcException(-32603, $e->getMessage(), $e->toEnvelope());
        } catch (Throwable $e) {
            throw new JsonRpcException(-32603, $e->getMessage(), [
                'code' => 'canvas_handler_error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    private function handleCanvasActionInvoke(array $params): mixed
    {
        $request = CanvasProviderInvokeActionRequest::fromArray($params);
        $session = $this->sessionForCanvasRequest($request->sessionId, $request->canvasId);

        try {
            return $session->handleCanvasActionInvoke($request);
        } catch (CanvasError $e) {
            throw new JsonRpcException(-32603, $e->getMessage(), $e->toEnvelope());
        } catch (Throwable $e) {
            throw new JsonRpcException(-32603, $e->getMessage(), [
                'code' => 'canvas_handler_error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    private function sessionForCanvasRequest(string $sessionId, string $canvasId): Session
    {
        $session = $this->sessions[$sessionId] ?? null;

        if (! $session instanceof Session || $session->canvasHandler() === null) {
            throw new RuntimeException("No canvas handler registered for session: {$sessionId}");
        }

        if (! $session->hasCanvas($canvasId)) {
            throw new RuntimeException("No canvas registered with id \"{$canvasId}\"");
        }

        return $session;
    }
}
