<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\LoginProviderKind;
use Revolution\Copilot\Types\Rpc\ProviderDescriptor;

describe('ProviderDescriptor', function () {
    it('hydrates known provider kinds and serializes their wire values', function () {
        $descriptor = ProviderDescriptor::fromArray([
            'kind' => 'entra',
            'label' => 'Microsoft Entra',
            'available' => true,
        ]);

        expect($descriptor->kind)->toBe(LoginProviderKind::ENTRA)
            ->and($descriptor->label)->toBe('Microsoft Entra')
            ->and($descriptor->available)->toBeTrue()
            ->and($descriptor->toArray())->toBe([
                'kind' => 'entra',
                'label' => 'Microsoft Entra',
                'available' => true,
            ]);
    });

    it('preserves unknown provider kinds as strings', function () {
        $descriptor = ProviderDescriptor::fromArray([
            'kind' => 'future-provider',
            'label' => 'Future provider',
            'available' => false,
        ]);

        expect($descriptor->kind)->toBe('future-provider')
            ->and($descriptor->toArray())->toBe([
                'kind' => 'future-provider',
                'label' => 'Future provider',
                'available' => false,
            ]);
    });

    it('uses defaults for missing fields', function () {
        $descriptor = ProviderDescriptor::fromArray([]);

        expect($descriptor->kind)->toBe('')
            ->and($descriptor->label)->toBe('')
            ->and($descriptor->available)->toBeFalse()
            ->and($descriptor->toArray())->toBe([
                'kind' => '',
                'label' => '',
                'available' => false,
            ]);
    });
});
