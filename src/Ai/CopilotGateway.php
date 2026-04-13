<?php

declare(strict_types=1);

namespace Revolution\Copilot\Ai;

use Closure;
use Exception;
use Generator;
use Illuminate\JsonSchema\Types\Type;
use Illuminate\Support\Str;
use Laravel\Ai\Contracts\Gateway\TextGateway;
use Laravel\Ai\Contracts\Providers\TextProvider;
use Laravel\Ai\Gateway\TextGenerationOptions;
use Laravel\Ai\Responses\Data\Meta;
use Laravel\Ai\Responses\Data\Usage;
use Laravel\Ai\Responses\TextResponse;
use Laravel\Ai\Streaming\Events\StreamEvent;
use Laravel\Ai\Streaming\Events\TextDelta;
use Revolution\Copilot\Contracts\CopilotSession;
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\Types\SessionConfig;
use Revolution\Copilot\Types\SessionEvent;
use Revolution\Copilot\Types\SystemMessageConfig;

/**
 * Laravel AI SDK Integration.
 */
class CopilotGateway implements TextGateway
{
    protected ?Closure $invokingToolCallback = null;

    protected ?Closure $toolInvokedCallback = null;

    /**
     * Generate text representing the next message in a conversation.
     *
     * @param  array<string, Type>|null  $schema
     *
     * @throws Exception
     */
    public function generateText(TextProvider $provider, string $model, ?string $instructions, array $messages = [], array $tools = [], ?array $schema = null, ?TextGenerationOptions $options = null, ?int $timeout = null): TextResponse
    {
        $config = new SessionConfig(
            model: $model,
            systemMessage: new SystemMessageConfig(
                content: $instructions,
            ),
        );

        $prompt = last($messages)->content;
        $response = Copilot::run($prompt, config: $config);

        return new TextResponse(
            text: $response->content(),
            usage: new Usage,
            meta: new Meta(
                provider: $provider->name(),
                model: $model,
            ),
        );
    }

    /**
     * Stream text representing the next message in a conversation.
     *
     * @param  array<string, Type>|null  $schema
     */
    public function streamText(string $invocationId, TextProvider $provider, string $model, ?string $instructions, array $messages = [], array $tools = [], ?array $schema = null, ?TextGenerationOptions $options = null, ?int $timeout = null): Generator
    {
        $config = new SessionConfig(
            model: $model,
            systemMessage: new SystemMessageConfig(
                content: $instructions,
            ),
            availableTools: $tools,
        );

        $prompt = last($messages)->content;

        $events = Copilot::stream(function (CopilotSession $session) use ($prompt) {
            foreach ($session->sendAndStream($prompt) as $event) {
                yield $event;
            }
        }, config: $config);

        foreach ($events as $event) {
            if (! is_null($event = $this->toLaravelStreamEvent(
                $invocationId, $event, $provider->name(), $model,
            ))) {
                yield $event;
            }
        }
    }

    protected function toLaravelStreamEvent($invocationId, SessionEvent $event, $provider, $model): ?StreamEvent
    {
        return tap(match (true) {
            $event->isAssistantMessageDelta() => new TextDelta(
                id: $event->id,
                messageId: Str::ulid()->toString(),
                delta: $event->deltaContent(),
                timestamp: now()->timestamp,
            ),
            default => null,
        }, function ($event) use ($invocationId) {
            $event?->withInvocationId($invocationId);
        });
    }

    /**
     * Specify callbacks that should be invoked when tools are invoking / invoked.
     */
    public function onToolInvocation(Closure $invoking, Closure $invoked): self
    {
        $this->invokingToolCallback = $invoking;
        $this->toolInvokedCallback = $invoked;

        return $this;
    }
}
