<?php

declare(strict_types=1);

namespace Revolution\Copilot\Ai;

use Closure;
use Exception;
use Generator;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Str;
use Laravel\Ai\Contracts\Files\TranscribableAudio;
use Laravel\Ai\Contracts\Gateway\Gateway;
use Laravel\Ai\Contracts\Providers\AudioProvider;
use Laravel\Ai\Contracts\Providers\EmbeddingProvider;
use Laravel\Ai\Contracts\Providers\ImageProvider;
use Laravel\Ai\Contracts\Providers\TextProvider;
use Laravel\Ai\Contracts\Providers\TranscriptionProvider;
use Laravel\Ai\Files\Image as ImageFile;
use Laravel\Ai\Gateway\Prism\PrismException;
use Laravel\Ai\Gateway\TextGenerationOptions;
use Laravel\Ai\Responses\AudioResponse;
use Laravel\Ai\Responses\Data\Meta;
use Laravel\Ai\Responses\Data\Usage;
use Laravel\Ai\Responses\EmbeddingsResponse;
use Laravel\Ai\Responses\ImageResponse;
use Laravel\Ai\Responses\TextResponse;
use Laravel\Ai\Responses\TranscriptionResponse;
use Laravel\Ai\Streaming\Events\StreamEvent;
use Laravel\Ai\Streaming\Events\TextDelta;
use LogicException;
use Prism\Prism\Exceptions\PrismException as PrismVendorException;
use Revolution\Copilot\Contracts\CopilotSession;
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\Types\SessionConfig;
use Revolution\Copilot\Types\SessionEvent;
use Revolution\Copilot\Types\SystemMessageConfig;

/**
 * Laravel AI SDK Integration.
 */
class CopilotGateway implements Gateway
{
    protected $invokingToolCallback;

    protected $toolInvokedCallback;

    public function __construct(protected Dispatcher $events)
    {
        $this->invokingToolCallback = fn () => true;
        $this->toolInvokedCallback = fn () => true;
    }

    /**
     * Generate text representing the next message in a conversation.
     *
     * @param  array<string, \Illuminate\JsonSchema\Types\Type>|null  $schema
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
     * @param  array<string, \Illuminate\JsonSchema\Types\Type>|null  $schema
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

        try {
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
        } catch (PrismVendorException $e) {
            throw PrismException::toAiException($e, $provider, $model);
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
    public function onToolInvocation(Closure $invoking, Closure $invoked): Gateway
    {
        $this->invokingToolCallback = $invoking;
        $this->toolInvokedCallback = $invoked;

        return $this;
    }

    /**
     * Generate audio from the given text.
     */
    public function generateAudio(AudioProvider $provider, string $model, string $text, string $voice, ?string $instructions = null): AudioResponse
    {
        throw new LogicException('Not supported.');
    }

    /**
     * Generate embedding vectors representing the given inputs.
     *
     * @param  string[]  $inputs
     */
    public function generateEmbeddings(EmbeddingProvider $provider, string $model, array $inputs, int $dimensions): EmbeddingsResponse
    {
        throw new LogicException('Not supported.');
    }

    /**
     * Generate an image.
     *
     * @param  array<ImageFile>  $attachments
     * @param  '3:2'|'2:3'|'1:1'  $size
     * @param  'low'|'medium'|'high'  $quality
     */
    public function generateImage(ImageProvider $provider, string $model, string $prompt, array $attachments = [], ?string $size = null, ?string $quality = null, ?int $timeout = null): ImageResponse
    {
        throw new LogicException('Not supported.');
    }

    /**
     * Generate text from the given audio.
     */
    public function generateTranscription(TranscriptionProvider $provider, string $model, TranscribableAudio $audio, ?string $language = null, bool $diarize = false, int $timeout = 30): TranscriptionResponse
    {
        throw new LogicException('Not supported.');
    }
}
