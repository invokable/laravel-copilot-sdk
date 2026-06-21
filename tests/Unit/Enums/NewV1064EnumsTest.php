<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\LlmInferenceHttpRequestStartTransport;
use Revolution\Copilot\Enums\McpOauthPendingRequestResponseKind;

describe('LlmInferenceHttpRequestStartTransport', function () {
    it('has http case', function () {
        expect(LlmInferenceHttpRequestStartTransport::Http->value)->toBe('http');
    });

    it('has websocket case', function () {
        expect(LlmInferenceHttpRequestStartTransport::Websocket->value)->toBe('websocket');
    });

    it('can create from string', function () {
        expect(LlmInferenceHttpRequestStartTransport::from('http'))->toBe(LlmInferenceHttpRequestStartTransport::Http)
            ->and(LlmInferenceHttpRequestStartTransport::from('websocket'))->toBe(LlmInferenceHttpRequestStartTransport::Websocket);
    });

    it('returns null for unknown value with tryFrom', function () {
        expect(LlmInferenceHttpRequestStartTransport::tryFrom('unknown'))->toBeNull();
    });
});

describe('McpOauthPendingRequestResponseKind', function () {
    it('has token case', function () {
        expect(McpOauthPendingRequestResponseKind::Token->value)->toBe('token');
    });

    it('has cancelled case', function () {
        expect(McpOauthPendingRequestResponseKind::Cancelled->value)->toBe('cancelled');
    });

    it('can create from string', function () {
        expect(McpOauthPendingRequestResponseKind::from('token'))->toBe(McpOauthPendingRequestResponseKind::Token)
            ->and(McpOauthPendingRequestResponseKind::from('cancelled'))->toBe(McpOauthPendingRequestResponseKind::Cancelled);
    });

    it('returns null for unknown value with tryFrom', function () {
        expect(McpOauthPendingRequestResponseKind::tryFrom('unknown'))->toBeNull();
    });
});
