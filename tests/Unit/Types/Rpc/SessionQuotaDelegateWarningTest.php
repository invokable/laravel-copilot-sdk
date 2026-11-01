<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\SessionQuotaDelegateWarning;

describe('SessionQuotaDelegateWarning', function () {
    it('can be created from array with all fields', function () {
        $warning = SessionQuotaDelegateWarning::fromArray([
            'text' => 'Upgrade your account to enable delegation.',
            'url' => 'https://example.com/upgrade',
        ]);

        expect($warning->text)->toBe('Upgrade your account to enable delegation.')
            ->and($warning->url)->toBe('https://example.com/upgrade');
    });

    it('defaults missing fields to empty strings', function () {
        $warning = SessionQuotaDelegateWarning::fromArray([]);

        expect($warning->text)->toBe('')
            ->and($warning->url)->toBe('')
            ->and($warning->toArray())->toBe(['text' => '', 'url' => '']);
    });

    it('converts its fields to an array', function () {
        $warning = new SessionQuotaDelegateWarning(
            text: 'Delegation is unavailable.',
            url: 'https://example.com/help',
        );

        expect($warning->toArray())->toBe([
            'text' => 'Delegation is unavailable.',
            'url' => 'https://example.com/help',
        ]);
    });
});
