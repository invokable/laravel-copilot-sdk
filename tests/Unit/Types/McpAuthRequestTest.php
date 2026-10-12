<?php

declare(strict_types=1);

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Types\McpAuthRequest;

describe('McpAuthRequest', function () {
    it('round trips required and optional request data', function () {
        $request = McpAuthRequest::fromArray([
            'requestId' => 'request-1',
            'serverName' => 'docs',
            'serverUrl' => 'https://mcp.example.test',
            'reason' => 'initial',
            'wwwAuthenticateParams' => ['scope' => 'read'],
            'resourceMetadata' => '{"resource":"metadata"}',
            'staticClientConfig' => ['clientId' => 'host-client'],
        ]);

        expect($request)->toBeInstanceOf(Arrayable::class)
            ->and($request->toArray())->toBe([
                'requestId' => 'request-1',
                'serverName' => 'docs',
                'serverUrl' => 'https://mcp.example.test',
                'reason' => 'initial',
                'wwwAuthenticateParams' => ['scope' => 'read'],
                'resourceMetadata' => '{"resource":"metadata"}',
                'staticClientConfig' => ['clientId' => 'host-client'],
            ]);
    });

    it('omits optional request data when absent', function () {
        $request = new McpAuthRequest(
            requestId: 'request-1',
            serverName: 'docs',
            serverUrl: 'https://mcp.example.test',
            reason: 'refresh',
        );

        expect($request->toArray())->toBe([
            'requestId' => 'request-1',
            'serverName' => 'docs',
            'serverUrl' => 'https://mcp.example.test',
            'reason' => 'refresh',
        ]);
    });
});
