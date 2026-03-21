<?php

declare(strict_types=1);

namespace Revolution\Copilot\Concerns\Client;

use Revolution\Copilot\Events\Client\ToolCall;
use Revolution\Copilot\Types\ToolResultObject;
use Revolution\Copilot\Types\UserInputRequest;
use RuntimeException;
use Throwable;

/**
 * Handles incoming JSON-RPC requests from the Copilot CLI server
 * (tool calls, permission requests, user input, hooks invocation).
 *
 * @internal
 */
trait HandlesServerRequests
{
    /**
     * Handle tool call requests from the server.
     */
    protected function handleToolCall(array $params): array
    {
        $sessionId = $params['sessionId'] ?? null;
        $toolCallId = $params['toolCallId'] ?? null;
        $toolName = $params['toolName'] ?? null;
        $arguments = $params['arguments'] ?? [];

        if ($sessionId === null || $toolName === null) {
            return ['result' => [
                'textResultForLlm' => 'Invalid tool call parameters',
                'resultType' => 'failure',
            ]];
        }

        $session = $this->sessions[$sessionId] ?? null;

        if ($session === null) {
            return ['result' => [
                'textResultForLlm' => "Unknown session: {$sessionId}",
                'resultType' => 'failure',
            ]];
        }

        $handler = $session->getToolHandler($toolName);

        if ($handler === null) {
            return ['result' => [
                'textResultForLlm' => "Tool not supported by SDK client: {$toolName}",
                'resultType' => 'rejected',
            ]];
        }

        try {
            $invocation = [
                'sessionId' => $sessionId,
                'toolCallId' => $toolCallId,
                'toolName' => $toolName,
                'arguments' => $arguments,
            ];

            /** @var ToolResultObject|array|mixed $result */
            $result = $handler($arguments, $invocation);

            $result = $result instanceof ToolResultObject ? $result->toArray() : $result;

            ToolCall::dispatch($arguments, $invocation, $result);

            // Normalize result
            if (is_string($result)) {
                return ['result' => $result];
            }

            if (is_array($result) && isset($result['textResultForLlm'])) {
                return ['result' => $result];
            }

            return ['result' => [
                'textResultForLlm' => is_array($result) ? json_encode($result) : (string) $result,
                'resultType' => 'success',
            ]];
        } catch (Throwable $e) {
            return ['result' => [
                'textResultForLlm' => "Tool execution failed: {$e->getMessage()}",
                'resultType' => 'failure',
                'error' => $e->getMessage(),
            ]];
        }
    }

    /**
     * Handle permission requests from the server.
     */
    protected function handlePermissionRequest(array $params): array
    {
        $sessionId = $params['sessionId'] ?? null;
        $request = $params['permissionRequest'] ?? [];

        if ($sessionId === null) {
            return ['result' => ['kind' => 'denied-no-approval-rule-and-could-not-request-from-user']];
        }

        $session = $this->sessions[$sessionId] ?? null;

        if ($session === null) {
            return ['result' => ['kind' => 'denied-no-approval-rule-and-could-not-request-from-user']];
        }

        return ['result' => $session->handlePermissionRequest($request)];
    }

    /**
     * Handle user input requests from the server.
     */
    protected function handleUserInputRequest(array $params): array
    {
        $sessionId = $params['sessionId'] ?? null;
        $question = $params['question'] ?? '';
        $choices = $params['choices'] ?? null;
        $allowFreeform = $params['allowFreeform'] ?? null;

        if ($sessionId === null || $question === '') {
            throw new \InvalidArgumentException('Invalid user input request payload');
        }

        $session = $this->sessions[$sessionId] ?? null;

        if ($session === null) {
            throw new RuntimeException("Session not found: {$sessionId}");
        }

        $request = new UserInputRequest(
            question: $question,
            choices: $choices,
            allowFreeform: $allowFreeform,
        );

        $response = $session->handleUserInputRequest($request);

        return $response->toArray();
    }

    /**
     * Handle hooks invocation from the server.
     */
    protected function handleHooksInvoke(array $params): array
    {
        $sessionId = $params['sessionId'] ?? null;
        $hookType = $params['hookType'] ?? '';
        $input = $params['input'] ?? null;

        if ($sessionId === null || $hookType === '') {
            throw new \InvalidArgumentException('Invalid hooks invoke payload');
        }

        $session = $this->sessions[$sessionId] ?? null;

        if ($session === null) {
            throw new RuntimeException("Session not found: {$sessionId}");
        }

        $output = $session->handleHooksInvoke($hookType, $input);

        return ['output' => $output];
    }
}
