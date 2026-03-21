<?php

declare(strict_types=1);

namespace Revolution\Copilot\Concerns\Session;

use Closure;
use Revolution\Copilot\Types\UserInputRequest;
use Revolution\Copilot\Types\UserInputResponse;

/**
 * Manages user input request handler registration and execution.
 *
 * @internal
 */
trait HasUserInputHandler
{
    /**
     * User input handler.
     *
     * @var Closure(UserInputRequest, array): UserInputResponse|null
     */
    protected ?Closure $userInputHandler = null;

    /**
     * Register a user input handler.
     *
     * @param  Closure(UserInputRequest, array): UserInputResponse|null  $handler
     *
     * @internal
     */
    public function registerUserInputHandler(?Closure $handler): void
    {
        $this->userInputHandler = $handler;
    }

    /**
     * Handle a user input request.
     *
     * @throws \RuntimeException
     *
     * @internal
     */
    public function handleUserInputRequest(UserInputRequest $request): UserInputResponse
    {
        if ($this->userInputHandler === null) {
            throw new \RuntimeException('User input requested but no handler registered');
        }

        /** @var UserInputResponse|array $result */
        $result = ($this->userInputHandler)($request, ['sessionId' => $this->sessionId]);

        return $result instanceof UserInputResponse
            ? $result
            : UserInputResponse::fromArray($result);
    }
}
