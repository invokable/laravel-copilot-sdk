<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\RuntimeOpenBrowserRequest;
use Revolution\Copilot\Types\Rpc\SandboxPathGrantWarningParams;
use Revolution\Copilot\Types\Rpc\SandboxPathGrantWarningResult;

describe('runtime and sandbox path grant RPC types', function () {
    it('round-trips a browser URL request', function () {
        $request = RuntimeOpenBrowserRequest::fromArray(['url' => 'https://example.test/authorize']);

        expect($request->toArray())->toBe(['url' => 'https://example.test/authorize']);
    });

    it('preserves a false read-only flag in a path warning request', function () {
        $request = SandboxPathGrantWarningParams::fromArray([
            'resolvedPath' => 'C:\\',
            'readOnly' => false,
        ]);

        expect($request->toArray())->toBe([
            'resolvedPath' => 'C:\\',
            'readOnly' => false,
        ]);
    });

    it('omits a missing path grant warning', function () {
        $result = SandboxPathGrantWarningResult::fromArray([]);

        expect($result->warning)->toBeNull()
            ->and($result->toArray())->toBe([]);
    });
});
