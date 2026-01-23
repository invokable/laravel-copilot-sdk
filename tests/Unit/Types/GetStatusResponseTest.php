<?php

declare(strict_types=1);

use Revolution\Copilot\Types\GetStatusResponse;

describe('GetStatusResponse', function () {
    it('can be created from array', function () {
        $response = GetStatusResponse::fromArray([
            'version' => '1.0.0',
            'protocolVersion' => 3,
        ]);

        expect($response->version)->toBe('1.0.0')
            ->and($response->protocolVersion)->toBe(3);
    });

    it('can convert to array', function () {
        $response = new GetStatusResponse(
            version: '2.0.0',
            protocolVersion: 5,
        );

        expect($response->toArray())->toBe([
            'version' => '2.0.0',
            'protocolVersion' => 5,
        ]);
    });

    it('implements Arrayable interface', function () {
        $response = new GetStatusResponse(version: '1.0.0', protocolVersion: 1);

        expect($response)->toBeInstanceOf(\Illuminate\Contracts\Support\Arrayable::class);
    });
});
