<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\InstructionSourceLocation;
use Revolution\Copilot\Enums\InstructionSourceType;
use Revolution\Copilot\Types\Rpc\CancelUserRequestedShellCommandResult;
use Revolution\Copilot\Types\Rpc\InstructionsDiscoverRequest;
use Revolution\Copilot\Types\Rpc\InstructionSource;
use Revolution\Copilot\Types\Rpc\McpIsServerRunningRequest;
use Revolution\Copilot\Types\Rpc\McpIsServerRunningResult;
use Revolution\Copilot\Types\Rpc\McpListToolsRequest;
use Revolution\Copilot\Types\Rpc\McpListToolsResult;
use Revolution\Copilot\Types\Rpc\McpStopServerRequest;
use Revolution\Copilot\Types\Rpc\McpTools;
use Revolution\Copilot\Types\Rpc\PlanReadSqlTodosResult;
use Revolution\Copilot\Types\Rpc\PlanSqlTodosRow;
use Revolution\Copilot\Types\Rpc\PluginsReloadRequest;
use Revolution\Copilot\Types\Rpc\ServerInstructionSourceList;
use Revolution\Copilot\Types\Rpc\ShellCancelUserRequestedRequest;

describe('McpTools', function () {
    it('can be created from array with all fields', function () {
        $tool = McpTools::fromArray(['name' => 'my_tool', 'description' => 'A tool']);

        expect($tool->name)->toBe('my_tool')
            ->and($tool->description)->toBe('A tool');
    });

    it('handles missing optional description', function () {
        $tool = McpTools::fromArray(['name' => 'my_tool']);

        expect($tool->name)->toBe('my_tool')
            ->and($tool->description)->toBeNull();
    });

    it('converts to array', function () {
        $tool = McpTools::fromArray(['name' => 'tool', 'description' => 'desc']);

        expect($tool->toArray())->toBe(['name' => 'tool', 'description' => 'desc']);
    });

    it('omits null description in toArray', function () {
        $tool = new McpTools(name: 'tool');

        expect($tool->toArray())->not->toHaveKey('description');
    });
});

describe('McpListToolsRequest', function () {
    it('can be created from array', function () {
        $req = McpListToolsRequest::fromArray(['serverName' => 'my-server']);

        expect($req->serverName)->toBe('my-server');
    });

    it('converts to array', function () {
        $req = new McpListToolsRequest(serverName: 'my-server');

        expect($req->toArray())->toBe(['serverName' => 'my-server']);
    });
});

describe('McpListToolsResult', function () {
    it('can be created from array with tools', function () {
        $result = McpListToolsResult::fromArray([
            'tools' => [
                ['name' => 'tool1', 'description' => 'Tool 1'],
                ['name' => 'tool2'],
            ],
        ]);

        expect($result->tools)->toHaveCount(2)
            ->and($result->tools[0])->toBeInstanceOf(McpTools::class)
            ->and($result->tools[0]->name)->toBe('tool1')
            ->and($result->tools[1]->description)->toBeNull();
    });

    it('can be created with empty tools', function () {
        $result = McpListToolsResult::fromArray(['tools' => []]);

        expect($result->tools)->toBeEmpty();
    });

    it('converts to array', function () {
        $result = McpListToolsResult::fromArray([
            'tools' => [['name' => 'tool1']],
        ]);

        expect($result->toArray())->toHaveKey('tools')
            ->and($result->toArray()['tools'])->toHaveCount(1);
    });
});

describe('McpIsServerRunningRequest', function () {
    it('can be created from array', function () {
        $req = McpIsServerRunningRequest::fromArray(['serverName' => 'my-server']);

        expect($req->serverName)->toBe('my-server');
    });

    it('converts to array', function () {
        $req = new McpIsServerRunningRequest(serverName: 'my-server');

        expect($req->toArray())->toBe(['serverName' => 'my-server']);
    });
});

describe('McpIsServerRunningResult', function () {
    it('returns true when server is running', function () {
        $result = McpIsServerRunningResult::fromArray(['running' => true]);

        expect($result->running)->toBeTrue();
    });

    it('returns false when server is not running', function () {
        $result = McpIsServerRunningResult::fromArray(['running' => false]);

        expect($result->running)->toBeFalse();
    });

    it('handles default false', function () {
        $result = McpIsServerRunningResult::fromArray([]);

        expect($result->running)->toBeFalse();
    });

    it('converts to array', function () {
        $result = new McpIsServerRunningResult(running: true);

        expect($result->toArray())->toBe(['running' => true]);
    });
});

describe('McpStopServerRequest', function () {
    it('can be created from array', function () {
        $req = McpStopServerRequest::fromArray(['serverName' => 'my-server']);

        expect($req->serverName)->toBe('my-server');
    });

    it('converts to array', function () {
        $req = new McpStopServerRequest(serverName: 'my-server');

        expect($req->toArray())->toBe(['serverName' => 'my-server']);
    });
});

describe('PluginsReloadRequest', function () {
    it('can be created from array with all fields', function () {
        $req = PluginsReloadRequest::fromArray([
            'deferRepoHooks' => true,
            'reloadCustomAgents' => false,
            'reloadHooks' => true,
            'reloadMcp' => false,
        ]);

        expect($req->deferRepoHooks)->toBeTrue()
            ->and($req->reloadCustomAgents)->toBeFalse()
            ->and($req->reloadHooks)->toBeTrue()
            ->and($req->reloadMcp)->toBeFalse();
    });

    it('handles all null defaults', function () {
        $req = PluginsReloadRequest::fromArray([]);

        expect($req->deferRepoHooks)->toBeNull()
            ->and($req->reloadCustomAgents)->toBeNull();
    });

    it('converts to array omitting nulls', function () {
        $req = new PluginsReloadRequest(reloadMcp: true);

        expect($req->toArray())->toBe(['reloadMcp' => true])
            ->and($req->toArray())->not->toHaveKey('deferRepoHooks');
    });
});

describe('InstructionsDiscoverRequest', function () {
    it('can be created from array with all fields', function () {
        $req = InstructionsDiscoverRequest::fromArray([
            'excludeHostInstructions' => true,
            'projectPaths' => ['/path/to/project'],
        ]);

        expect($req->excludeHostInstructions)->toBeTrue()
            ->and($req->projectPaths)->toBe(['/path/to/project']);
    });

    it('handles empty defaults', function () {
        $req = InstructionsDiscoverRequest::fromArray([]);

        expect($req->excludeHostInstructions)->toBeNull()
            ->and($req->projectPaths)->toBeNull();
    });

    it('converts to array omitting nulls', function () {
        $req = new InstructionsDiscoverRequest(projectPaths: ['/foo']);

        expect($req->toArray())->toBe(['projectPaths' => ['/foo']])
            ->and($req->toArray())->not->toHaveKey('excludeHostInstructions');
    });
});

describe('InstructionSource', function () {
    it('can be created from array with all fields', function () {
        $source = InstructionSource::fromArray([
            'content' => 'Be helpful.',
            'id' => 'src-1',
            'label' => 'My Source',
            'location' => 'repository',
            'sourcePath' => '.copilot/instructions.md',
            'type' => 'repo',
            'applyTo' => ['**/*.php'],
            'defaultDisabled' => false,
            'description' => 'PHP instructions',
            'projectPath' => '/my/project',
        ]);

        expect($source->content)->toBe('Be helpful.')
            ->and($source->id)->toBe('src-1')
            ->and($source->location)->toBe(InstructionSourceLocation::REPOSITORY)
            ->and($source->type)->toBe(InstructionSourceType::REPO)
            ->and($source->applyTo)->toBe(['**/*.php'])
            ->and($source->projectPath)->toBe('/my/project');
    });

    it('handles optional fields defaulting to null', function () {
        $source = InstructionSource::fromArray([
            'content' => 'content',
            'id' => 'src-1',
            'label' => 'label',
            'location' => 'user',
            'sourcePath' => 'path.md',
            'type' => 'home',
        ]);

        expect($source->applyTo)->toBeNull()
            ->and($source->description)->toBeNull()
            ->and($source->projectPath)->toBeNull();
    });

    it('converts to array', function () {
        $source = InstructionSource::fromArray([
            'content' => 'text',
            'id' => 'id1',
            'label' => 'label',
            'location' => 'plugin',
            'sourcePath' => 'path',
            'type' => 'home',
        ]);

        $array = $source->toArray();

        expect($array)->toHaveKey('location', 'plugin')
            ->and($array)->toHaveKey('type', 'home')
            ->and($array)->not->toHaveKey('projectPath');
    });
});

describe('ServerInstructionSourceList', function () {
    it('can be created from array with sources', function () {
        $list = ServerInstructionSourceList::fromArray([
            'sources' => [
                [
                    'content' => 'text',
                    'id' => 'src-1',
                    'label' => 'label',
                    'location' => 'user',
                    'sourcePath' => 'path',
                    'type' => 'home',
                ],
            ],
        ]);

        expect($list->sources)->toHaveCount(1)
            ->and($list->sources[0])->toBeInstanceOf(InstructionSource::class);
    });

    it('can be created with empty sources', function () {
        $list = ServerInstructionSourceList::fromArray(['sources' => []]);

        expect($list->sources)->toBeEmpty();
    });

    it('converts to array', function () {
        $list = ServerInstructionSourceList::fromArray(['sources' => []]);

        expect($list->toArray())->toBe(['sources' => []]);
    });
});

describe('PlanSqlTodosRow', function () {
    it('can be created from array with all fields', function () {
        $row = PlanSqlTodosRow::fromArray([
            'description' => 'Do something',
            'id' => 'todo-1',
            'status' => 'pending',
            'title' => 'My Todo',
        ]);

        expect($row->description)->toBe('Do something')
            ->and($row->id)->toBe('todo-1')
            ->and($row->status)->toBe('pending')
            ->and($row->title)->toBe('My Todo');
    });

    it('handles all null defaults', function () {
        $row = PlanSqlTodosRow::fromArray([]);

        expect($row->id)->toBeNull()
            ->and($row->title)->toBeNull();
    });

    it('converts to array omitting nulls', function () {
        $row = new PlanSqlTodosRow(id: 'todo-1', title: 'Task');

        expect($row->toArray())->toBe(['id' => 'todo-1', 'title' => 'Task'])
            ->and($row->toArray())->not->toHaveKey('description');
    });
});

describe('PlanReadSqlTodosResult', function () {
    it('can be created from array with rows', function () {
        $result = PlanReadSqlTodosResult::fromArray([
            'rows' => [
                ['id' => 'todo-1', 'title' => 'Task 1', 'status' => 'pending'],
            ],
        ]);

        expect($result->rows)->toHaveCount(1)
            ->and($result->rows[0])->toBeInstanceOf(PlanSqlTodosRow::class)
            ->and($result->rows[0]->id)->toBe('todo-1');
    });

    it('can be created with empty rows', function () {
        $result = PlanReadSqlTodosResult::fromArray(['rows' => []]);

        expect($result->rows)->toBeEmpty();
    });

    it('converts to array', function () {
        $result = PlanReadSqlTodosResult::fromArray(['rows' => []]);

        expect($result->toArray())->toBe(['rows' => []]);
    });
});

describe('ShellCancelUserRequestedRequest', function () {
    it('can be created from array', function () {
        $req = ShellCancelUserRequestedRequest::fromArray(['requestId' => 'req-123']);

        expect($req->requestId)->toBe('req-123');
    });

    it('converts to array', function () {
        $req = new ShellCancelUserRequestedRequest(requestId: 'req-abc');

        expect($req->toArray())->toBe(['requestId' => 'req-abc']);
    });
});

describe('CancelUserRequestedShellCommandResult', function () {
    it('returns true when cancelled', function () {
        $result = CancelUserRequestedShellCommandResult::fromArray(['cancelled' => true]);

        expect($result->cancelled)->toBeTrue();
    });

    it('returns false when not cancelled', function () {
        $result = CancelUserRequestedShellCommandResult::fromArray(['cancelled' => false]);

        expect($result->cancelled)->toBeFalse();
    });

    it('handles default false', function () {
        $result = CancelUserRequestedShellCommandResult::fromArray([]);

        expect($result->cancelled)->toBeFalse();
    });

    it('converts to array', function () {
        $result = new CancelUserRequestedShellCommandResult(cancelled: true);

        expect($result->toArray())->toBe(['cancelled' => true]);
    });
});
