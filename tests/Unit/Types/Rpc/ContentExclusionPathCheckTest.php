<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\ContentExclusionPathCheck;

it('round trips the checked path and exclusion status', function () {
    $check = new ContentExclusionPathCheck('/workspace/private.txt', true);

    expect($check->toArray())->toBe([
        'path' => '/workspace/private.txt',
        'excluded' => true,
    ])
        ->and(ContentExclusionPathCheck::fromArray($check->toArray()))->toEqual($check);
});
