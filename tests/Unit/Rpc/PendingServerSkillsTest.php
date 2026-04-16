<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingServerSkills;
use Revolution\Copilot\Rpc\PendingServerSkillsConfig;
use Revolution\Copilot\Transport\StdioTransport;
use Revolution\Copilot\Types\Rpc\ServerSkill;
use Revolution\Copilot\Types\Rpc\ServerSkillList;
use Revolution\Copilot\Types\Rpc\SkillsConfigSetDisabledSkillsRequest;
use Revolution\Copilot\Types\Rpc\SkillsDiscoverRequest;

describe('ServerSkill', function () {
    it('can be created from array with all fields', function () {
        $skill = ServerSkill::fromArray([
            'name' => 'code-review',
            'description' => 'Reviews code changes',
            'source' => 'project',
            'userInvocable' => true,
            'enabled' => true,
            'path' => '/workspace/.copilot/skills/code-review',
            'projectPath' => '/workspace',
        ]);

        expect($skill->name)->toBe('code-review')
            ->and($skill->description)->toBe('Reviews code changes')
            ->and($skill->source)->toBe('project')
            ->and($skill->userInvocable)->toBeTrue()
            ->and($skill->enabled)->toBeTrue()
            ->and($skill->path)->toBe('/workspace/.copilot/skills/code-review')
            ->and($skill->projectPath)->toBe('/workspace');
    });

    it('handles optional fields', function () {
        $skill = ServerSkill::fromArray([
            'name' => 'builtin-skill',
            'description' => 'A built-in skill',
            'source' => 'builtin',
            'userInvocable' => false,
            'enabled' => true,
        ]);

        expect($skill->path)->toBeNull()
            ->and($skill->projectPath)->toBeNull();
    });

    it('converts to array', function () {
        $skill = ServerSkill::fromArray([
            'name' => 'skill-1',
            'description' => 'Desc',
            'source' => 'plugin',
            'userInvocable' => true,
            'enabled' => false,
            'path' => '/path/to/skill',
        ]);

        $arr = $skill->toArray();

        expect($arr)->toHaveKey('name', 'skill-1')
            ->and($arr)->toHaveKey('description', 'Desc')
            ->and($arr)->toHaveKey('source', 'plugin')
            ->and($arr)->toHaveKey('userInvocable', true)
            ->and($arr)->toHaveKey('enabled', false)
            ->and($arr)->toHaveKey('path', '/path/to/skill')
            ->and($arr)->not->toHaveKey('projectPath');
    });
});

describe('ServerSkillList', function () {
    it('can be created from array with skills', function () {
        $list = ServerSkillList::fromArray([
            'skills' => [
                [
                    'name' => 'skill-1',
                    'description' => 'First skill',
                    'source' => 'project',
                    'userInvocable' => true,
                    'enabled' => true,
                ],
                [
                    'name' => 'skill-2',
                    'description' => 'Second skill',
                    'source' => 'builtin',
                    'userInvocable' => false,
                    'enabled' => true,
                ],
            ],
        ]);

        expect($list->skills)->toHaveCount(2)
            ->and($list->skills[0])->toBeInstanceOf(ServerSkill::class)
            ->and($list->skills[0]->name)->toBe('skill-1')
            ->and($list->skills[1]->name)->toBe('skill-2');
    });

    it('handles empty skills list', function () {
        $list = ServerSkillList::fromArray(['skills' => []]);

        expect($list->skills)->toBeEmpty();
    });

    it('handles missing skills key', function () {
        $list = ServerSkillList::fromArray([]);

        expect($list->skills)->toBeEmpty();
    });

    it('converts to array', function () {
        $list = ServerSkillList::fromArray([
            'skills' => [
                [
                    'name' => 'skill-1',
                    'description' => 'Desc',
                    'source' => 'project',
                    'userInvocable' => true,
                    'enabled' => true,
                ],
            ],
        ]);

        $arr = $list->toArray();

        expect($arr)->toHaveKey('skills')
            ->and($arr['skills'])->toHaveCount(1)
            ->and($arr['skills'][0])->toHaveKey('name', 'skill-1');
    });
});

describe('SkillsConfigSetDisabledSkillsRequest', function () {
    it('can be created with disabled skills', function () {
        $req = new SkillsConfigSetDisabledSkillsRequest(disabledSkills: ['skill-a', 'skill-b']);

        expect($req->disabledSkills)->toBe(['skill-a', 'skill-b']);
    });

    it('can be created from array', function () {
        $req = SkillsConfigSetDisabledSkillsRequest::fromArray([
            'disabledSkills' => ['skill-x'],
        ]);

        expect($req->disabledSkills)->toBe(['skill-x']);
    });

    it('handles empty disabled skills', function () {
        $req = SkillsConfigSetDisabledSkillsRequest::fromArray([]);

        expect($req->disabledSkills)->toBeEmpty();
    });

    it('converts to array', function () {
        $req = new SkillsConfigSetDisabledSkillsRequest(disabledSkills: ['a', 'b']);

        expect($req->toArray())->toBe(['disabledSkills' => ['a', 'b']]);
    });
});

describe('SkillsDiscoverRequest', function () {
    it('can be created with all fields', function () {
        $req = new SkillsDiscoverRequest(
            projectPaths: ['/workspace/project1'],
            skillDirectories: ['/custom/skills'],
        );

        expect($req->projectPaths)->toBe(['/workspace/project1'])
            ->and($req->skillDirectories)->toBe(['/custom/skills']);
    });

    it('handles empty constructor', function () {
        $req = new SkillsDiscoverRequest;

        expect($req->projectPaths)->toBeNull()
            ->and($req->skillDirectories)->toBeNull();
    });

    it('can be created from array', function () {
        $req = SkillsDiscoverRequest::fromArray([
            'projectPaths' => ['/path1', '/path2'],
        ]);

        expect($req->projectPaths)->toBe(['/path1', '/path2'])
            ->and($req->skillDirectories)->toBeNull();
    });

    it('converts to array omitting nulls', function () {
        $req = new SkillsDiscoverRequest(projectPaths: ['/project']);

        $arr = $req->toArray();

        expect($arr)->toHaveKey('projectPaths', ['/project'])
            ->and($arr)->not->toHaveKey('skillDirectories');
    });

    it('converts empty request to empty array', function () {
        $req = new SkillsDiscoverRequest;

        expect($req->toArray())->toBeEmpty();
    });
});

describe('PendingServerSkills', function () {
    it('config() returns PendingServerSkillsConfig', function () {
        $transport = new StdioTransport(fopen('php://memory', 'r'), fopen('php://memory', 'w'));
        $client = new JsonRpcClient($transport);
        $pending = new PendingServerSkills($client);

        expect($pending->config())->toBeInstanceOf(PendingServerSkillsConfig::class);
    });
});
