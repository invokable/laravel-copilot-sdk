<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/** @experimental */
readonly class HistoryImportAssistantMessage implements Arrayable
{
    /** @param  array<HistoryImportToolRequest|array>|null  $toolRequests */
    public function __construct(
        public string $messageId,
        public string $content,
        public ?string $model = null,
        public ?string $turnId = null,
        public ?array $toolRequests = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            messageId: Arr::string($data, 'messageId'),
            content: Arr::string($data, 'content'),
            model: $data['model'] ?? null,
            turnId: $data['turnId'] ?? null,
            toolRequests: isset($data['toolRequests']) ? array_map(
                fn (HistoryImportToolRequest|array $request) => $request instanceof HistoryImportToolRequest
                    ? $request
                    : HistoryImportToolRequest::fromArray($request),
                Arr::array($data, 'toolRequests'),
            ) : null,
        );
    }

    public function toArray(): array
    {
        $data = array_filter([
            'messageId' => $this->messageId,
            'content' => $this->content,
            'model' => $this->model,
            'turnId' => $this->turnId,
        ], fn ($value) => $value !== null);
        if ($this->toolRequests !== null) {
            $data['toolRequests'] = array_map(
                fn (HistoryImportToolRequest|array $request) => ($request instanceof HistoryImportToolRequest
                    ? $request
                    : HistoryImportToolRequest::fromArray($request))->toArray(),
                $this->toolRequests,
            );
        }

        return $data;
    }
}
