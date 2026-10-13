<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Reasoning-summary policy for the session's current model.
 */
readonly class ModelGetReasoningSummariesResult implements Arrayable
{
    public function __construct(
        public bool $ready = false,
        public bool $experimentSettled = false,
        public bool $sessionShowsSummaries = false,
        public bool $modelDefaultShowsSummaries = false,
        public bool $showReasoningHeaders = false,
        public ?bool $visibility = null,
        public bool $showHiddenHint = false,
        public bool $clearRedundantSetting = false,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            ready: $data['ready'] ?? false,
            experimentSettled: $data['experimentSettled'] ?? false,
            sessionShowsSummaries: $data['sessionShowsSummaries'] ?? false,
            modelDefaultShowsSummaries: $data['modelDefaultShowsSummaries'] ?? false,
            showReasoningHeaders: $data['showReasoningHeaders'] ?? false,
            visibility: $data['visibility'] ?? null,
            showHiddenHint: $data['showHiddenHint'] ?? false,
            clearRedundantSetting: $data['clearRedundantSetting'] ?? false,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'ready' => $this->ready,
            'experimentSettled' => $this->experimentSettled,
            'sessionShowsSummaries' => $this->sessionShowsSummaries,
            'modelDefaultShowsSummaries' => $this->modelDefaultShowsSummaries,
            'showReasoningHeaders' => $this->showReasoningHeaders,
            'visibility' => $this->visibility,
            'showHiddenHint' => $this->showHiddenHint,
            'clearRedundantSetting' => $this->clearRedundantSetting,
        ], fn ($value) => $value !== null);
    }
}
