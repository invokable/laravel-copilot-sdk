<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\McpTools;
use Revolution\Copilot\Types\Rpc\McpToolUi;

describe('McpTools', function () {
    it('can be created from array with all fields', function () {
        $tool = McpTools::fromArray([
            'name' => 'search',
            'description' => 'Search the web',
            'ui' => ['resourceUri' => 'ui://search', 'visibility' => ['model']],
        ]);

        expect($tool->name)->toBe('search')
            ->and($tool->description)->toBe('Search the web')
            ->and($tool->ui)->toBeInstanceOf(McpToolUi::class)
            ->and($tool->ui->resourceUri)->toBe('ui://search');
    });

    it('can be created from array without ui', function () {
        $tool = McpTools::fromArray(['name' => 'search']);

        expect($tool->name)->toBe('search')
            ->and($tool->description)->toBeNull()
            ->and($tool->ui)->toBeNull();
    });

    it('converts to array correctly', function () {
        $tool = new McpTools(
            name: 'search',
            description: 'Search the web',
            ui: new McpToolUi(resourceUri: 'ui://search'),
        );

        $array = $tool->toArray();

        expect($array['name'])->toBe('search')
            ->and($array['description'])->toBe('Search the web')
            ->and($array['ui'])->toBe(['resourceUri' => 'ui://search']);
    });

    it('filters null values from array', function () {
        $tool = new McpTools(name: 'search');

        expect($tool->toArray())->toBe(['name' => 'search']);
    });
});
