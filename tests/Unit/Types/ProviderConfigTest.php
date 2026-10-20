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
            'maxPromptTokens' => 8192,
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
            ->and($config->maxPromptTokens)->toBe(8192)
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
            ->and($config->maxPromptTokens)->toBeNull()
            ->and($config->maxOutputTokens)->toBeNull();
    });

    it('uses empty string for missing baseUrl', function () {
        $config = ProviderConfig::fromArray([]);

        expect($config->baseUrl)->toBe('');
    });

    it('accepts maxInputTokens as alias for maxPromptTokens in fromArray', function () {
        $config = ProviderConfig::fromArray([
            'baseUrl' => 'https://api.example.com',
            'maxInputTokens' => 4096,
        ]);

        expect($config->maxPromptTokens)->toBe(4096);
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
            maxPromptTokens: 8192,
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

    it('emits maxPromptTokens in toArray', function () {
        $config = new ProviderConfig(baseUrl: 'https://api.test.com', maxPromptTokens: 8192);

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

    it('keeps bearer token callbacks out of the serialized config', function () {
        $callback = fn (array $args): string => $args['sessionId'];
        $config = ProviderConfig::fromArray([
            'baseUrl' => 'https://api.example.com',
            'bearerTokenProvider' => $callback,
        ]);

        expect($config->bearerTokenProvider)->toBeInstanceOf(Closure::class)
            ->and(($config->bearerTokenProvider)(['providerName' => 'default', 'sessionId' => 'session-1']))->toBe('session-1')
            ->and($config->toArray())->not->toHaveKey('bearerTokenProvider');
    });

    it('round-trips apiKeyCommand and omits it when absent', function () {
        $command = 'credential-helper --profile sdk';
        $config = ProviderConfig::fromArray([
            'baseUrl' => 'https://models.example.test',
            'apiKeyCommand' => $command,
        ]);

        expect($config->apiKeyCommand)->toBe($command)
            ->and($config->toArray())->toBe([
                'baseUrl' => 'https://models.example.test',
                'apiKeyCommand' => $command,
            ])
            ->and(ProviderConfig::fromArray($config->toArray())->apiKeyCommand)->toBe($command)
            ->and((new ProviderConfig(baseUrl: 'https://models.example.test'))->toArray())
            ->not->toHaveKey('apiKeyCommand');
    });

    it('keeps apiKeyCommand alongside other credentials and the bearer token callback', function () {
        $config = new ProviderConfig(
            baseUrl: 'https://models.example.test',
            apiKey: 'static-key',
            bearerTokenProvider: fn (): string => 'token',
            apiKeyCommand: 'credential-helper',
        );

        expect($config->toArray())->toMatchArray([
            'apiKey' => 'static-key',
            'apiKeyCommand' => 'credential-helper',
        ]);
    });

    it('rejects empty or blank apiKeyCommand strings', function (string $invalid) {
        $message = 'provider.apiKeyCommand must be a non-empty string.';

        expect(fn () => new ProviderConfig(baseUrl: 'https://models.example.test', apiKeyCommand: $invalid))
            ->toThrow(InvalidArgumentException::class, $message)
            ->and(fn () => ProviderConfig::fromArray(['baseUrl' => 'https://models.example.test', 'apiKeyCommand' => $invalid]))
            ->toThrow(InvalidArgumentException::class, $message);
    })->with(['empty' => [''], 'blank' => ["  \t "]]);

    it('rejects non-string apiKeyCommand values from arrays', function (mixed $invalid) {
        expect(fn () => ProviderConfig::fromArray(['baseUrl' => 'https://models.example.test', 'apiKeyCommand' => $invalid]))
            ->toThrow(InvalidArgumentException::class, 'provider.apiKeyCommand must be a non-empty string.');
    })->with(['array' => [['credential-helper']], 'int' => [123], 'bool' => [false]]);

    it('uses a stable error message that never includes the command', function () {
        $secret = 'echo super-secret-token';

        expect(fn () => ProviderConfig::fromArray(['baseUrl' => 'x', 'apiKeyCommand' => ['not-a-string', $secret]]))
            ->toThrow(InvalidArgumentException::class, 'provider.apiKeyCommand must be a non-empty string.');

        try {
            ProviderConfig::fromArray(['baseUrl' => 'x', 'apiKeyCommand' => '   ']);
        } catch (InvalidArgumentException $e) {
            expect($e->getMessage())->toBe('provider.apiKeyCommand must be a non-empty string.');
        }
    });

    it('implements Arrayable interface', function () {
        $config = new ProviderConfig(baseUrl: 'https://example.com');

        expect($config)->toBeInstanceOf(Arrayable::class);
    });
});
