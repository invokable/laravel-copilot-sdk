<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * ExP ("flight") assignment data, in the same JSON shape the Copilot CLI
 * fetches from the experimentation service. Field names are PascalCase to match
 * the on-the-wire contract consumed by the runtime.
 */
readonly class CopilotExpAssignmentResponse implements Arrayable
{
    /**
     * @param  array<int, string>  $features  Enabled feature names.
     * @param  array<string, string>  $flights  Assigned flights keyed by flight name.
     * @param  array<int, ExpConfigEntry>  $configs  Configuration entries carrying typed parameter values.
     * @param  mixed  $parameterGroups  Opaque parameter-group payload passed through untouched.
     * @param  ?int  $flightingVersion  Version of the flighting configuration.
     * @param  ?string  $impressionId  Impression identifier for the assignment.
     * @param  string  $assignmentContext  Assignment context string forwarded to CAPI and telemetry.
     */
    public function __construct(
        public array $features = [],
        public array $flights = [],
        public array $configs = [],
        public mixed $parameterGroups = null,
        public ?int $flightingVersion = null,
        public ?string $impressionId = null,
        public string $assignmentContext = '',
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            features: Arr::array($data, 'Features', []),
            flights: Arr::array($data, 'Flights', []),
            configs: array_map(
                fn (array $entry) => ExpConfigEntry::fromArray($entry),
                Arr::array($data, 'Configs', []),
            ),
            parameterGroups: $data['ParameterGroups'] ?? null,
            flightingVersion: $data['FlightingVersion'] ?? null,
            impressionId: $data['ImpressionId'] ?? null,
            assignmentContext: Arr::string($data, 'AssignmentContext', ''),
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'Features' => $this->features,
            'Flights' => $this->flights,
            'Configs' => array_map(fn (ExpConfigEntry $entry) => $entry->toArray(), $this->configs),
            'ParameterGroups' => $this->parameterGroups,
            'FlightingVersion' => $this->flightingVersion,
            'ImpressionId' => $this->impressionId,
            'AssignmentContext' => $this->assignmentContext,
        ], fn ($v) => $v !== null);
    }
}
