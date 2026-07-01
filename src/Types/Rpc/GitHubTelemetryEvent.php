<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * A single telemetry event in the runtime's native GitHub-shaped telemetry format,
 * forwarded verbatim to opted-in hosts.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class GitHubTelemetryEvent implements Arrayable
{
    /**
     * @param  string  $kind  Event type/kind (e.g. get_completion_with_tools_turn, tool_call_executed).
     * @param  array<string, string>  $properties  String-valued properties as a map from key to value.
     * @param  array<string, float>  $metrics  Numeric metrics as a map from key to value.
     * @param  ?string  $createdAt  Timestamp when the event was created (ISO 8601 format).
     * @param  ?string  $modelCallId  Reference to the model call that produced this event.
     * @param  ?string  $expAssignmentContext  Experiment assignment context.
     * @param  array<string, string>|null  $features  Feature flags enabled for this session.
     * @param  ?string  $sessionId  Session identifier the event belongs to.
     * @param  ?string  $copilotTrackingId  Copilot tracking ID for user-level attribution.
     * @param  ?GitHubTelemetryClientInfo  $client  Client environment metadata.
     */
    public function __construct(
        public string $kind,
        public array $properties,
        public array $metrics,
        public ?string $createdAt = null,
        public ?string $modelCallId = null,
        public ?string $expAssignmentContext = null,
        public ?array $features = null,
        public ?string $sessionId = null,
        public ?string $copilotTrackingId = null,
        public ?GitHubTelemetryClientInfo $client = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $client = null;
        if (isset($data['client'])) {
            $client = $data['client'] instanceof GitHubTelemetryClientInfo
                ? $data['client']
                : GitHubTelemetryClientInfo::fromArray($data['client']);
        }

        return new self(
            kind: Arr::string($data, 'kind', ''),
            properties: Arr::array($data, 'properties', []),
            metrics: Arr::array($data, 'metrics', []),
            createdAt: $data['created_at'] ?? null,
            modelCallId: $data['model_call_id'] ?? null,
            expAssignmentContext: $data['exp_assignment_context'] ?? null,
            features: $data['features'] ?? null,
            sessionId: $data['session_id'] ?? null,
            copilotTrackingId: $data['copilot_tracking_id'] ?? null,
            client: $client,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'kind' => $this->kind,
            'properties' => $this->properties,
            'metrics' => $this->metrics,
            'created_at' => $this->createdAt,
            'model_call_id' => $this->modelCallId,
            'exp_assignment_context' => $this->expAssignmentContext,
            'features' => $this->features,
            'session_id' => $this->sessionId,
            'copilot_tracking_id' => $this->copilotTrackingId,
            'client' => $this->client?->toArray(),
        ], fn ($v) => $v !== null);
    }
}
