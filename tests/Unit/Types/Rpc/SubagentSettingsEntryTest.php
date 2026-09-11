<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\SubagentSettingsEntryContextTier;
use Revolution\Copilot\Types\Rpc\SubagentSettingsEntry;

describe('SubagentSettingsEntry', function () {
    it('can be created with all fields', function () {
        $entry = new SubagentSettingsEntry(
            contextTier: SubagentSettingsEntryContextTier::Default,
            effortLevel: 'medium',
            model: 'gpt-5',
            autoInvoke: true,
        );

        expect($entry->contextTier)->toBe(SubagentSettingsEntryContextTier::Default)
            ->and($entry->effortLevel)->toBe('medium')
            ->and($entry->model)->toBe('gpt-5')
            ->and($entry->autoInvoke)->toBeTrue();
    });

    it('handles default values', function () {
        $entry = new SubagentSettingsEntry;

        expect($entry->contextTier)->toBeNull()
            ->and($entry->effortLevel)->toBeNull()
            ->and($entry->model)->toBeNull()
            ->and($entry->autoInvoke)->toBeNull();
    });

    it('can be created from array', function () {
        $entry = SubagentSettingsEntry::fromArray(['autoInvoke' => false]);

        expect($entry->autoInvoke)->toBeFalse();
    });

    it('roundtrips through fromArray/toArray', function () {
        $data = ['effortLevel' => 'high', 'model' => 'gpt-5', 'autoInvoke' => true];

        expect(SubagentSettingsEntry::fromArray($data)->toArray())->toBe($data);
    });

    it('omits null values when serializing', function () {
        $entry = new SubagentSettingsEntry;

        expect($entry->toArray())->toBe([]);
    });
});
