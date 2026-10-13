<?php

declare(strict_types=1);

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Types\Rpc\SessionFsSetProviderCapabilities;
use Revolution\Copilot\Types\SessionFsConfig;

describe('SessionFsConfig', function () {
    it('is Arrayable and round-trips through fromArray and toArray', function () {
        $data = [
            'initialCwd' => '/workspace',
            'sessionStatePath' => '/state',
            'conventions' => 'windows',
            'capabilities' => ['sqlite' => true, 'binary' => false],
        ];
        $config = SessionFsConfig::fromArray($data);

        expect($config)->toBeInstanceOf(Arrayable::class)
            ->and($config->initialCwd)->toBe('/workspace')
            ->and($config->capabilities)->toBeInstanceOf(SessionFsSetProviderCapabilities::class)
            ->and($config->supportsSqlite())->toBeTrue()
            ->and($config->supportsBinary())->toBeFalse()
            ->and($config->toArray())->toBe($data);
    });

    it('omits capabilities when none are declared', function () {
        $config = SessionFsConfig::fromArray(['initialCwd' => '/w', 'sessionStatePath' => '/s', 'conventions' => 'posix']);

        expect($config->capabilities)->toBeNull()
            ->and($config->supportsSqlite())->toBeFalse()
            ->and($config->toArray())->toBe(['initialCwd' => '/w', 'sessionStatePath' => '/s', 'conventions' => 'posix']);
    });

    it('accepts capabilities as an Arrayable object', function () {
        $config = SessionFsConfig::fromArray([
            'initialCwd' => '/w', 'sessionStatePath' => '/s', 'conventions' => 'posix',
            'capabilities' => new SessionFsSetProviderCapabilities(binary: true),
        ]);

        expect($config->supportsBinary())->toBeTrue();
    });

    it('validates required fields and conventions', function () {
        $valid = SessionFsConfig::fromArray(['initialCwd' => '/w', 'sessionStatePath' => '/s', 'conventions' => 'posix']);
        $valid->validate();

        expect(fn () => SessionFsConfig::fromArray([])->validate())->toThrow(InvalidArgumentException::class, 'sessionFs.initialCwd is required')
            ->and(fn () => SessionFsConfig::fromArray(['initialCwd' => '/w'])->validate())->toThrow(InvalidArgumentException::class, 'sessionFs.sessionStatePath is required')
            ->and(fn () => SessionFsConfig::fromArray(['initialCwd' => '/w', 'sessionStatePath' => '/s'])->validate())
            ->toThrow(InvalidArgumentException::class, "sessionFs.conventions must be either 'windows' or 'posix'")
            ->and(fn () => SessionFsConfig::fromArray(['initialCwd' => '/w', 'sessionStatePath' => '/s', 'conventions' => 'POSIX'])->validate())
            ->toThrow(InvalidArgumentException::class, "'windows' or 'posix'");
    });
});
