<?php

declare(strict_types=1);

use Revolution\Copilot\Types\CanvasProviderIdentity;

describe('CanvasProviderIdentity', function () {
    it('can be created with id only', function () {
        $identity = new CanvasProviderIdentity(id: 'app:builtin:win1');

        expect($identity->id)->toBe('app:builtin:win1')
            ->and($identity->name)->toBeNull();
    });

    it('can be created with id and name', function () {
        $identity = new CanvasProviderIdentity(id: 'app:builtin:win1', name: 'Main Window');

        expect($identity->id)->toBe('app:builtin:win1')
            ->and($identity->name)->toBe('Main Window');
    });

    it('can be created from array', function () {
        $identity = CanvasProviderIdentity::fromArray(['id' => 'my-id', 'name' => 'My Provider']);

        expect($identity->id)->toBe('my-id')
            ->and($identity->name)->toBe('My Provider');
    });

    it('converts to array excluding null name', function () {
        $identity = new CanvasProviderIdentity(id: 'my-id');

        expect($identity->toArray())->toBe(['id' => 'my-id']);
    });

    it('includes name in array when set', function () {
        $identity = new CanvasProviderIdentity(id: 'my-id', name: 'My Name');

        expect($identity->toArray())->toBe(['id' => 'my-id', 'name' => 'My Name']);
    });
});
