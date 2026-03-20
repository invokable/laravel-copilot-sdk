<?php

declare(strict_types=1);

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\SectionOverrideAction;
use Revolution\Copilot\Types\SectionOverride;
use Revolution\Copilot\Types\SystemMessageConfig;

describe('SystemMessageConfig', function () {
    it('can be created from array with all fields', function () {
        $config = SystemMessageConfig::fromArray([
            'mode' => 'append',
            'content' => 'Additional instructions',
        ]);

        expect($config->mode)->toBe('append')
            ->and($config->content)->toBe('Additional instructions');
    });

    it('can be created from array with minimal fields', function () {
        $config = SystemMessageConfig::fromArray([]);

        expect($config->mode)->toBeNull()
            ->and($config->content)->toBeNull();
    });

    it('can be created with replace mode', function () {
        $config = new SystemMessageConfig(
            mode: 'replace',
            content: 'Full custom system message',
        );

        expect($config->mode)->toBe('replace')
            ->and($config->content)->toBe('Full custom system message');
    });

    it('can convert to array with all fields', function () {
        $config = new SystemMessageConfig(
            mode: 'append',
            content: 'Extra content',
        );

        expect($config->toArray())->toBe([
            'mode' => 'append',
            'content' => 'Extra content',
        ]);
    });

    it('filters null values in toArray', function () {
        $config = new SystemMessageConfig;

        expect($config->toArray())->toBe([]);
    });

    it('can have only mode', function () {
        $config = new SystemMessageConfig(mode: 'append');

        expect($config->toArray())->toBe(['mode' => 'append']);
    });

    it('can have only content', function () {
        $config = new SystemMessageConfig(content: 'Some content');

        expect($config->toArray())->toBe(['content' => 'Some content']);
    });

    it('implements Arrayable interface', function () {
        $config = new SystemMessageConfig;

        expect($config)->toBeInstanceOf(Arrayable::class);
    });

    it('can be created with customize mode and sections', function () {
        $config = new SystemMessageConfig(
            mode: 'customize',
            sections: [
                'tools' => new SectionOverride(action: SectionOverrideAction::REPLACE, content: 'Custom tools section'),
                'safety' => new SectionOverride(action: SectionOverrideAction::REMOVE),
            ],
        );

        expect($config->mode)->toBe('customize')
            ->and($config->sections)->toHaveCount(2);
    });

    it('can be created from array with sections', function () {
        $config = SystemMessageConfig::fromArray([
            'mode' => 'customize',
            'sections' => [
                'tools' => ['action' => 'replace', 'content' => 'Custom tools'],
                'safety' => ['action' => 'remove'],
                'intro' => ['action' => 'append', 'content' => 'Extra intro'],
            ],
        ]);

        expect($config->mode)->toBe('customize')
            ->and($config->sections)->toHaveCount(3)
            ->and($config->sections['tools'])->toBeInstanceOf(SectionOverride::class)
            ->and($config->sections['tools']->action)->toBe(SectionOverrideAction::REPLACE)
            ->and($config->sections['tools']->content)->toBe('Custom tools')
            ->and($config->sections['safety']->action)->toBe(SectionOverrideAction::REMOVE);
    });

    it('converts sections to array in toArray', function () {
        $config = new SystemMessageConfig(
            mode: 'customize',
            sections: [
                'tools' => new SectionOverride(action: SectionOverrideAction::PREPEND, content: 'Before tools'),
            ],
        );

        $array = $config->toArray();

        expect($array['mode'])->toBe('customize')
            ->and($array['sections'])->toHaveCount(1)
            ->and($array['sections']['tools'])->toBe([
                'action' => 'prepend',
                'content' => 'Before tools',
            ]);
    });

    it('does not include sections key when sections is null', function () {
        $config = new SystemMessageConfig(mode: 'append', content: 'Content');

        expect($config->toArray())->not->toHaveKey('sections');
    });
});
