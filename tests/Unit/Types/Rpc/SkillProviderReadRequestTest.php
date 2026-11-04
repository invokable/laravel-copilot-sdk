<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\SkillProviderReadRequest;

describe('SkillProviderReadRequest', function () {
    it('can be created from array with all fields', function () {
        $request = SkillProviderReadRequest::fromArray([
            'sessionId' => 'session-123',
            'name' => 'laravel-guidelines',
        ]);

        expect($request->sessionId)->toBe('session-123')
            ->and($request->name)->toBe('laravel-guidelines');
    });

    it('converts to array', function () {
        $request = new SkillProviderReadRequest(
            sessionId: 'session-123',
            name: 'laravel-guidelines',
        );

        expect($request->toArray())->toBe([
            'sessionId' => 'session-123',
            'name' => 'laravel-guidelines',
        ]);
    });

    it('requires a session id when created from array', function () {
        expect(fn () => SkillProviderReadRequest::fromArray([
            'name' => 'laravel-guidelines',
        ]))->toThrow(InvalidArgumentException::class);
    });

    it('requires a name when created from array', function () {
        expect(fn () => SkillProviderReadRequest::fromArray([
            'sessionId' => 'session-123',
        ]))->toThrow(InvalidArgumentException::class);
    });
});
