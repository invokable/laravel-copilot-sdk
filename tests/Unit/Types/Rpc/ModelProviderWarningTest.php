<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\ModelProviderWarning;

describe('ModelProviderWarning', function () {
    it('can be created from array with all fields', function () {
        $warning = ModelProviderWarning::fromArray([
            'code' => 'unsupported-model',
            'message' => 'This model is not supported.',
        ]);

        expect($warning->code)->toBe('unsupported-model')
            ->and($warning->message)->toBe('This model is not supported.');
    });

    it('defaults missing fields to empty strings', function () {
        $warning = ModelProviderWarning::fromArray([]);

        expect($warning->code)->toBe('')
            ->and($warning->message)->toBe('');
    });

    it('converts its fields to an array', function () {
        $warning = new ModelProviderWarning(
            code: 'unsupported-model',
            message: 'This model is not supported.',
        );

        expect($warning->toArray())->toBe([
            'code' => 'unsupported-model',
            'message' => 'This model is not supported.',
        ]);
    });
});
