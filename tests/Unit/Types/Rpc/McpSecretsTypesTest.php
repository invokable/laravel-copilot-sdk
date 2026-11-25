<?php

declare(strict_types=1);

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Types\Rpc\McpSecretsDeleteParams;
use Revolution\Copilot\Types\Rpc\McpSecretsDeleteServerParams;
use Revolution\Copilot\Types\Rpc\McpSecretsSaveParams;
use Revolution\Copilot\Types\Rpc\McpSecretsSaveResult;

describe('MCP secrets types', function () {
    it('round trips request fields with and without a config directory', function (string $class, array $fields) {
        foreach ([$fields, [...$fields, 'configDir' => '/config']] as $data) {
            $params = $class::fromArray($data);

            expect($params)->toBeInstanceOf(Arrayable::class)
                ->and($params->toArray())->toBe($data);
        }
    })->with([
        [McpSecretsSaveParams::class, ['secretId' => 'server:key', 'value' => 'example-value']],
        [McpSecretsDeleteParams::class, ['secretId' => 'server:key']],
        [McpSecretsDeleteServerParams::class, ['serverName' => 'server']],
    ]);

    it('round trips both storage backend results', function (bool $usedKeychain) {
        $result = McpSecretsSaveResult::fromArray(['usedKeychain' => $usedKeychain]);

        expect($result)->toBeInstanceOf(Arrayable::class)
            ->and($result->usedKeychain)->toBe($usedKeychain)
            ->and($result->toArray())->toBe(['usedKeychain' => $usedKeychain]);
    })->with([true, false]);
});
