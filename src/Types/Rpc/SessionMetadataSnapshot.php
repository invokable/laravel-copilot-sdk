<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\AgentMode;

/**
 * Point-in-time metadata snapshot for a session.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionMetadataSnapshot implements Arrayable
{
    /**
     * @param  ?array<string, mixed>  $remoteMetadata
     * @param  ?array<string, mixed>  $workspace
     * @param  SessionLimitsConfig|null  $sessionLimits  Current session limits, or null when no limits are active
     */
    public function __construct(
        public string $sessionId,
        public string $startTime,
        public string $modifiedTime,
        public bool $isRemote,
        public bool $alreadyInUse,
        public ?string $workspacePath,
        public string $workingDirectory,
        public AgentMode|string $currentMode,
        public ?string $initialName = null,
        public ?array $remoteMetadata = null,
        public ?string $summary = null,
        public ?string $selectedModel = null,
        public ?array $workspace = null,
        public ?SessionLimitsConfig $sessionLimits = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $currentMode = $data['currentMode'] ?? AgentMode::INTERACTIVE->value;
        if (is_string($currentMode)) {
            $currentMode = AgentMode::tryFrom($currentMode) ?? $currentMode;
        }

        $sessionLimits = isset($data['sessionLimits']) && is_array($data['sessionLimits'])
            ? SessionLimitsConfig::fromArray($data['sessionLimits'])
            : null;

        return new self(
            sessionId: Arr::string($data, 'sessionId', ''),
            startTime: Arr::string($data, 'startTime', ''),
            modifiedTime: Arr::string($data, 'modifiedTime', ''),
            isRemote: Arr::boolean($data, 'isRemote', false),
            alreadyInUse: Arr::boolean($data, 'alreadyInUse', false),
            workspacePath: $data['workspacePath'] ?? null,
            workingDirectory: Arr::string($data, 'workingDirectory', ''),
            currentMode: $currentMode,
            initialName: $data['initialName'] ?? null,
            remoteMetadata: isset($data['remoteMetadata']) && is_array($data['remoteMetadata']) ? $data['remoteMetadata'] : null,
            summary: $data['summary'] ?? null,
            selectedModel: $data['selectedModel'] ?? null,
            workspace: isset($data['workspace']) && is_array($data['workspace']) ? $data['workspace'] : null,
            sessionLimits: $sessionLimits,
        );
    }

    public function toArray(): array
    {
        $currentMode = $this->currentMode instanceof AgentMode
            ? $this->currentMode->value
            : $this->currentMode;

        return array_filter([
            'sessionId' => $this->sessionId,
            'startTime' => $this->startTime,
            'modifiedTime' => $this->modifiedTime,
            'isRemote' => $this->isRemote,
            'alreadyInUse' => $this->alreadyInUse,
            'workspacePath' => $this->workspacePath,
            'workingDirectory' => $this->workingDirectory,
            'currentMode' => $currentMode,
            'initialName' => $this->initialName,
            'remoteMetadata' => $this->remoteMetadata,
            'summary' => $this->summary,
            'selectedModel' => $this->selectedModel,
            'workspace' => $this->workspace,
            'sessionLimits' => $this->sessionLimits?->toArray(),
        ], fn ($value): bool => $value !== null);
    }
}
