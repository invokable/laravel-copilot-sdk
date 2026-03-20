<?php

declare(strict_types=1);

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\SectionOverrideAction;
use Revolution\Copilot\Types\SectionOverride;

describe('SectionOverride', function () {
    it('can be created with enum action', function () {
        $override = new SectionOverride(
            action: SectionOverrideAction::REPLACE,
            content: 'New content',
        );

        expect($override->action)->toBe(SectionOverrideAction::REPLACE)
            ->and($override->content)->toBe('New content');
    });

    it('can be created with string action', function () {
        $override = new SectionOverride(
            action: 'custom-action',
            content: 'Custom content',
        );

        expect($override->action)->toBe('custom-action')
            ->and($override->content)->toBe('Custom content');
    });

    it('can be created without content', function () {
        $override = new SectionOverride(action: SectionOverrideAction::REMOVE);

        expect($override->content)->toBeNull();
    });

    it('can be created from array with known action', function () {
        $override = SectionOverride::fromArray([
            'action' => 'replace',
            'content' => 'Replaced content',
        ]);

        expect($override->action)->toBe(SectionOverrideAction::REPLACE)
            ->and($override->content)->toBe('Replaced content');
    });

    it('can be created from array with unknown action', function () {
        $override = SectionOverride::fromArray([
            'action' => 'unknown-action',
        ]);

        expect($override->action)->toBe('unknown-action')
            ->and($override->content)->toBeNull();
    });

    it('converts to array with enum action', function () {
        $override = new SectionOverride(
            action: SectionOverrideAction::APPEND,
            content: 'Appended content',
        );

        expect($override->toArray())->toBe([
            'action' => 'append',
            'content' => 'Appended content',
        ]);
    });

    it('converts to array with string action', function () {
        $override = new SectionOverride(
            action: 'custom',
            content: 'Custom',
        );

        expect($override->toArray())->toBe([
            'action' => 'custom',
            'content' => 'Custom',
        ]);
    });

    it('filters null content in toArray', function () {
        $override = new SectionOverride(action: SectionOverrideAction::REMOVE);

        expect($override->toArray())->toBe(['action' => 'remove']);
    });

    it('implements Arrayable interface', function () {
        $override = new SectionOverride(action: SectionOverrideAction::REPLACE);
        expect($override)->toBeInstanceOf(Arrayable::class);
    });

    it('handles all SectionOverrideAction values', function () {
        foreach (SectionOverrideAction::cases() as $action) {
            $override = SectionOverride::fromArray(['action' => $action->value]);
            expect($override->action)->toBe($action);
        }
    });
});
