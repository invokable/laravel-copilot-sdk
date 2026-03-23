<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\SessionEventType;

describe('SessionEventType command events', function () {
    it('has command event types', function () {
        expect(SessionEventType::COMMAND_EXECUTE->value)->toBe('command.execute')
            ->and(SessionEventType::COMMAND_QUEUED->value)->toBe('command.queued')
            ->and(SessionEventType::COMMAND_COMPLETED->value)->toBe('command.completed')
            ->and(SessionEventType::COMMANDS_CHANGED->value)->toBe('commands.changed');
    });

    it('can create command types from string', function () {
        expect(SessionEventType::from('command.execute'))->toBe(SessionEventType::COMMAND_EXECUTE)
            ->and(SessionEventType::from('command.queued'))->toBe(SessionEventType::COMMAND_QUEUED)
            ->and(SessionEventType::from('command.completed'))->toBe(SessionEventType::COMMAND_COMPLETED)
            ->and(SessionEventType::from('commands.changed'))->toBe(SessionEventType::COMMANDS_CHANGED);
    });

    it('has exit plan mode event types', function () {
        expect(SessionEventType::EXIT_PLAN_MODE_REQUESTED->value)->toBe('exit_plan_mode.requested')
            ->and(SessionEventType::EXIT_PLAN_MODE_COMPLETED->value)->toBe('exit_plan_mode.completed');
    });

    it('can create exit plan mode types from string', function () {
        expect(SessionEventType::from('exit_plan_mode.requested'))->toBe(SessionEventType::EXIT_PLAN_MODE_REQUESTED)
            ->and(SessionEventType::from('exit_plan_mode.completed'))->toBe(SessionEventType::EXIT_PLAN_MODE_COMPLETED);
    });
});
