<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\ContentExclusionValidatePoliciesRequest;

it('round trips raw policy JSON without changing its contents', function () {
    $policiesJson = '{ "policies" : [ {"path":"vendor/**"} ] }';
    $request = new ContentExclusionValidatePoliciesRequest($policiesJson);

    expect($request->toArray())->toBe(['policiesJson' => $policiesJson])
        ->and(ContentExclusionValidatePoliciesRequest::fromArray($request->toArray())->policiesJson)->toBe($policiesJson);
});
