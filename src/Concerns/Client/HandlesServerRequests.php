<?php

declare(strict_types=1);

namespace Revolution\Copilot\Concerns\Client;

use Revolution\Copilot\Events\Client\ToolCall;
use Revolution\Copilot\Types\AutoModeSwitchRequest;
use Revolution\Copilot\Types\ExitPlanModeRequest;
use Revolution\Copilot\Types\Rpc\ExtensionLaunchProviderResolveRequest;
use Revolution\Copilot\Types\Rpc\ExtensionLaunchProviderResolveResult;
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
     * Handle exit plan mode requests from the server.
     */
    protected function handleExitPlanModeRequest(array $params): array
    {
        $sessionId = $params['sessionId'] ?? null;
        $summary = $params['summary'] ?? null;
        $actions = $params['actions'] ?? null;
        $recommendedAction = $params['recommendedAction'] ?? null;

        if (! is_string($sessionId) || $sessionId === ''
            || ! is_string($summary)
            || ! is_array($actions)
            || ! is_string($recommendedAction)) {
            throw new \InvalidArgumentException('Invalid exit plan mode request payload');
        }

        $session = $this->sessions[$sessionId] ?? null;

        if ($session === null) {
            throw new RuntimeException("Session not found: {$sessionId}");
        }

        $request = new ExitPlanModeRequest(
            summary: $summary,
            actions: $actions,
            recommendedAction: $recommendedAction,
            planContent: $params['planContent'] ?? null,
        );

        return $session->handleExitPlanModeRequest($request)->toArray();
    }

    /**
     * Handle auto mode switch requests from the server.
     */
    protected function handleAutoModeSwitchRequest(array $params): array
    {
        $sessionId = $params['sessionId'] ?? null;

        if (! is_string($sessionId) || $sessionId === '') {
            throw new \InvalidArgumentException('Invalid auto mode switch request payload');
        }

        $session = $this->sessions[$sessionId] ?? null;

        if ($session === null) {
            throw new RuntimeException("Session not found: {$sessionId}");
        }

        $response = $session->handleAutoModeSwitchRequest(
            AutoModeSwitchRequest::fromArray($params),
        );

        return ['response' => $response->value];
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

    /**
     * Resolve a host-provided extension launch profile.
     */
    protected function handleExtensionLaunchProviderResolve(array $params): array
    {
        if ($this->extensionLaunchProvider === null) {
            return [];
        }

        try {
            $result = ($this->extensionLaunchProvider)(
                ExtensionLaunchProviderResolveRequest::fromArray($params),
            );

            if ($result instanceof ExtensionLaunchProviderResolveResult) {
                return $result->toArray();
            }

            if (is_array($result)) {
                return ExtensionLaunchProviderResolveResult::fromArray($result)->toArray();
            }
        } catch (Throwable) {
            return [];
        }

        return [];
    }
}
