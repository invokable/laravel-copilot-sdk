<?php

declare(strict_types=1);

use Revolution\Copilot\Types\UserInputResponse;

describe('UserInputResponse', function () {
    it('can be created with all fields', function () {
        $response = new UserInputResponse(
            answer: 'My answer',
            wasFreeform: true,
        );

        expect($response->answer)->toBe('My answer')
            ->and($response->wasFreeform)->toBeTrue();
    });

    it('can be created with default wasFreeform', function () {
        $response = new UserInputResponse(
            answer: 'Selected choice',
        );

        expect($response->answer)->toBe('Selected choice')
            ->and($response->wasFreeform)->toBeFalse();
    });

    it('can be created from array', function () {
        $response = UserInputResponse::fromArray([
            'answer' => 'User typed this',
            'wasFreeform' => true,
        ]);

        expect($response->answer)->toBe('User typed this')
            ->and($response->wasFreeform)->toBeTrue();
    });

    it('can be created from array with defaults', function () {
        $response = UserInputResponse::fromArray([
            'answer' => 'Choice A',
        ]);

        expect($response->answer)->toBe('Choice A')
            ->and($response->wasFreeform)->toBeFalse();
    });

    it('can convert to array', function () {
        $response = new UserInputResponse(
            answer: 'Test answer',
            wasFreeform: true,
        );

        expect($response->toArray())->toBe([
            'answer' => 'Test answer',
            'wasFreeform' => true,
        ]);
    });

    it('includes wasFreeform false in toArray', function () {
        $response = new UserInputResponse(
            answer: 'Selected',
            wasFreeform: false,
        );

        expect($response->toArray())->toBe([
            'answer' => 'Selected',
            'wasFreeform' => false,
        ]);
    });

    it('implements Arrayable interface', function () {
        $response = new UserInputResponse(answer: 'Test');

        expect($response)->toBeInstanceOf(\Illuminate\Contracts\Support\Arrayable::class);
    });
});
