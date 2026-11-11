<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\ShellAuthenticationResponse;

describe('ShellAuthenticationResponse', function () {
    it('serializes accepted answers', function () {
        $response = ShellAuthenticationResponse::fromArray([
            'action' => 'accept',
            'value' => 'secret answer',
        ]);

        expect($response->toArray())->toBe([
            'action' => 'accept',
            'value' => 'secret answer',
        ])
            ->and(json_encode($response))->not->toContain('secret answer');
    });

    it('omits the answer when cancelling', function () {
        $response = new ShellAuthenticationResponse(action: 'cancel', value: 'secret answer');

        expect($response->toArray())->toBe(['action' => 'cancel']);
    });
});
