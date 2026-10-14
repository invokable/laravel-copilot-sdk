<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\ContentExclusionValidatePoliciesResult;

it('round trips normalized content-exclusion policies', function () {
    $policies = [
        ['pattern' => 'vendor/**', 'source' => 'user'],
        ['pattern' => 'build/**', 'source' => 'workspace'],
    ];
    $result = new ContentExclusionValidatePoliciesResult($policies);

    expect($result->toArray())->toBe(['policies' => $policies])
        ->and(ContentExclusionValidatePoliciesResult::fromArray($result->toArray())->policies)->toBe($policies);
});
