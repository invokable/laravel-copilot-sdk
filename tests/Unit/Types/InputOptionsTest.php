<?php

declare(strict_types=1);

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Types\InputOptions;

describe('InputOptions', function () {
    it('can be created with all fields', function () {
        $opts = new InputOptions(
            title: 'Release Name',
            description: 'Enter the name for this release',
            minLength: 1,
            maxLength: 100,
            format: 'uri',
            default: 'v1.0.0',
        );

        expect($opts->title)->toBe('Release Name')
            ->and($opts->description)->toBe('Enter the name for this release')
            ->and($opts->minLength)->toBe(1)
            ->and($opts->maxLength)->toBe(100)
            ->and($opts->format)->toBe('uri')
            ->and($opts->default)->toBe('v1.0.0');
    });

    it('can be created with no fields', function () {
        $opts = new InputOptions;

        expect($opts->title)->toBeNull()
            ->and($opts->description)->toBeNull()
            ->and($opts->minLength)->toBeNull()
            ->and($opts->maxLength)->toBeNull()
            ->and($opts->format)->toBeNull()
            ->and($opts->default)->toBeNull();
    });

    it('can be created from array with all fields', function () {
        $opts = InputOptions::fromArray([
            'title' => 'Email',
            'description' => 'Enter your email',
            'minLength' => 5,
            'maxLength' => 255,
            'format' => 'email',
            'default' => 'user@example.com',
        ]);

        expect($opts->title)->toBe('Email')
            ->and($opts->description)->toBe('Enter your email')
            ->and($opts->minLength)->toBe(5)
            ->and($opts->maxLength)->toBe(255)
            ->and($opts->format)->toBe('email')
            ->and($opts->default)->toBe('user@example.com');
    });

    it('can be created from empty array', function () {
        $opts = InputOptions::fromArray([]);

        expect($opts->title)->toBeNull()
            ->and($opts->description)->toBeNull()
            ->and($opts->minLength)->toBeNull()
            ->and($opts->maxLength)->toBeNull()
            ->and($opts->format)->toBeNull()
            ->and($opts->default)->toBeNull();
    });

    it('can convert to array with all fields', function () {
        $opts = new InputOptions(
            title: 'Name',
            description: 'Enter name',
            minLength: 1,
            maxLength: 50,
            format: 'date',
            default: 'John',
        );

        expect($opts->toArray())->toBe([
            'title' => 'Name',
            'description' => 'Enter name',
            'minLength' => 1,
            'maxLength' => 50,
            'format' => 'date',
            'default' => 'John',
        ]);
    });

    it('filters null values in toArray', function () {
        $opts = new InputOptions(maxLength: 100);

        expect($opts->toArray())->toBe(['maxLength' => 100]);
    });

    it('returns empty array when all fields are null', function () {
        $opts = new InputOptions;

        expect($opts->toArray())->toBe([]);
    });

    it('implements Arrayable interface', function () {
        expect(new InputOptions)->toBeInstanceOf(Arrayable::class);
    });

    it('roundtrips through fromArray and toArray', function () {
        $data = [
            'title' => 'URL',
            'description' => 'Enter a URL',
            'minLength' => 10,
            'maxLength' => 2048,
            'format' => 'uri',
            'default' => 'https://',
        ];

        $opts = InputOptions::fromArray($data);

        expect($opts->toArray())->toBe($data);
    });
});
