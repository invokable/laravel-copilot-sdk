<?php

declare(strict_types=1);

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Types\Rpc\HostLocalServerOptions;

describe('HostLocalServerOptions', function () {
    it('can be created from array with all fields', function () {
        $options = HostLocalServerOptions::fromArray([
            'hostname' => '127.0.0.1',
            'port' => '4321',
            'token' => 'host-token',
            'requireConnectionToken' => false,
        ]);

        expect($options->hostname)->toBe('127.0.0.1')
            ->and($options->port)->toBe(4321)
            ->and($options->token)->toBe('host-token')
            ->and($options->requireConnectionToken)->toBeFalse();
    });

    it('defaults omitted fields to null', function () {
        $options = HostLocalServerOptions::fromArray([]);

        expect($options->hostname)->toBeNull()
            ->and($options->port)->toBeNull()
            ->and($options->token)->toBeNull()
            ->and($options->requireConnectionToken)->toBeNull();
    });

    it('converts populated options to an array', function () {
        $options = HostLocalServerOptions::fromArray([
            'hostname' => 'localhost',
            'port' => 8080,
            'token' => 'secret-token',
            'requireConnectionToken' => true,
        ]);

        expect($options->toArray())->toBe([
            'hostname' => 'localhost',
            'port' => 8080,
            'token' => 'secret-token',
            'requireConnectionToken' => true,
        ])->and($options)->toBeInstanceOf(Arrayable::class);
    });

    it('omits null options while preserving false values', function () {
        $options = HostLocalServerOptions::fromArray([
            'requireConnectionToken' => false,
        ]);

        expect($options->toArray())->toBe([
            'requireConnectionToken' => false,
        ]);
    });
});
