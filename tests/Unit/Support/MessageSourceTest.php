<?php

declare(strict_types=1);

use Revolution\Copilot\Support\MessageSource;

describe('MessageSource', function () {
    it('user', function () {
        expect(MessageSource::user())->toBe('user');
    });

    it('system', function () {
        expect(MessageSource::system())->toBe('system');
    });

    it('agent', function () {
        expect(MessageSource::agent('reviewer'))->toBe('agent-reviewer');
    });

    it('agent with empty id', function () {
        expect(MessageSource::agent(''))->toBe('agent-');
    });
});
