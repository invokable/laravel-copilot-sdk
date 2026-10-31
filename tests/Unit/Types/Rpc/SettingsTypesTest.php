<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\SettingsRedactValueForDisplayRequest;
use Revolution\Copilot\Types\Rpc\SettingsRedactValueForDisplayResult;
use Revolution\Copilot\Types\Rpc\SettingsRemoveRawKeyRequest;
use Revolution\Copilot\Types\Rpc\SettingsRemoveRawKeyResult;

describe('settings types', function () {
    it('round-trips the removeRawKey request', function () {
        $request = SettingsRemoveRawKeyRequest::fromArray([
            'filePath' => '/home/user/.copilot/settings.json',
            'rawKey' => 'oldKey',
        ]);

        expect($request->filePath)->toBe('/home/user/.copilot/settings.json')
            ->and($request->rawKey)->toBe('oldKey')
            ->and($request->toArray())->toBe([
                'filePath' => '/home/user/.copilot/settings.json',
                'rawKey' => 'oldKey',
            ]);
    });

    it('round-trips the removeRawKey result', function () {
        $result = SettingsRemoveRawKeyResult::fromArray(['status' => 'invalid']);

        expect($result->status)->toBe('invalid')
            ->and($result->toArray())->toBe(['status' => 'invalid']);
    });

    it('keeps nested JSON values and the base path for redaction', function () {
        $request = SettingsRedactValueForDisplayRequest::fromArray([
            'value' => ['token' => 'abc', 'list' => [1, 2]],
            'basePath' => ['mcpServers', 'github'],
        ]);

        expect($request->value)->toBe(['token' => 'abc', 'list' => [1, 2]])
            ->and($request->basePath)->toBe(['mcpServers', 'github'])
            ->and($request->toArray())->toBe([
                'value' => ['token' => 'abc', 'list' => [1, 2]],
                'basePath' => ['mcpServers', 'github'],
            ]);
    });

    it('defaults redaction input to null and an empty base path', function () {
        $request = SettingsRedactValueForDisplayRequest::fromArray([]);

        expect($request->value)->toBeNull()
            ->and($request->basePath)->toBe([]);
    });

    it('round-trips the redaction result', function () {
        $result = SettingsRedactValueForDisplayResult::fromArray(['value' => 'redacted']);

        expect($result->value)->toBe('redacted')
            ->and($result->toArray())->toBe(['value' => 'redacted']);
    });
});
