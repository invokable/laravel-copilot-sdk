<?php

declare(strict_types=1);

namespace Revolution\Copilot\Support;

final class BuiltInTools
{
    /**
     * Keep this list limited to tools confined to one session, with no host
     * filesystem, environment, network, or cross-session access.
     *
     * @var list<string>
     */
    public const array Isolated = [
        'ask_user',
        'task_complete',
        'exit_plan_mode',
        'task',
        'read_agent',
        'write_agent',
        'list_agents',
        'send_inbox',
        'context_board',
        'skill',
    ];
}
