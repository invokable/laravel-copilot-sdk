<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Who created a schedule: `user` (an explicit user action such as `/every` or
 * `/after`) or `model` (the agent via the `manage_schedule` tool). Gates whether
 * a scheduled skill that opted out of model invocation may fire: only
 * user-created schedules may.
 */
enum ScheduleOrigin: string
{
    case USER = 'user';
    case MODEL = 'model';
}
