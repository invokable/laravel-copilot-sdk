<?php

declare(strict_types=1);

use Revolution\Copilot\Types\ExtensionInfo;

describe('ExtensionInfo', function () {
    it('can be created from array', function () {
        $info = ExtensionInfo::fromArray([
            'source' => 'github-app',
            'name' => 'test-extension',
        ]);

        expect($info->source)->toBe('github-app')
            ->and($info->name)->toBe('test-extension');
    });

    it('converts to array correctly', function () {
        $info = new ExtensionInfo(
            source: 'custom-provider',
            name: 'my-extension'
        );

        expect($info->toArray())->toBe([
            'source' => 'custom-provider',
            'name' => 'my-extension',
        ]);
    });
});
