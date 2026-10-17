<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\ModelCallProviderType;
use Revolution\Copilot\Enums\ModelCallRequestBodyCompressionFallback;

test('model-call provider types are fixed categories', function () {
    expect(array_column(ModelCallProviderType::cases(), 'value'))->toBe([
        'openai',
        'azure',
        'anthropic',
        'local_model',
        'winml',
        'other',
    ]);
});

test('request-body compression fallback supports the official outcomes', function () {
    expect(array_column(ModelCallRequestBodyCompressionFallback::cases(), 'value'))->toBe([
        'retried',
        'disabled',
    ]);
});
