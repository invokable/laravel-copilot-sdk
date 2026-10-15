<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\AuthLoginMode;
use Revolution\Copilot\Enums\LoginProviderKind;
use Revolution\Copilot\Types\Rpc\AuthLoginBeginRequest;

describe('AuthLoginBeginRequest', function () {
    it('serializes a provider enum to its wire value', function () {
        $request = new AuthLoginBeginRequest(LoginProviderKind::ENTRA);

        expect($request->toArray())->toBe(['kind' => 'entra']);
    });

    it('preserves an unrecognized provider kind string', function () {
        $request = new AuthLoginBeginRequest('customProvider');

        expect($request->toArray())->toBe(['kind' => 'customProvider']);
    });

    it('serializes account-selection mode without optional identity fields', function () {
        $request = new AuthLoginBeginRequest(LoginProviderKind::ENTRA, AuthLoginMode::SelectAccount);

        expect($request->toArray())->toBe([
            'kind' => 'entra',
            'mode' => 'selectAccount',
        ]);
    });

    it('preserves an opaque cached account ID in default mode', function () {
        $request = new AuthLoginBeginRequest(
            LoginProviderKind::ENTRA,
            AuthLoginMode::Default,
            'entra:opaque-base-identity',
        );

        expect($request->toArray())->toBe([
            'kind' => 'entra',
            'mode' => 'default',
            'accountId' => 'entra:opaque-base-identity',
        ]);
    });
});
