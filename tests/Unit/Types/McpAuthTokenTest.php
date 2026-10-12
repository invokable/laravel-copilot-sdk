<?php

declare(strict_types=1);

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Types\McpAuthToken;

describe('McpAuthToken', function () {
    it('round trips token data', function () {
        $token = McpAuthToken::fromArray([
            'accessToken' => 'access-token',
            'tokenType' => 'Bearer',
            'expiresIn' => 3600,
        ]);

        expect($token)->toBeInstanceOf(Arrayable::class)
            ->and($token->toArray())->toBe([
                'accessToken' => 'access-token',
                'tokenType' => 'Bearer',
                'expiresIn' => 3600,
            ]);
    });

    it('omits optional token data when absent', function () {
        $token = new McpAuthToken('access-token');

        expect($token->toArray())->toBe(['accessToken' => 'access-token']);
    });
});
