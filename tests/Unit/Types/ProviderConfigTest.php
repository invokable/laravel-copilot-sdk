<?php

declare(strict_types=1);

use Illuminate\Contracts\Support\Arrayable;
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
            'modelId' => 'gpt-4o',
            'wireModel' => 'gpt-4o-2024-08-06',
            'maxInputTokens' => 8192,
            'maxOutputTokens' => 4096,
        ]);

        expect($config->baseUrl)->toBe('https://api.example.com')
            ->and($config->type)->toBe('openai')
            ->and($config->wireApi)->toBe('completions')
            ->and($config->apiKey)->toBe('sk-test-key')
            ->and($config->bearerToken)->toBe('bearer-token')
            ->and($config->azure)->toBe(['deployment' => 'my-deployment'])
            ->and($config->modelId)->toBe('gpt-4o')
            ->and($config->wireModel)->toBe('gpt-4o-2024-08-06')
            ->and($config->maxInputTokens)->toBe(8192)
            ->and($config->maxOutputTokens)->toBe(4096);
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
            ->and($config->azure)->toBeNull()
            ->and($config->modelId)->toBeNull()
            ->and($config->wireModel)->toBeNull()
            ->and($config->maxInputTokens)->toBeNull()
            ->and($config->maxOutputTokens)->toBeNull();
    });

    it('uses empty string for missing baseUrl', function () {
        $config = ProviderConfig::fromArray([]);

        expect($config->baseUrl)->toBe('');
    });

    it('accepts maxPromptTokens as alias for maxInputTokens in fromArray', function () {
        $config = ProviderConfig::fromArray([
            'baseUrl' => 'https://api.example.com',
            'maxPromptTokens' => 4096,
        ]);

        expect($config->maxInputTokens)->toBe(4096);
    });

    it('can convert to array with all fields', function () {
        $config = new ProviderConfig(
            baseUrl: 'https://api.test.com',
            type: 'azure',
            wireApi: 'chat',
            apiKey: 'test-key',
            bearerToken: 'test-token',
            azure: ['resource' => 'my-resource'],
            modelId: 'gpt-4o',
            wireModel: 'my-deployment',
            maxInputTokens: 8192,
            maxOutputTokens: 4096,
        );

        expect($config->toArray())->toBe([
            'baseUrl' => 'https://api.test.com',
            'type' => 'azure',
            'wireApi' => 'chat',
            'apiKey' => 'test-key',
            'bearerToken' => 'test-token',
            'azure' => ['resource' => 'my-resource'],
            'modelId' => 'gpt-4o',
            'wireModel' => 'my-deployment',
            'maxPromptTokens' => 8192,
            'maxOutputTokens' => 4096,
        ]);
    });

    it('remaps maxInputTokens to maxPromptTokens in toArray', function () {
        $config = new ProviderConfig(baseUrl: 'https://api.test.com', maxInputTokens: 8192);

        $array = $config->toArray();

        expect($array)->toHaveKey('maxPromptTokens', 8192)
            ->and($array)->not->toHaveKey('maxInputTokens');
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

        expect($config)->toBeInstanceOf(Arrayable::class);
    });
});
