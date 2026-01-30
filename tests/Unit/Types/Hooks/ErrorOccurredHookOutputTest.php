<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Hooks\ErrorOccurredHookOutput;

describe('ErrorOccurredHookOutput', function () {
    it('can be created with all fields', function () {
        $output = new ErrorOccurredHookOutput(
            suppressOutput: true,
            errorHandling: 'retry',
            retryCount: 3,
            userNotification: 'An error occurred, retrying...',
        );

        expect($output->suppressOutput)->toBeTrue()
            ->and($output->errorHandling)->toBe('retry')
            ->and($output->retryCount)->toBe(3)
            ->and($output->userNotification)->toBe('An error occurred, retrying...');
    });

    it('can be created with minimal fields', function () {
        $output = new ErrorOccurredHookOutput;

        expect($output->suppressOutput)->toBeNull()
            ->and($output->errorHandling)->toBeNull()
            ->and($output->retryCount)->toBeNull()
            ->and($output->userNotification)->toBeNull();
    });

    it('can be created with skip handling', function () {
        $output = new ErrorOccurredHookOutput(
            errorHandling: 'skip',
            userNotification: 'Skipping this operation',
        );

        expect($output->errorHandling)->toBe('skip');
    });

    it('can be created with abort handling', function () {
        $output = new ErrorOccurredHookOutput(
            errorHandling: 'abort',
            userNotification: 'Operation aborted',
        );

        expect($output->errorHandling)->toBe('abort');
    });

    it('can be created from array', function () {
        $output = ErrorOccurredHookOutput::fromArray([
            'suppressOutput' => false,
            'errorHandling' => 'retry',
            'retryCount' => 5,
            'userNotification' => 'Retrying...',
        ]);

        expect($output->suppressOutput)->toBeFalse()
            ->and($output->errorHandling)->toBe('retry')
            ->and($output->retryCount)->toBe(5)
            ->and($output->userNotification)->toBe('Retrying...');
    });

    it('can convert to array with all fields', function () {
        $output = new ErrorOccurredHookOutput(
            suppressOutput: true,
            errorHandling: 'skip',
            retryCount: 0,
            userNotification: 'Notice',
        );

        expect($output->toArray())->toBe([
            'suppressOutput' => true,
            'errorHandling' => 'skip',
            'retryCount' => 0,
            'userNotification' => 'Notice',
        ]);
    });

    it('filters null values in toArray', function () {
        $output = new ErrorOccurredHookOutput(
            errorHandling: 'abort',
        );

        expect($output->toArray())->toBe([
            'errorHandling' => 'abort',
        ]);
    });

    it('implements Arrayable interface', function () {
        $output = new ErrorOccurredHookOutput;

        expect($output)->toBeInstanceOf(\Illuminate\Contracts\Support\Arrayable::class);
    });
});
