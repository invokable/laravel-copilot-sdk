<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\SessionsGetClientMetadataRequest;

describe('SessionsGetClientMetadataRequest', function () {
    it('can be created with required fields only', function () {
        $request = new SessionsGetClientMetadataRequest(sessionIds: ['s1', 's2']);

        expect($request->sessionIds)->toBe(['s1', 's2'])
            ->and($request->keys)->toBeNull();
    });

    it('can be created from array', function () {
        $request = SessionsGetClientMetadataRequest::fromArray([
            'sessionIds' => ['s1'],
            'keys' => ['k1'],
        ]);

        expect($request->sessionIds)->toBe(['s1'])
            ->and($request->keys)->toBe(['k1']);
    });

    it('roundtrips through fromArray/toArray', function () {
        $data = ['sessionIds' => ['s1', 's2'], 'keys' => ['k1']];

        expect(SessionsGetClientMetadataRequest::fromArray($data)->toArray())->toBe($data);
    });

    it('omits keys when null', function () {
        $request = new SessionsGetClientMetadataRequest(sessionIds: ['s1']);

        expect($request->toArray())->toBe(['sessionIds' => ['s1']]);
    });
});
