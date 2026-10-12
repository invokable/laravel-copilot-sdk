<?php

declare(strict_types=1);

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\McpOauthPendingRequestResponseKind;
use Revolution\Copilot\Types\McpAuthResult;

describe('McpAuthResult', function () {
    it('round trips a token result', function () {
        $result = McpAuthResult::fromArray([
            'kind' => 'token',
            'accessToken' => 'access-token',
            'tokenType' => 'Bearer',
            'expiresIn' => 3600,
        ]);

        expect($result)->toBeInstanceOf(Arrayable::class)
            ->and($result->kind)->toBe(McpOauthPendingRequestResponseKind::Token)
            ->and($result->toArray())->toBe([
                'kind' => 'token',
                'accessToken' => 'access-token',
                'tokenType' => 'Bearer',
                'expiresIn' => 3600,
            ]);
    });

    it('serializes a cancelled result without token data', function () {
        $result = new McpAuthResult(McpOauthPendingRequestResponseKind::Cancelled);

        expect($result->toArray())->toBe(['kind' => 'cancelled']);
    });
});
