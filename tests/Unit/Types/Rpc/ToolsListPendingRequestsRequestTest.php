<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\ToolsListPendingRequestsRequest;

describe('ToolsListPendingRequestsRequest', function () {
    it('can be created from an empty array', function () {
        $request = ToolsListPendingRequestsRequest::fromArray([]);

        expect($request)->toBeInstanceOf(ToolsListPendingRequestsRequest::class)
            ->and($request->toArray())->toBe([]);
    });

    it('converts to an empty array', function () {
        expect((new ToolsListPendingRequestsRequest)->toArray())->toBe([]);
    });
});
