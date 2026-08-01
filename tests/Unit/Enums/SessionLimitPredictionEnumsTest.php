<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\SessionLimitPredictionClientType;
use Revolution\Copilot\Enums\SessionLimitPredictionResultKind;
use Revolution\Copilot\Enums\SessionLimitPredictionSource;
use Revolution\Copilot\Enums\SessionLimitPredictionTier;
use Revolution\Copilot\Enums\SessionLimitPredictionUnavailableReason;

describe('Session limit prediction enums', function () {
    it('defines client type cases', function () {
        expect(SessionLimitPredictionClientType::CLI_INTERACTIVE->value)->toBe('cli-interactive')
            ->and(SessionLimitPredictionClientType::CLI_PROMPT->value)->toBe('cli-prompt');
    });

    it('defines source cases', function () {
        expect(SessionLimitPredictionSource::MODEL->value)->toBe('model')
            ->and(SessionLimitPredictionSource::FAMILY->value)->toBe('family')
            ->and(SessionLimitPredictionSource::GLOBAL->value)->toBe('global');
    });

    it('defines tier cases', function () {
        expect(SessionLimitPredictionTier::RECOMMENDED->value)->toBe('recommended')
            ->and(SessionLimitPredictionTier::ADDITIONAL_HEADROOM->value)->toBe('additional_headroom')
            ->and(SessionLimitPredictionTier::GENEROUS_HEADROOM->value)->toBe('generous_headroom')
            ->and(SessionLimitPredictionTier::MAXIMUM_HEADROOM->value)->toBe('maximum_headroom');
    });

    it('defines unavailable reason cases', function () {
        expect(SessionLimitPredictionUnavailableReason::AUTO_UNRESOLVED->value)->toBe('auto_unresolved')
            ->and(SessionLimitPredictionUnavailableReason::NO_MODEL->value)->toBe('no_model');
    });

    it('defines result kind cases', function () {
        expect(SessionLimitPredictionResultKind::AVAILABLE->value)->toBe('available')
            ->and(SessionLimitPredictionResultKind::UNAVAILABLE->value)->toBe('unavailable');
    });
});
