<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\AutoTierDescriptor;
use Revolution\Copilot\Types\Rpc\AutoTierMetadata;
use Revolution\Copilot\Types\Rpc\AutoTierStatus;

it('round trips an Auto tier status and omits nullable fields by default', function () {
    $status = AutoTierStatus::fromArray(['enabled' => true]);

    expect($status->toArray())->toBe(['enabled' => true]);
});

it('round trips an Auto tier descriptor', function () {
    $descriptor = AutoTierDescriptor::fromArray([
        'description' => 'Balanced routing',
        'displayName' => 'Balance',
        'id' => 'balance',
        'status' => ['enabled' => true, 'message' => 'Available'],
        'type' => 'auto',
    ]);

    expect($descriptor->toArray())->toBe([
        'description' => 'Balanced routing',
        'displayName' => 'Balance',
        'id' => 'balance',
        'status' => ['enabled' => true, 'message' => 'Available'],
        'type' => 'auto',
    ]);
});

it('round trips Auto tier metadata and defaults tiers to an empty list', function () {
    $metadata = AutoTierMetadata::fromArray([
        'defaultTier' => 'balance',
        'tiers' => [[
            'description' => 'Balanced routing',
            'displayName' => 'Balance',
            'id' => 'balance',
            'status' => ['enabled' => true],
            'type' => 'auto',
        ]],
        'providerId' => 'copilot',
    ]);

    expect($metadata->toArray())->toBe([
        'defaultTier' => 'balance',
        'tiers' => [[
            'description' => 'Balanced routing',
            'displayName' => 'Balance',
            'id' => 'balance',
            'status' => ['enabled' => true],
            'type' => 'auto',
        ]],
        'providerId' => 'copilot',
    ])->and(AutoTierMetadata::fromArray(['defaultTier' => 'balance'])->tiers)->toBe([]);
});
