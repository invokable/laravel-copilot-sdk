<?php

declare(strict_types=1);

use Revolution\Copilot\Support\BuiltInTools;
use Revolution\Copilot\Support\ToolSet;

describe('ToolSet', function () {
    it('builds source-qualified filters and returns a copy', function () {
        $toolSet = (new ToolSet)
            ->addBuiltIn(['read_file', 'write_file'])
            ->addMcp('github-list_issues')
            ->addCustom('*');

        $filters = $toolSet->toArray();
        $filters[] = 'builtin:mutated';

        expect($toolSet->toArray())->toBe([
            'builtin:read_file',
            'builtin:write_file',
            'mcp:github-list_issues',
            'custom:*',
        ]);
    });

    it('accepts wildcard built-in filters', function () {
        expect((new ToolSet)->addBuiltIn('*')->toArray())->toBe(['builtin:*'])
            ->and((new ToolSet)->addBuiltIn(BuiltInTools::Isolated)->toArray())->toBe([
                'builtin:ask_user',
                'builtin:task_complete',
                'builtin:exit_plan_mode',
                'builtin:task',
                'builtin:read_agent',
                'builtin:write_agent',
                'builtin:list_agents',
                'builtin:send_inbox',
                'builtin:context_board',
                'builtin:skill',
            ]);
    });

    it('rejects empty or invalid tool names', function () {
        foreach (['', 'bad name', 'name:part', 'name/path'] as $name) {
            expect(fn () => (new ToolSet)->addBuiltIn($name))
                ->toThrow(InvalidArgumentException::class);
        }
    });
});
