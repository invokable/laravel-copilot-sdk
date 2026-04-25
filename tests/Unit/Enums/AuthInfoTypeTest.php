<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\AuthInfoType;

describe('AuthInfoType', function () {
    it('has all expected cases', function () {
        expect(AuthInfoType::API_KEY->value)->toBe('api-key')
            ->and(AuthInfoType::COPILOT_API_TOKEN->value)->toBe('copilot-api-token')
            ->and(AuthInfoType::ENV->value)->toBe('env')
            ->and(AuthInfoType::GH_CLI->value)->toBe('gh-cli')
            ->and(AuthInfoType::HMAC->value)->toBe('hmac')
            ->and(AuthInfoType::TOKEN->value)->toBe('token')
            ->and(AuthInfoType::USER->value)->toBe('user');
    });

    it('can create from string value', function () {
        expect(AuthInfoType::from('api-key'))->toBe(AuthInfoType::API_KEY)
            ->and(AuthInfoType::from('copilot-api-token'))->toBe(AuthInfoType::COPILOT_API_TOKEN)
            ->and(AuthInfoType::from('env'))->toBe(AuthInfoType::ENV)
            ->and(AuthInfoType::from('gh-cli'))->toBe(AuthInfoType::GH_CLI)
            ->and(AuthInfoType::from('hmac'))->toBe(AuthInfoType::HMAC)
            ->and(AuthInfoType::from('token'))->toBe(AuthInfoType::TOKEN)
            ->and(AuthInfoType::from('user'))->toBe(AuthInfoType::USER);
    });

    it('tryFrom returns null for unknown value', function () {
        expect(AuthInfoType::tryFrom('unknown-type'))->toBeNull();
    });
});
