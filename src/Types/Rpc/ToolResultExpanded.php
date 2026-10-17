<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\ToolResultType;

/**
 * Expanded canonical result returned by a session tool.
 *
 * @experimental
 */
readonly class ToolResultExpanded implements Arrayable
{
    /**
     * @param  array<int, mixed>|null  $binaryResultsForLlm
     * @param  array<int, ToolResultNewMessage|array>|null  $newMessages
     * @param  array<int, mixed>|null  $contents
     * @param  array<int, string>|null  $toolReferences
     * @param  array<int, mixed>|null  $citableSources
     */
    public function __construct(
        public string $textResultForLlm,
        public ToolResultType|string|null $resultType = null,
        public ?array $binaryResultsForLlm = null,
        public ?string $sessionLog = null,
        public ?string $error = null,
        public mixed $toolTelemetry = null,
        public ?bool $skipLargeOutputProcessing = null,
        public ?array $newMessages = null,
        public ?array $contents = null,
        public ?array $toolReferences = null,
        public ?array $citableSources = null,
        public mixed $skillInvocation = null,
        public ?bool $postToolUseFailureHooksProcessed = null,
        public mixed $uiResource = null,
        public ?array $mcpMeta = null,
        public mixed $structuredContent = null,
        public TaskCompletionDecision|array|null $taskCompletionDecision = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $resultType = $data['resultType'] ?? null;
        $resultType = is_string($resultType)
            ? ToolResultType::tryFrom($resultType) ?? $resultType
            : $resultType;

        return new self(
            textResultForLlm: Arr::string($data, 'textResultForLlm', ''),
            resultType: $resultType,
            binaryResultsForLlm: $data['binaryResultsForLlm'] ?? null,
            sessionLog: $data['sessionLog'] ?? null,
            error: $data['error'] ?? null,
            toolTelemetry: $data['toolTelemetry'] ?? null,
            skipLargeOutputProcessing: $data['skipLargeOutputProcessing'] ?? null,
            newMessages: isset($data['newMessages'])
                ? array_map(
                    fn (array $message) => ToolResultNewMessage::fromArray($message),
                    $data['newMessages'],
                )
                : null,
            contents: $data['contents'] ?? null,
            toolReferences: $data['toolReferences'] ?? null,
            citableSources: $data['citableSources'] ?? null,
            skillInvocation: $data['skillInvocation'] ?? null,
            postToolUseFailureHooksProcessed: $data['postToolUseFailureHooksProcessed'] ?? null,
            uiResource: $data['uiResource'] ?? null,
            mcpMeta: $data['mcpMeta'] ?? null,
            structuredContent: $data['structuredContent'] ?? null,
            taskCompletionDecision: isset($data['taskCompletionDecision'])
                ? TaskCompletionDecision::fromArray($data['taskCompletionDecision'])
                : null,
        );
    }

    public function toArray(): array
    {
        $newMessages = $this->newMessages === null
            ? null
            : array_map(
                fn (ToolResultNewMessage|array $message) => $message instanceof ToolResultNewMessage
                    ? $message->toArray()
                    : ToolResultNewMessage::fromArray($message)->toArray(),
                $this->newMessages,
            );

        $taskCompletionDecision = $this->taskCompletionDecision instanceof TaskCompletionDecision
            ? $this->taskCompletionDecision->toArray()
            : (is_array($this->taskCompletionDecision)
                ? TaskCompletionDecision::fromArray($this->taskCompletionDecision)->toArray()
                : null);

        return array_filter([
            'textResultForLlm' => $this->textResultForLlm,
            'resultType' => $this->resultType instanceof ToolResultType ? $this->resultType->value : $this->resultType,
            'binaryResultsForLlm' => $this->binaryResultsForLlm,
            'sessionLog' => $this->sessionLog,
            'error' => $this->error,
            'toolTelemetry' => $this->toolTelemetry,
            'skipLargeOutputProcessing' => $this->skipLargeOutputProcessing,
            'newMessages' => $newMessages,
            'contents' => $this->contents,
            'toolReferences' => $this->toolReferences,
            'citableSources' => $this->citableSources,
            'skillInvocation' => $this->skillInvocation,
            'postToolUseFailureHooksProcessed' => $this->postToolUseFailureHooksProcessed,
            'uiResource' => $this->uiResource,
            'mcpMeta' => $this->mcpMeta,
            'structuredContent' => $this->structuredContent,
            'taskCompletionDecision' => $taskCompletionDecision,
        ], fn ($value) => $value !== null);
    }
}
