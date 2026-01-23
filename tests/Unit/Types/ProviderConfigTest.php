<?php

declare(strict_types=1);

use Revolution\Copilot\Types\ProviderConfig;

describe('ProviderConfig', function () {
    it('can be created from array with all fields', function () {
        $config = ProviderConfig::fromArray([
            'baseUrl' => 'https://api.example.com',
            'type' => 'openai',
            'wireApi' => 'completions',
            'apiKey' => 'sk-test-key',
            'bearerToken' => 'bearer-token',
            'azure' => ['deployment' => 'my-deployment'],
        ]);

        expect($config->baseUrl)->toBe('https://api.example.com')
            ->and($config->type)->toBe('openai')
            ->and($config->wireApi)->toBe('completions')
            ->and($config->apiKey)->toBe('sk-test-key')
            ->and($config->bearerToken)->toBe('bearer-token')
            ->and($config->azure)->toBe(['deployment' => 'my-deployment']);
    });

    it('can be created from array with minimal fields', function () {
        $config = ProviderConfig::fromArray([
            'baseUrl' => 'http://localhost:11434',
        ]);

        expect($config->baseUrl)->toBe('http://localhost:11434')
            ->and($config->type)->toBeNull()
            ->and($config->wireApi)->toBeNull()
            ->and($config->apiKey)->toBeNull()
            ->and($config->bearerToken)->toBeNull()
            ->and($config->azure)->toBeNull();
    });

    it('uses empty string for missing baseUrl', function () {
        $config = ProviderConfig::fromArray([]);

        expect($config->baseUrl)->toBe('');
    });

    it('can convert to array with all fields', function () {
        $config = new ProviderConfig(
            baseUrl: 'https://api.test.com',
            type: 'azure',
            wireApi: 'chat',
            apiKey: 'test-key',
            bearerToken: 'test-token',
            azure: ['resource' => 'my-resource'],
        );

        expect($config->toArray())->toBe([
            'baseUrl' => 'https://api.test.com',
            'type' => 'azure',
            'wireApi' => 'chat',
            'apiKey' => 'test-key',
            'bearerToken' => 'test-token',
            'azure' => ['resource' => 'my-resource'],
        ]);
    });

    it('filters null values in toArray', function () {
        $config = new ProviderConfig(
            baseUrl: 'http://localhost:11434',
        );

        expect($config->toArray())->toBe([
            'baseUrl' => 'http://localhost:11434',
        ]);
    });

    it('implements Arrayable interface', function () {
        $config = new ProviderConfig(baseUrl: 'https://example.com');

        expect($config)->toBeInstanceOf(\Illuminate\Contracts\Support\Arrayable::class);
    });
});
