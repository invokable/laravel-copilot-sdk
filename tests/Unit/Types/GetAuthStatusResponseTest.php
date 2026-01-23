<?php

declare(strict_types=1);

use Revolution\Copilot\Types\GetAuthStatusResponse;

describe('GetAuthStatusResponse', function () {
    it('can be created from array with all fields', function () {
        $response = GetAuthStatusResponse::fromArray([
            'isAuthenticated' => true,
            'authType' => 'token',
            'host' => 'https://github.com',
            'login' => 'testuser',
            'statusMessage' => 'Authenticated',
        ]);

        expect($response->isAuthenticated)->toBeTrue()
            ->and($response->authType)->toBe('token')
            ->and($response->host)->toBe('https://github.com')
            ->and($response->login)->toBe('testuser')
            ->and($response->statusMessage)->toBe('Authenticated');
    });

    it('can be created from array with minimal fields', function () {
        $response = GetAuthStatusResponse::fromArray([
            'isAuthenticated' => false,
        ]);

        expect($response->isAuthenticated)->toBeFalse()
            ->and($response->authType)->toBeNull()
            ->and($response->host)->toBeNull()
            ->and($response->login)->toBeNull()
            ->and($response->statusMessage)->toBeNull();
    });

    it('can convert to array with all fields', function () {
        $response = new GetAuthStatusResponse(
            isAuthenticated: true,
            authType: 'token',
            host: 'https://github.com',
            login: 'testuser',
            statusMessage: 'OK',
        );

        expect($response->toArray())->toBe([
            'isAuthenticated' => true,
            'authType' => 'token',
            'host' => 'https://github.com',
            'login' => 'testuser',
            'statusMessage' => 'OK',
        ]);
    });

    it('filters null values in toArray', function () {
        $response = new GetAuthStatusResponse(
            isAuthenticated: false,
        );

        expect($response->toArray())->toBe([
            'isAuthenticated' => false,
        ]);
    });

    it('implements Arrayable interface', function () {
        $response = new GetAuthStatusResponse(isAuthenticated: true);

        expect($response)->toBeInstanceOf(\Illuminate\Contracts\Support\Arrayable::class);
    });
});
