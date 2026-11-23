<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

readonly class UIBackgroundAgentTask implements Arrayable
{
    public function __construct(
        public string $type,
        public string $id,
        public string $toolCallId,
        public string $description,
        public string $status,
        public int|float $startedAt,
        public string $agentType,
        public string $prompt,
        public ?string $displayName = null,
        public int|float|null $completedAt = null,
        public int|float|null $activeTimeMs = null,
        public int|float|null $activeStartedAt = null,
        public ?string $error = null,
        public ?string $result = null,
        public ?string $modelOverride = null,
        public ?string $resolvedModel = null,
        public ?string $executionMode = null,
        public ?bool $canPromoteToBackground = null,
        public ?string $latestResponse = null,
        public int|float|null $idleSince = null,
        public ?string $source = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $startedAt = $data['startedAt'] ?? 0;
        $completedAt = $data['completedAt'] ?? null;
        $activeTimeMs = $data['activeTimeMs'] ?? null;
        $activeStartedAt = $data['activeStartedAt'] ?? null;
        $idleSince = $data['idleSince'] ?? null;

        return new self(
            type: Arr::string($data, 'type', 'agent'),
            id: Arr::string($data, 'id', ''),
            toolCallId: Arr::string($data, 'toolCallId', ''),
            displayName: isset($data['displayName']) ? Arr::string($data, 'displayName') : null,
            description: Arr::string($data, 'description', ''),
            status: Arr::string($data, 'status', ''),
            startedAt: is_int($startedAt) || is_float($startedAt) ? $startedAt : 0,
            completedAt: is_int($completedAt) || is_float($completedAt) ? $completedAt : null,
            activeTimeMs: is_int($activeTimeMs) || is_float($activeTimeMs) ? $activeTimeMs : null,
            activeStartedAt: is_int($activeStartedAt) || is_float($activeStartedAt) ? $activeStartedAt : null,
            error: isset($data['error']) ? Arr::string($data, 'error') : null,
            agentType: Arr::string($data, 'agentType', ''),
            prompt: Arr::string($data, 'prompt', ''),
            result: isset($data['result']) ? Arr::string($data, 'result') : null,
            modelOverride: isset($data['modelOverride']) ? Arr::string($data, 'modelOverride') : null,
            resolvedModel: isset($data['resolvedModel']) ? Arr::string($data, 'resolvedModel') : null,
            executionMode: isset($data['executionMode']) ? Arr::string($data, 'executionMode') : null,
            canPromoteToBackground: isset($data['canPromoteToBackground']) ? Arr::boolean($data, 'canPromoteToBackground') : null,
            latestResponse: isset($data['latestResponse']) ? Arr::string($data, 'latestResponse') : null,
            idleSince: is_int($idleSince) || is_float($idleSince) ? $idleSince : null,
            source: isset($data['source']) ? Arr::string($data, 'source') : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'type' => $this->type,
            'id' => $this->id,
            'toolCallId' => $this->toolCallId,
            'displayName' => $this->displayName,
            'description' => $this->description,
            'status' => $this->status,
            'startedAt' => $this->startedAt,
            'completedAt' => $this->completedAt,
            'activeTimeMs' => $this->activeTimeMs,
            'activeStartedAt' => $this->activeStartedAt,
            'error' => $this->error,
            'agentType' => $this->agentType,
            'prompt' => $this->prompt,
            'result' => $this->result,
            'modelOverride' => $this->modelOverride,
            'resolvedModel' => $this->resolvedModel,
            'executionMode' => $this->executionMode,
            'canPromoteToBackground' => $this->canPromoteToBackground,
            'latestResponse' => $this->latestResponse,
            'idleSince' => $this->idleSince,
            'source' => $this->source,
        ], static fn ($value): bool => $value !== null);
    }
}
