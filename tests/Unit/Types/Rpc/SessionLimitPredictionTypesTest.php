<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\SessionLimitPredictionClientType;
use Revolution\Copilot\Enums\SessionLimitPredictionResultKind;
use Revolution\Copilot\Enums\SessionLimitPredictionSource;
use Revolution\Copilot\Enums\SessionLimitPredictionTier;
use Revolution\Copilot\Enums\SessionLimitPredictionUnavailableReason;
use Revolution\Copilot\Types\Rpc\SessionLimitPredictionBaselineData;
use Revolution\Copilot\Types\Rpc\SessionLimitPredictionDetails;
use Revolution\Copilot\Types\Rpc\SessionLimitPredictionPredictRequest;
use Revolution\Copilot\Types\Rpc\SessionLimitPredictionResult;
use Revolution\Copilot\Types\Rpc\SessionLimitPredictionTierOption;

describe('SessionLimitPredictionPredictRequest', function () {
    it('creates from named arguments and serializes', function () {
        $request = new SessionLimitPredictionPredictRequest(
            modelId: 'gpt-5',
            clientType: SessionLimitPredictionClientType::CLI_INTERACTIVE,
        );

        expect($request->toArray())->toBe([
            'modelId' => 'gpt-5',
            'clientType' => 'cli-interactive',
        ]);
    });

    it('omits null fields', function () {
        expect((new SessionLimitPredictionPredictRequest)->toArray())->toBe([]);
    });

    it('hydrates enum from array', function () {
        $request = SessionLimitPredictionPredictRequest::fromArray([
            'modelId' => 'gpt-5',
            'clientType' => 'cli-prompt',
        ]);

        expect($request->clientType)->toBe(SessionLimitPredictionClientType::CLI_PROMPT)
            ->and($request->modelId)->toBe('gpt-5');
    });
});

describe('SessionLimitPredictionResult', function () {
    it('parses an available prediction with nested details', function () {
        $result = SessionLimitPredictionResult::fromArray([
            'kind' => 'available',
            'prediction' => [
                'clientType' => 'cli-interactive',
                'modelId' => 'gpt-5',
                'source' => 'model',
                'sourceKey' => 'gpt-5',
                'family' => 'gpt',
                'tiers' => [
                    ['tier' => 'recommended', 'cap' => 100.25],
                    ['tier' => 'maximum_headroom', 'cap' => 500.75],
                ],
                'baselineData' => ['windowStart' => '2026-01-01', 'windowEnd' => '2026-02-01'],
                'recommendedTier' => 'recommended',
                'recommendedCap' => 100.5,
            ],
        ]);

        expect($result->kind)->toBe(SessionLimitPredictionResultKind::AVAILABLE)
            ->and($result->reason)->toBeNull()
            ->and($result->prediction)->toBeInstanceOf(SessionLimitPredictionDetails::class)
            ->and($result->prediction->source)->toBe(SessionLimitPredictionSource::MODEL)
            ->and($result->prediction->recommendedTier)->toBe(SessionLimitPredictionTier::RECOMMENDED)
            ->and($result->prediction->recommendedCap)->toBe(100.5)
            ->and($result->prediction->family)->toBe('gpt')
            ->and($result->prediction->tiers)->toHaveCount(2)
            ->and($result->prediction->tiers[0])->toBeInstanceOf(SessionLimitPredictionTierOption::class)
            ->and($result->prediction->tiers[0]->tier)->toBe(SessionLimitPredictionTier::RECOMMENDED)
            ->and($result->prediction->tiers[0]->cap)->toBe(100.25)
            ->and($result->prediction->baselineData)->toBeInstanceOf(SessionLimitPredictionBaselineData::class)
            ->and($result->prediction->baselineData->windowStart)->toBe('2026-01-01');
    });

    it('parses an unavailable prediction with a reason', function () {
        $result = SessionLimitPredictionResult::fromArray([
            'kind' => 'unavailable',
            'reason' => 'auto_unresolved',
        ]);

        expect($result->kind)->toBe(SessionLimitPredictionResultKind::UNAVAILABLE)
            ->and($result->prediction)->toBeNull()
            ->and($result->reason)->toBe(SessionLimitPredictionUnavailableReason::AUTO_UNRESOLVED);
    });

    it('round-trips an available prediction through toArray', function () {
        $data = [
            'kind' => 'available',
            'prediction' => [
                'clientType' => 'cli-interactive',
                'modelId' => 'gpt-5',
                'source' => 'family',
                'sourceKey' => 'gpt',
                'tiers' => [['tier' => 'recommended', 'cap' => 100.25]],
                'baselineData' => ['windowStart' => '2026-01-01', 'windowEnd' => '2026-02-01'],
                'recommendedTier' => 'recommended',
                'recommendedCap' => 100.5,
            ],
        ];

        expect(SessionLimitPredictionResult::fromArray($data)->toArray())->toBe($data);
    });
});
