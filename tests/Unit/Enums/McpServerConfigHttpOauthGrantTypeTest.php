<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\McpServerConfigHttpOauthGrantType;

describe('McpServerConfigHttpOauthGrantType', function () {
    it('has correct string values', function () {
        expect(McpServerConfigHttpOauthGrantType::AUTHORIZATION_CODE->value)->toBe('authorization_code')
            ->and(McpServerConfigHttpOauthGrantType::CLIENT_CREDENTIALS->value)->toBe('client_credentials');
    });

    it('can be created from string', function () {
        expect(McpServerConfigHttpOauthGrantType::from('authorization_code'))->toBe(McpServerConfigHttpOauthGrantType::AUTHORIZATION_CODE)
            ->and(McpServerConfigHttpOauthGrantType::from('client_credentials'))->toBe(McpServerConfigHttpOauthGrantType::CLIENT_CREDENTIALS);
    });

    it('has all expected cases', function () {
        expect(McpServerConfigHttpOauthGrantType::cases())->toHaveCount(2);
    });

    it('returns null for invalid value with tryFrom', function () {
        expect(McpServerConfigHttpOauthGrantType::tryFrom('invalid'))->toBeNull();
    });
});
