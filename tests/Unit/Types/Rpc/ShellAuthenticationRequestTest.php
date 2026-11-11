<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\ShellAuthenticationInputKind;
use Revolution\Copilot\Types\Rpc\ShellAuthenticationRequest;

describe('ShellAuthenticationRequest', function () {
    it('converts an authentication request to and from arrays', function () {
        $request = new ShellAuthenticationRequest(
            requestId: 'request-1',
            program: 'ssh',
            prompt: 'Password:',
            inputKind: ShellAuthenticationInputKind::Secret,
            command: 'ssh example.com',
            cwd: '/workspace',
        );

        expect(ShellAuthenticationRequest::fromArray($request->toArray()))->toEqual($request);
    });

    it('preserves unknown input kinds when converting from arrays', function () {
        $request = ShellAuthenticationRequest::fromArray([
            'requestId' => 'request-1',
            'program' => 'ssh',
            'prompt' => 'Password:',
            'inputKind' => 'future-kind',
            'command' => 'ssh example.com',
            'cwd' => '/workspace',
        ]);

        expect($request->inputKind)->toBe('future-kind')
            ->and($request->toArray()['inputKind'])->toBe('future-kind');
    });
});
