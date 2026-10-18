<?php

declare(strict_types=1);

use Revolution\Copilot\Types\ProviderConfig;

it('round trips provider model-provider metadata', function () {
    $config = ProviderConfig::fromArray([
        'baseUrl' => 'https://api.example.com',
        'modelProvider' => 'azure-openai',
    ]);

    expect($config->modelProvider)->toBe('azure-openai')
        ->and($config->toArray()['modelProvider'])->toBe('azure-openai');
});
