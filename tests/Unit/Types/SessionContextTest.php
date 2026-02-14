<?php

declare(strict_types=1);

use Revolution\Copilot\Types\SessionContext;

describe('SessionContext', function () {
    it('can be created with all fields', function () {
        $context = new SessionContext(
            cwd: '/home/user/project',
            gitRoot: '/home/user/project',
            repository: 'owner/repo',
            branch: 'main',
        );

        expect($context->cwd)->toBe('/home/user/project')
            ->and($context->gitRoot)->toBe('/home/user/project')
            ->and($context->repository)->toBe('owner/repo')
            ->and($context->branch)->toBe('main');
    });

    it('can be created with minimal fields', function () {
        $context = new SessionContext(
            cwd: '/home/user/project',
        );

        expect($context->cwd)->toBe('/home/user/project')
            ->and($context->gitRoot)->toBeNull()
            ->and($context->repository)->toBeNull()
            ->and($context->branch)->toBeNull();
    });

    it('can be created from array with all fields', function () {
        $context = SessionContext::fromArray([
            'cwd' => '/home/user/work',
            'gitRoot' => '/home/user/work',
            'repository' => 'org/project',
            'branch' => 'develop',
        ]);

        expect($context->cwd)->toBe('/home/user/work')
            ->and($context->gitRoot)->toBe('/home/user/work')
            ->and($context->repository)->toBe('org/project')
            ->and($context->branch)->toBe('develop');
    });

    it('can be created from array with minimal fields', function () {
        $context = SessionContext::fromArray([
            'cwd' => '/tmp/test',
        ]);

        expect($context->cwd)->toBe('/tmp/test')
            ->and($context->gitRoot)->toBeNull()
            ->and($context->repository)->toBeNull()
            ->and($context->branch)->toBeNull();
    });

    it('can convert to array with all fields', function () {
        $context = new SessionContext(
            cwd: '/var/www/app',
            gitRoot: '/var/www/app',
            repository: 'company/webapp',
            branch: 'feature/new',
        );

        $array = $context->toArray();

        expect($array)->toBe([
            'cwd' => '/var/www/app',
            'gitRoot' => '/var/www/app',
            'repository' => 'company/webapp',
            'branch' => 'feature/new',
        ]);
    });

    it('filters null values in toArray', function () {
        $context = new SessionContext(
            cwd: '/workspace',
            repository: 'team/project',
        );

        $array = $context->toArray();

        expect($array)->toBe([
            'cwd' => '/workspace',
            'repository' => 'team/project',
        ]);
        expect($array)->not->toHaveKey('gitRoot')
            ->and($array)->not->toHaveKey('branch');
    });

    it('implements Arrayable interface', function () {
        $context = new SessionContext(cwd: '/home');

        expect($context)->toBeInstanceOf(\Illuminate\Contracts\Support\Arrayable::class);
    });
});
