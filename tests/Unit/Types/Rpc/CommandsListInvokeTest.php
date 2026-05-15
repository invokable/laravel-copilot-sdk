<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\CommandList;
use Revolution\Copilot\Types\Rpc\CommandsInvokeRequest;
use Revolution\Copilot\Types\Rpc\CommandsListRequest;
use Revolution\Copilot\Types\Rpc\SlashCommandInfo;
use Revolution\Copilot\Types\Rpc\SlashCommandInput;

describe('CommandsInvokeRequest', function () {
    it('can be created with all fields', function () {
        $req = CommandsInvokeRequest::fromArray(['name' => 'help', 'input' => 'some text']);

        expect($req->name)->toBe('help')
            ->and($req->input)->toBe('some text');
    });

    it('can be created with minimal fields', function () {
        $req = CommandsInvokeRequest::fromArray(['name' => 'help']);

        expect($req->name)->toBe('help')
            ->and($req->input)->toBeNull();
    });

    it('converts to array', function () {
        $req = CommandsInvokeRequest::fromArray(['name' => 'help', 'input' => 'foo']);
        $array = $req->toArray();

        expect($array)->toHaveKey('name', 'help')
            ->and($array)->toHaveKey('input', 'foo');
    });

    it('excludes null fields from array', function () {
        $req = CommandsInvokeRequest::fromArray(['name' => 'help']);
        $array = $req->toArray();

        expect($array)->toHaveKey('name', 'help')
            ->and($array)->not->toHaveKey('input');
    });
});

describe('CommandsListRequest', function () {
    it('can be created with all fields', function () {
        $req = CommandsListRequest::fromArray([
            'includeBuiltins' => true,
            'includeClientCommands' => false,
            'includeSkills' => true,
        ]);

        expect($req->includeBuiltins)->toBeTrue()
            ->and($req->includeClientCommands)->toBeFalse()
            ->and($req->includeSkills)->toBeTrue();
    });

    it('can be created empty', function () {
        $req = CommandsListRequest::fromArray([]);

        expect($req->includeBuiltins)->toBeNull()
            ->and($req->includeClientCommands)->toBeNull()
            ->and($req->includeSkills)->toBeNull();
    });

    it('converts to array', function () {
        $req = CommandsListRequest::fromArray(['includeBuiltins' => true]);
        $array = $req->toArray();

        expect($array)->toHaveKey('includeBuiltins', true)
            ->and($array)->not->toHaveKey('includeClientCommands');
    });
});

describe('SlashCommandInput', function () {
    it('can be created with all fields', function () {
        $input = SlashCommandInput::fromArray([
            'hint' => 'Enter path',
            'completion' => 'directory',
            'preserveMultilineInput' => true,
            'required' => false,
        ]);

        expect($input->hint)->toBe('Enter path')
            ->and($input->completion)->toBe('directory')
            ->and($input->preserveMultilineInput)->toBeTrue()
            ->and($input->required)->toBeFalse();
    });

    it('can be created with minimal fields', function () {
        $input = SlashCommandInput::fromArray(['hint' => 'Enter text']);

        expect($input->hint)->toBe('Enter text')
            ->and($input->completion)->toBeNull();
    });

    it('converts to array', function () {
        $input = SlashCommandInput::fromArray(['hint' => 'Enter path']);
        $array = $input->toArray();

        expect($array)->toHaveKey('hint', 'Enter path')
            ->and($array)->not->toHaveKey('completion');
    });
});

describe('SlashCommandInfo', function () {
    it('can be created with all fields', function () {
        $info = SlashCommandInfo::fromArray([
            'name' => 'help',
            'description' => 'Show help',
            'kind' => 'builtin',
            'allowDuringAgentExecution' => true,
            'aliases' => ['h', '?'],
            'experimental' => false,
            'input' => ['hint' => 'Enter topic'],
        ]);

        expect($info->name)->toBe('help')
            ->and($info->description)->toBe('Show help')
            ->and($info->kind)->toBe('builtin')
            ->and($info->allowDuringAgentExecution)->toBeTrue()
            ->and($info->aliases)->toBe(['h', '?'])
            ->and($info->input)->toBeInstanceOf(SlashCommandInput::class);
    });

    it('can be created with minimal fields', function () {
        $info = SlashCommandInfo::fromArray([
            'name' => 'help',
            'description' => 'Help',
            'kind' => 'builtin',
            'allowDuringAgentExecution' => false,
        ]);

        expect($info->name)->toBe('help')
            ->and($info->aliases)->toBeNull()
            ->and($info->input)->toBeNull();
    });

    it('converts to array', function () {
        $info = SlashCommandInfo::fromArray([
            'name' => 'help',
            'description' => 'Help',
            'kind' => 'builtin',
            'allowDuringAgentExecution' => true,
        ]);
        $array = $info->toArray();

        expect($array)->toHaveKey('name', 'help')
            ->and($array)->not->toHaveKey('aliases');
    });
});

describe('CommandList', function () {
    it('can be created from array', function () {
        $list = CommandList::fromArray([
            'commands' => [
                ['name' => 'help', 'description' => 'Help', 'kind' => 'builtin', 'allowDuringAgentExecution' => true],
            ],
        ]);

        expect($list->commands)->toHaveCount(1)
            ->and($list->commands[0])->toBeInstanceOf(SlashCommandInfo::class)
            ->and($list->commands[0]->name)->toBe('help');
    });

    it('handles empty commands list', function () {
        $list = CommandList::fromArray(['commands' => []]);

        expect($list->commands)->toBeEmpty();
    });

    it('converts to array', function () {
        $list = CommandList::fromArray([
            'commands' => [
                ['name' => 'help', 'description' => 'Help', 'kind' => 'builtin', 'allowDuringAgentExecution' => false],
            ],
        ]);
        $array = $list->toArray();

        expect($array)->toHaveKey('commands')
            ->and($array['commands'])->toHaveCount(1)
            ->and($array['commands'][0])->toHaveKey('name', 'help');
    });
});
