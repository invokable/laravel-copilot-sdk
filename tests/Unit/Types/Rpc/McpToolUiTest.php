<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\McpToolUiVisibility;
use Revolution\Copilot\Types\Rpc\McpToolUi;

describe('McpToolUi', function () {
    it('can be created from array with all fields', function () {
        $ui = McpToolUi::fromArray([
            'resourceUri' => 'ui://tool/resource',
            'visibility' => ['model', 'app'],
        ]);

        expect($ui->resourceUri)->toBe('ui://tool/resource')
            ->and($ui->visibility)->toBe([McpToolUiVisibility::Model, McpToolUiVisibility::App]);
    });

    it('can be created from empty array', function () {
        $ui = McpToolUi::fromArray([]);

        expect($ui->resourceUri)->toBeNull()
            ->and($ui->visibility)->toBeNull();
    });

    it('converts to array correctly', function () {
        $ui = new McpToolUi(
            resourceUri: 'ui://tool/resource',
            visibility: [McpToolUiVisibility::Model],
        );

        expect($ui->toArray())->toBe([
            'resourceUri' => 'ui://tool/resource',
            'visibility' => ['model'],
        ]);
    });

    it('filters null values from array', function () {
        $ui = new McpToolUi;

        expect($ui->toArray())->toBe([]);
    });
});
