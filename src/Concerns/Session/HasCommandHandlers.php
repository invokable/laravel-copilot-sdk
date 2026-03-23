<?php

declare(strict_types=1);

namespace Revolution\Copilot\Concerns\Session;

use Closure;
use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Types\CommandContext;
use Revolution\Copilot\Types\CommandDefinition;
use Revolution\Copilot\Types\Rpc\SessionCommandsHandlePendingCommandParams;
use Throwable;

/**
 * Manages command handler registration and execution.
 *
 * @internal
 */
trait HasCommandHandlers
{
    /**
     * Command handlers.
     *
     * @var array<string, Closure(CommandContext): void>
     */
    protected array $commandHandlers = [];

    /**
     * Register command handlers for this session.
     *
     * @param  array<array{name: string, handler: Closure}|CommandDefinition>  $commands
     *
     * @internal
     */
    public function registerCommands(array $commands): void
    {
        $this->commandHandlers = [];

        foreach ($commands as $command) {
            if ($command instanceof Arrayable) {
                $command = $command->toArray();
            }

            if (! is_array($command)) {
                continue;
            }

            if (! isset($command['name'], $command['handler'])) {
                continue;
            }

            if (! is_string($command['name'])) {
                continue;
            }

            if (! $command['handler'] instanceof Closure) {
                continue;
            }

            $this->commandHandlers[$command['name']] = $command['handler'];
        }
    }

    /**
     * Get a command handler by name.
     *
     * @internal
     */
    public function getCommandHandler(string $name): ?Closure
    {
        return $this->commandHandlers[$name] ?? null;
    }

    /**
     * Execute a command handler and send the result back via RPC.
     * Runs in a new Fiber to allow async RPC calls without blocking the event loop.
     *
     * @internal
     */
    protected function executeCommandAndRespond(string $requestId, string $commandName, string $command, string $args): void
    {
        $fiber = new \Fiber(function () use ($requestId, $commandName, $command, $args): void {
            $handler = $this->getCommandHandler($commandName);

            if ($handler === null) {
                try {
                    $this->rpc()->commands()->handlePendingCommand(
                        new SessionCommandsHandlePendingCommandParams(
                            requestId: $requestId,
                            error: "Unknown command: {$commandName}",
                        ),
                    );
                } catch (Throwable) {
                    // Connection lost or RPC error
                }

                return;
            }

            try {
                $context = new CommandContext(
                    sessionId: $this->sessionId,
                    command: $command,
                    commandName: $commandName,
                    args: $args,
                );

                $handler($context);

                $this->rpc()->commands()->handlePendingCommand(
                    new SessionCommandsHandlePendingCommandParams(
                        requestId: $requestId,
                    ),
                );
            } catch (Throwable $e) {
                try {
                    logger()->error('Command handler failed', ['command' => $commandName, 'exception' => $e]);
                    $this->rpc()->commands()->handlePendingCommand(
                        new SessionCommandsHandlePendingCommandParams(
                            requestId: $requestId,
                            error: 'Command failed',
                        ),
                    );
                } catch (Throwable) {
                    // Connection lost or RPC error
                }
            }
        });

        $fiber->start();
    }
}
