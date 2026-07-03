<?php

declare(strict_types=1);

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Types\Rpc\SlashCommandInputChoice;

describe('SlashCommandInputChoice', function () {
    it('implements Arrayable', function () {
        expect(new SlashCommandInputChoice('on', 'Enable the feature'))
            ->toBeInstanceOf(Arrayable::class);
    });

    it('can be created from array', function () {
        $choice = SlashCommandInputChoice::fromArray([
            'name' => 'on',
            'description' => 'Enable the feature',
        ]);

        expect($choice->name)->toBe('on')
            ->and($choice->description)->toBe('Enable the feature');
    });

    it('converts to array', function () {
        $choice = new SlashCommandInputChoice('off', 'Disable the feature');

        expect($choice->toArray())->toBe([
            'name' => 'off',
            'description' => 'Disable the feature',
        ]);
    });

    it('roundtrips through fromArray and toArray', function () {
        $data = [
            'name' => 'show',
            'description' => 'Show current value',
        ];

        $choice = SlashCommandInputChoice::fromArray($data);

        expect($choice->toArray())->toBe($data);
    });
});
