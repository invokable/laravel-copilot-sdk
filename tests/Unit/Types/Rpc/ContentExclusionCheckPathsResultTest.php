<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\ContentExclusionCheckPathsResult;

it('round trips availability and typed path checks', function () {
    $payload = [
        'available' => true,
        'checks' => [
            ['path' => '/workspace/private.txt', 'excluded' => true],
            ['path' => '/workspace/public.txt', 'excluded' => false],
        ],
    ];
    $result = ContentExclusionCheckPathsResult::fromArray($payload);

    expect($result->toArray())->toBe($payload)
        ->and($result->isExcluded('/workspace/private.txt'))->toBeTrue()
        ->and($result->isExcluded('/workspace/public.txt'))->toBeFalse()
        ->and($result->isExcluded('/workspace/not-checked.txt'))->toBeTrue();
});

it('fails closed when checks are unavailable', function () {
    $result = ContentExclusionCheckPathsResult::fromArray([
        'available' => false,
        'checks' => [],
    ]);

    expect($result->available)->toBeFalse()
        ->and($result->checks)->toBe([])
        ->and($result->toArray())->toBe(['available' => false, 'checks' => []])
        ->and($result->isExcluded('/workspace/file.txt'))->toBeTrue();
});
