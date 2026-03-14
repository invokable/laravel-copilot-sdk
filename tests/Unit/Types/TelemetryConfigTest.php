<?php

declare(strict_types=1);

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Types\TelemetryConfig;

describe('TelemetryConfig', function () {
    it('can be created from array with all fields', function () {
        $config = TelemetryConfig::fromArray([
            'otlpEndpoint' => 'https://otel.example.com',
            'filePath' => '/var/log/traces.jsonl',
            'exporterType' => 'otlp-http',
            'sourceName' => 'my-app',
            'captureContent' => true,
        ]);

        expect($config->otlpEndpoint)->toBe('https://otel.example.com')
            ->and($config->filePath)->toBe('/var/log/traces.jsonl')
            ->and($config->exporterType)->toBe('otlp-http')
            ->and($config->sourceName)->toBe('my-app')
            ->and($config->captureContent)->toBeTrue();
    });

    it('can be created from empty array using defaults', function () {
        $config = TelemetryConfig::fromArray([]);

        expect($config->otlpEndpoint)->toBeNull()
            ->and($config->filePath)->toBeNull()
            ->and($config->exporterType)->toBeNull()
            ->and($config->sourceName)->toBeNull()
            ->and($config->captureContent)->toBeNull();
    });

    it('casts captureContent to bool from truthy value', function () {
        $config = TelemetryConfig::fromArray(['captureContent' => 1]);

        expect($config->captureContent)->toBeTrue();
    });

    it('casts captureContent to bool from falsy value', function () {
        $config = TelemetryConfig::fromArray(['captureContent' => 0]);

        expect($config->captureContent)->toBeFalse();
    });

    it('converts to array filtering null values', function () {
        $config = new TelemetryConfig(
            otlpEndpoint: 'https://otel.example.com',
            sourceName: 'my-app',
        );

        expect($config->toArray())->toBe([
            'otlpEndpoint' => 'https://otel.example.com',
            'sourceName' => 'my-app',
        ]);
    });

    it('converts to array with all fields', function () {
        $config = new TelemetryConfig(
            otlpEndpoint: 'https://otel.example.com',
            filePath: '/var/log/traces.jsonl',
            exporterType: 'file',
            sourceName: 'my-app',
            captureContent: false,
        );

        expect($config->toArray())->toBe([
            'otlpEndpoint' => 'https://otel.example.com',
            'filePath' => '/var/log/traces.jsonl',
            'exporterType' => 'file',
            'sourceName' => 'my-app',
            'captureContent' => false,
        ]);
    });

    it('returns empty array when all fields are null', function () {
        $config = new TelemetryConfig;

        expect($config->toArray())->toBe([]);
    });

    it('implements Arrayable interface', function () {
        expect(new TelemetryConfig)->toBeInstanceOf(Arrayable::class);
    });

    it('builds env vars with OTEL enabled flag', function () {
        $config = new TelemetryConfig;
        $env = $config->toEnv();

        expect($env)->toHaveKey('COPILOT_OTEL_ENABLED', 'true');
    });

    it('builds env vars with all fields set', function () {
        $config = new TelemetryConfig(
            otlpEndpoint: 'https://otel.example.com',
            filePath: '/var/log/traces.jsonl',
            exporterType: 'otlp-http',
            sourceName: 'my-app',
            captureContent: true,
        );

        $env = $config->toEnv();

        expect($env)->toHaveKey('COPILOT_OTEL_ENABLED', 'true')
            ->and($env)->toHaveKey('OTEL_EXPORTER_OTLP_ENDPOINT', 'https://otel.example.com')
            ->and($env)->toHaveKey('COPILOT_OTEL_FILE_EXPORTER_PATH', '/var/log/traces.jsonl')
            ->and($env)->toHaveKey('COPILOT_OTEL_EXPORTER_TYPE', 'otlp-http')
            ->and($env)->toHaveKey('COPILOT_OTEL_SOURCE_NAME', 'my-app')
            ->and($env)->toHaveKey('OTEL_INSTRUMENTATION_GENAI_CAPTURE_MESSAGE_CONTENT', 'true');
    });

    it('sets captureContent env var to false string when false', function () {
        $config = new TelemetryConfig(captureContent: false);

        expect($config->toEnv())->toHaveKey('OTEL_INSTRUMENTATION_GENAI_CAPTURE_MESSAGE_CONTENT', 'false');
    });

    it('omits optional env vars when fields are null', function () {
        $config = new TelemetryConfig(sourceName: 'my-app');
        $env = $config->toEnv();

        expect($env)->toHaveKey('COPILOT_OTEL_SOURCE_NAME', 'my-app')
            ->and($env)->not->toHaveKey('OTEL_EXPORTER_OTLP_ENDPOINT')
            ->and($env)->not->toHaveKey('COPILOT_OTEL_FILE_EXPORTER_PATH');
    });
});
