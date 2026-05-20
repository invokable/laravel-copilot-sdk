<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\AbortReason;
use Revolution\Copilot\Types\Rpc\AbortRequest;

describe('AbortRequest', function () {
    it('can be created from array with reason', function () {
        $request = AbortRequest::fromArray([
            'reason' => 'user_initiated',
        ]);

        expect($request->reason)->toBe(AbortReason::UserInitiated);
    });

    it('can be created from array without reason', function () {
        $request = AbortRequest::fromArray([]);

        expect($request->reason)->toBeNull();
    });

    it('can convert to array with reason', function () {
        $request = new AbortRequest(reason: AbortReason::RemoteCommand);

        expect($request->toArray())->toBe([
            'reason' => 'remote_command',
        ]);
    });

    it('excludes null reason from array', function () {
        $request = new AbortRequest;

        expect($request->toArray())->toBe([]);
    });
});
