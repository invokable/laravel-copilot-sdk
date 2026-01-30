<?php

declare(strict_types=1);

use Revolution\Copilot\Types\UserInputRequest;

describe('UserInputRequest', function () {
    it('can be created with all fields', function () {
        $request = new UserInputRequest(
            question: 'What is your name?',
            choices: ['Alice', 'Bob', 'Charlie'],
            allowFreeform: true,
        );

        expect($request->question)->toBe('What is your name?')
            ->and($request->choices)->toBe(['Alice', 'Bob', 'Charlie'])
            ->and($request->allowFreeform)->toBeTrue();
    });

    it('can be created with minimal fields', function () {
        $request = new UserInputRequest(
            question: 'Enter your input',
        );

        expect($request->question)->toBe('Enter your input')
            ->and($request->choices)->toBeNull()
            ->and($request->allowFreeform)->toBeNull();
    });

    it('can be created from array', function () {
        $request = UserInputRequest::fromArray([
            'question' => 'Pick a color',
            'choices' => ['Red', 'Green', 'Blue'],
            'allowFreeform' => false,
        ]);

        expect($request->question)->toBe('Pick a color')
            ->and($request->choices)->toBe(['Red', 'Green', 'Blue'])
            ->and($request->allowFreeform)->toBeFalse();
    });

    it('can be created from array with minimal fields', function () {
        $request = UserInputRequest::fromArray([
            'question' => 'What do you want?',
        ]);

        expect($request->question)->toBe('What do you want?')
            ->and($request->choices)->toBeNull()
            ->and($request->allowFreeform)->toBeNull();
    });

    it('can convert to array with all fields', function () {
        $request = new UserInputRequest(
            question: 'Choose one',
            choices: ['A', 'B'],
            allowFreeform: true,
        );

        expect($request->toArray())->toBe([
            'question' => 'Choose one',
            'choices' => ['A', 'B'],
            'allowFreeform' => true,
        ]);
    });

    it('filters null values in toArray', function () {
        $request = new UserInputRequest(
            question: 'Simple question',
        );

        expect($request->toArray())->toBe([
            'question' => 'Simple question',
        ]);
    });

    it('implements Arrayable interface', function () {
        $request = new UserInputRequest(question: 'Test');

        expect($request)->toBeInstanceOf(\Illuminate\Contracts\Support\Arrayable::class);
    });
});
