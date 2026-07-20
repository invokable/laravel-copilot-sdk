<?php

declare(strict_types=1);

use Revolution\Copilot\Types\CopilotExpAssignmentResponse;
use Revolution\Copilot\Types\ExpConfigEntry;

describe('CopilotExpAssignmentResponse', function () {
    it('can be created with defaults', function () {
        $response = new CopilotExpAssignmentResponse;

        expect($response->features)->toBe([])
            ->and($response->flights)->toBe([])
            ->and($response->configs)->toBe([])
            ->and($response->parameterGroups)->toBeNull()
            ->and($response->flightingVersion)->toBeNull()
            ->and($response->impressionId)->toBeNull()
            ->and($response->assignmentContext)->toBe('');
    });

    it('can be created with all fields', function () {
        $entry = new ExpConfigEntry(id: 'entry-1', parameters: ['flag' => true]);

        $response = new CopilotExpAssignmentResponse(
            features: ['feature-a'],
            flights: ['flight-a' => 'treatment'],
            configs: [$entry],
            parameterGroups: ['group' => 'data'],
            flightingVersion: 3,
            impressionId: 'impression-1',
            assignmentContext: 'context-string',
        );

        expect($response->features)->toBe(['feature-a'])
            ->and($response->flights)->toBe(['flight-a' => 'treatment'])
            ->and($response->configs)->toBe([$entry])
            ->and($response->parameterGroups)->toBe(['group' => 'data'])
            ->and($response->flightingVersion)->toBe(3)
            ->and($response->impressionId)->toBe('impression-1')
            ->and($response->assignmentContext)->toBe('context-string');
    });

    it('can be created from array', function () {
        $response = CopilotExpAssignmentResponse::fromArray([
            'Features' => ['feature-a'],
            'Flights' => ['flight-a' => 'treatment'],
            'Configs' => [
                ['Id' => 'entry-1', 'Parameters' => ['flag' => true]],
            ],
            'ParameterGroups' => ['group' => 'data'],
            'FlightingVersion' => 3,
            'ImpressionId' => 'impression-1',
            'AssignmentContext' => 'context-string',
        ]);

        expect($response->features)->toBe(['feature-a'])
            ->and($response->flights)->toBe(['flight-a' => 'treatment'])
            ->and($response->configs[0])->toBeInstanceOf(ExpConfigEntry::class)
            ->and($response->configs[0]->id)->toBe('entry-1')
            ->and($response->parameterGroups)->toBe(['group' => 'data'])
            ->and($response->flightingVersion)->toBe(3)
            ->and($response->impressionId)->toBe('impression-1')
            ->and($response->assignmentContext)->toBe('context-string');
    });

    it('converts to array excluding null values', function () {
        $response = new CopilotExpAssignmentResponse(assignmentContext: 'context-string');

        expect($response->toArray())->toBe([
            'Features' => [],
            'Flights' => [],
            'Configs' => [],
            'AssignmentContext' => 'context-string',
        ]);
    });

    it('roundtrips through fromArray and toArray', function () {
        $data = [
            'Features' => ['feature-a'],
            'Flights' => ['flight-a' => 'treatment'],
            'Configs' => [
                ['Id' => 'entry-1', 'Parameters' => ['flag' => true]],
            ],
            'FlightingVersion' => 3,
            'ImpressionId' => 'impression-1',
            'AssignmentContext' => 'context-string',
        ];

        $response = CopilotExpAssignmentResponse::fromArray($data);

        expect($response->toArray())->toBe($data);
    });
});
