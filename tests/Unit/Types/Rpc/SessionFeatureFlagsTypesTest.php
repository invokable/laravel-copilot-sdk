<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\SessionFeatureFlagsMode;
use Revolution\Copilot\Types\Rpc\FeatureFlagAssignmentsUpdate;
use Revolution\Copilot\Types\Rpc\FeatureFlagConfigPatch;
use Revolution\Copilot\Types\Rpc\FeatureFlagEnabledOverride;
use Revolution\Copilot\Types\Rpc\FeatureFlagExpOverrideQuery;
use Revolution\Copilot\Types\Rpc\FeatureFlagReasoningSummariesArm;
use Revolution\Copilot\Types\Rpc\FeatureFlagServiceSnapshot;
use Revolution\Copilot\Types\Rpc\SessionFeatureFlagConfiguration;
use Revolution\Copilot\Types\Rpc\SessionFeatureFlagsConfigureRequest;
use Revolution\Copilot\Types\Rpc\SessionFeatureFlagsConfigureResult;
use Revolution\Copilot\Types\Rpc\SessionFeatureFlagsReadRequest;
use Revolution\Copilot\Types\Rpc\SessionFeatureFlagsReadResult;
use Revolution\Copilot\Types\Rpc\SessionFeatureFlagsUpdateRequest;
use Revolution\Copilot\Types\Rpc\SessionFeatureFlagsUpdateResult;

function featureFlagSnapshotData(): array
{
    return [
        'serviceId' => 'service-1',
        'revision' => 3,
        'active' => true,
        'expResponseResolved' => false,
        'flags' => ['feature-a' => true],
        'expFlags' => ['experiment-a' => 0.5],
        'response' => ['assignments' => ['experiment-a' => 0.5]],
        'assignmentContext' => 'user-context',
        'secondaryAssignmentContext' => 'workspace-context',
        'autoApprovalTargeted' => false,
        'reasoningSummariesArm' => ['offByDefault' => true, 'hintEnabled' => false],
        'gptDefaultModelEnabled' => true,
        'dynamicInstructionsRetrievalArm' => 'control',
    ];
}

describe('SessionFeatureFlagConfiguration', function () {
    it('round trips the session mode and false configuration values', function () {
        $configuration = SessionFeatureFlagConfiguration::fromArray([
            'initOptions' => ['feature-a' => true],
            'mode' => 'cli',
            'streamerMode' => false,
            'firstLaunchAtMillis' => 0,
            'settingsAreLive' => false,
        ]);

        expect($configuration->mode)->toBe(SessionFeatureFlagsMode::Cli)
            ->and($configuration->toArray())->toBe([
                'initOptions' => ['feature-a' => true],
                'mode' => 'cli',
                'streamerMode' => false,
                'firstLaunchAtMillis' => 0,
                'settingsAreLive' => false,
            ]);
    });

    it('preserves a null init-options JSON value', function () {
        $configuration = new SessionFeatureFlagConfiguration(
            initOptions: null,
            mode: SessionFeatureFlagsMode::Local,
        );

        expect($configuration->toArray())->toBe([
            'initOptions' => null,
            'mode' => 'local',
        ]);
    });
});

describe('Feature-flag update value types', function () {
    it('preserves explicit null assignment and preference values', function () {
        $assignments = FeatureFlagAssignmentsUpdate::fromArray(['response' => null]);
        $override = FeatureFlagEnabledOverride::fromArray(['name' => 'feature-a', 'enabled' => null]);

        expect($assignments->toArray())->toBe(['response' => null])
            ->and($override->toArray())->toBe(['name' => 'feature-a', 'enabled' => null]);
    });

    it('round trips config patches and experiment override queries', function () {
        $patch = FeatureFlagConfigPatch::fromArray([
            'values' => ['features' => ['feature-a' => true]],
            'undefinedKeys' => ['obsolete'],
        ]);
        $query = FeatureFlagExpOverrideQuery::fromArray([
            'flag' => 'feature-a',
            'expFlag' => 'experiment-a',
        ]);

        expect($patch->toArray())->toBe([
            'values' => ['features' => ['feature-a' => true]],
            'undefinedKeys' => ['obsolete'],
        ])->and($query->toArray())->toBe([
            'flag' => 'feature-a',
            'expFlag' => 'experiment-a',
        ]);
    });

    it('round trips the reasoning-summary arm', function () {
        $arm = FeatureFlagReasoningSummariesArm::fromArray([
            'offByDefault' => true,
            'hintEnabled' => false,
        ]);

        expect($arm->toArray())->toBe([
            'offByDefault' => true,
            'hintEnabled' => false,
        ]);
    });
});

describe('SessionFeatureFlagsConfigureRequest', function () {
    it('serializes configure inputs and nested assignments', function () {
        $request = SessionFeatureFlagsConfigureRequest::fromArray([
            'isStaff' => false,
            'isExperimental' => true,
            'isTeam' => false,
            'online' => true,
            'streamerMode' => false,
            'isStaffGithub' => false,
            'config' => ['settings' => ['feature-a' => true]],
            'flagOverrides' => ['feature-a' => false],
            'expFlagOverrides' => ['experiment-a' => 0.25],
            'environment' => ['APP_ENV' => 'testing'],
            'assignments' => ['response' => null],
            'firstLaunchAtMillis' => 123,
        ]);

        expect($request->toArray())->toBe([
            'isStaff' => false,
            'isExperimental' => true,
            'isTeam' => false,
            'online' => true,
            'streamerMode' => false,
            'isStaffGithub' => false,
            'config' => ['settings' => ['feature-a' => true]],
            'flagOverrides' => ['feature-a' => false],
            'expFlagOverrides' => ['experiment-a' => 0.25],
            'environment' => ['APP_ENV' => 'testing'],
            'assignments' => ['response' => null],
            'firstLaunchAtMillis' => 123,
        ]);
    });
});

describe('SessionFeatureFlagsReadRequest', function () {
    it('serializes optional evaluations and typed override queries', function () {
        $request = SessionFeatureFlagsReadRequest::fromArray([
            'waitForExpResponse' => false,
            'flags' => ['feature-a'],
            'overrides' => [['flag' => 'feature-a', 'expFlag' => 'experiment-a']],
            'afterRevision' => 0,
            'displayOnly' => false,
            'serviceId' => 'service-1',
        ]);

        expect($request->toArray())->toBe([
            'waitForExpResponse' => false,
            'flags' => ['feature-a'],
            'overrides' => [['flag' => 'feature-a', 'expFlag' => 'experiment-a']],
            'afterRevision' => 0,
            'displayOnly' => false,
            'serviceId' => 'service-1',
        ]);
    });
});

describe('SessionFeatureFlagsUpdateRequest', function () {
    it('serializes nested patches, preferences, and assignment replacement', function () {
        $request = SessionFeatureFlagsUpdateRequest::fromArray([
            'configPatch' => ['values' => ['enabled' => true], 'undefinedKeys' => ['old']],
            'enabledOverride' => ['name' => 'feature-a', 'enabled' => null],
            'assignments' => ['response' => null],
            'secondaryAssignmentContext' => 'workspace-context',
        ]);

        expect($request->toArray())->toBe([
            'configPatch' => ['values' => ['enabled' => true], 'undefinedKeys' => ['old']],
            'enabledOverride' => ['name' => 'feature-a', 'enabled' => null],
            'assignments' => ['response' => null],
            'secondaryAssignmentContext' => 'workspace-context',
        ]);
    });
});

describe('FeatureFlagServiceSnapshot', function () {
    it('hydrates and serializes the nested reasoning-summary arm', function () {
        $snapshot = FeatureFlagServiceSnapshot::fromArray(featureFlagSnapshotData());

        expect($snapshot->reasoningSummariesArm)->toBeInstanceOf(FeatureFlagReasoningSummariesArm::class)
            ->and($snapshot->reasoningSummariesArm->offByDefault)->toBeTrue()
            ->and($snapshot->toArray())->toBe(featureFlagSnapshotData());
    });

    it('preserves a null assignment response JSON value', function () {
        $data = featureFlagSnapshotData();
        $data['response'] = null;
        $snapshot = FeatureFlagServiceSnapshot::fromArray($data);

        expect($snapshot->toArray())->toBe($data);
    });
});

describe('Session feature-flag results', function () {
    it('returns configure and update results as typed service snapshots', function () {
        $data = featureFlagSnapshotData();
        $configured = SessionFeatureFlagsConfigureResult::fromArray($data);
        $updated = SessionFeatureFlagsUpdateResult::fromArray($data);

        expect($configured)->toBeInstanceOf(FeatureFlagServiceSnapshot::class)
            ->and($configured->toArray())->toBe($data)
            ->and($updated)->toBeInstanceOf(FeatureFlagServiceSnapshot::class)
            ->and($updated->toArray())->toBe($data);
    });

    it('hydrates the read snapshot and preserves nullable overrides', function () {
        $data = [
            'snapshot' => featureFlagSnapshotData(),
            'flags' => ['feature-a' => true],
            'overrides' => [true, null, false],
            'displayState' => ['available' => true],
        ];
        $result = SessionFeatureFlagsReadResult::fromArray($data);

        expect($result->snapshot)->toBeInstanceOf(FeatureFlagServiceSnapshot::class)
            ->and($result->overrides)->toBe([true, null, false])
            ->and($result->toArray())->toBe($data);
    });
});
