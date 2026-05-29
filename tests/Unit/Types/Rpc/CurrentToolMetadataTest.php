<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\CurrentToolMetadata;
use Revolution\Copilot\Types\Rpc\ToolsGetCurrentMetadataResult;

describe('CurrentToolMetadata', function () {
    it('can be created from array with all fields', function () {
        $metadata = CurrentToolMetadata::fromArray([
            'name' => 'bash',
            'description' => 'Execute bash commands',
            'namespacedName' => 'mcp:bash',
            'mcpServerName' => 'my-server',
            'mcpToolName' => 'raw_bash',
            'input_schema' => ['type' => 'object'],
            'deferLoading' => false,
        ]);

        expect($metadata->name)->toBe('bash')
            ->and($metadata->description)->toBe('Execute bash commands')
            ->and($metadata->namespacedName)->toBe('mcp:bash')
            ->and($metadata->mcpServerName)->toBe('my-server')
            ->and($metadata->mcpToolName)->toBe('raw_bash')
            ->and($metadata->inputSchema)->toBe(['type' => 'object'])
            ->and($metadata->deferLoading)->toBeFalse();
    });

    it('can be created with minimal fields', function () {
        $metadata = CurrentToolMetadata::fromArray([
            'name' => 'bash',
            'description' => 'Execute bash commands',
        ]);

        expect($metadata->name)->toBe('bash')
            ->and($metadata->namespacedName)->toBeNull()
            ->and($metadata->mcpServerName)->toBeNull()
            ->and($metadata->deferLoading)->toBeNull();
    });

    it('converts to array correctly', function () {
        $metadata = new CurrentToolMetadata(
            name: 'bash',
            description: 'Execute bash commands',
        );

        $array = $metadata->toArray();

        expect($array)->toHaveKey('name', 'bash')
            ->and($array)->toHaveKey('description', 'Execute bash commands')
            ->and($array)->not->toHaveKey('namespacedName');
    });
});

describe('ToolsGetCurrentMetadataResult', function () {
    it('can be created with tools', function () {
        $result = ToolsGetCurrentMetadataResult::fromArray([
            'tools' => [
                ['name' => 'bash', 'description' => 'Execute bash'],
            ],
        ]);

        expect($result->tools)->toHaveCount(1)
            ->and($result->tools[0])->toBeInstanceOf(CurrentToolMetadata::class)
            ->and($result->tools[0]->name)->toBe('bash');
    });

    it('can be created with null tools', function () {
        $result = ToolsGetCurrentMetadataResult::fromArray(['tools' => null]);

        expect($result->tools)->toBeNull();
    });

    it('can be created from empty array', function () {
        $result = ToolsGetCurrentMetadataResult::fromArray([]);

        expect($result->tools)->toBeNull();
    });

    it('converts to array correctly', function () {
        $result = ToolsGetCurrentMetadataResult::fromArray([
            'tools' => [['name' => 'bash', 'description' => 'Execute bash']],
        ]);

        $array = $result->toArray();

        expect($array['tools'])->toHaveCount(1)
            ->and($array['tools'][0]['name'])->toBe('bash');
    });
});
