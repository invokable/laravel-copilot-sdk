<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\ContentExclusionCheckPathsRequest;

it('round trips paths without changing order or duplicates', function () {
    $paths = ['/workspace/a.txt', '/workspace/b.txt', '/workspace/a.txt'];
    $request = new ContentExclusionCheckPathsRequest($paths);

    expect($request->toArray())->toBe(['paths' => $paths])
        ->and(ContentExclusionCheckPathsRequest::fromArray($request->toArray())->paths)->toBe($paths);
});
