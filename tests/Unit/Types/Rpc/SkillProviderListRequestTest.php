<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\SkillProviderListRequest;

describe('SkillProviderListRequest', function () {
    it('creates the request from array and converts it to the RPC payload', function () {
        $request = SkillProviderListRequest::fromArray([
            'sessionId' => 'session-123',
        ]);

        expect($request->sessionId)->toBe('session-123')
            ->and($request->toArray())->toBe([
                'sessionId' => 'session-123',
            ]);
    });

    it('rejects a missing session id', function () {
        expect(fn () => SkillProviderListRequest::fromArray([]))
            ->toThrow(InvalidArgumentException::class);
    });
});
