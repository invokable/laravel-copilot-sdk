<?php

declare(strict_types=1);

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Types\EntraAuthConfig;

describe('EntraAuthConfig', function () {
    it('round-trips the host application client ID', function () {
        $data = ['clientId' => 'host-application-id'];
        $config = EntraAuthConfig::fromArray($data);

        expect($config)->toBeInstanceOf(Arrayable::class)
            ->and($config->clientId)->toBe('host-application-id')
            ->and($config->toArray())->toBe($data);
    });

    it('rejects missing, non-string, and blank client IDs', function () {
        expect(fn () => EntraAuthConfig::fromArray([]))
            ->toThrow(InvalidArgumentException::class, 'entra.clientId must be a non-empty string.')
            ->and(fn () => EntraAuthConfig::fromArray(['clientId' => 123]))
            ->toThrow(InvalidArgumentException::class, 'entra.clientId must be a non-empty string.')
            ->and(fn () => new EntraAuthConfig(''))
            ->toThrow(InvalidArgumentException::class, 'entra.clientId must be a non-empty string.')
            ->and(fn () => new EntraAuthConfig(" \t "))
            ->toThrow(InvalidArgumentException::class, 'entra.clientId must be a non-empty string.');
    });
});
