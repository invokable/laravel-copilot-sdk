<?php

declare(strict_types=1);

use Revolution\Copilot\Types\SessionListFilter;

describe('SessionListFilter', function () {
    it('can be created with all fields', function () {
        $filter = new SessionListFilter(
            cwd: '/home/user/project',
            gitRoot: '/home/user/project',
            repository: 'owner/repo',
            branch: 'main',
        );

        expect($filter->cwd)->toBe('/home/user/project')
            ->and($filter->gitRoot)->toBe('/home/user/project')
            ->and($filter->repository)->toBe('owner/repo')
            ->and($filter->branch)->toBe('main');
    });

    it('can be created with no fields', function () {
        $filter = new SessionListFilter;

        expect($filter->cwd)->toBeNull()
            ->and($filter->gitRoot)->toBeNull()
            ->and($filter->repository)->toBeNull()
            ->and($filter->branch)->toBeNull();
    });

    it('can be created from array with all fields', function () {
        $filter = SessionListFilter::fromArray([
            'cwd' => '/home/user/work',
            'gitRoot' => '/home/user/work',
            'repository' => 'org/project',
            'branch' => 'develop',
        ]);

        expect($filter->cwd)->toBe('/home/user/work')
            ->and($filter->gitRoot)->toBe('/home/user/work')
            ->and($filter->repository)->toBe('org/project')
            ->and($filter->branch)->toBe('develop');
    });

    it('can be created from array with partial fields', function () {
        $filter = SessionListFilter::fromArray([
            'repository' => 'company/app',
            'branch' => 'feature',
        ]);

        expect($filter->cwd)->toBeNull()
            ->and($filter->gitRoot)->toBeNull()
            ->and($filter->repository)->toBe('company/app')
            ->and($filter->branch)->toBe('feature');
    });

    it('can convert to array with all fields', function () {
        $filter = new SessionListFilter(
            cwd: '/var/www/app',
            gitRoot: '/var/www/app',
            repository: 'company/webapp',
            branch: 'feature/new',
        );

        $array = $filter->toArray();

        expect($array)->toBe([
            'cwd' => '/var/www/app',
            'gitRoot' => '/var/www/app',
            'repository' => 'company/webapp',
            'branch' => 'feature/new',
        ]);
    });

    it('filters null values in toArray', function () {
        $filter = new SessionListFilter(
            repository: 'team/project',
        );

        $array = $filter->toArray();

        expect($array)->toBe([
            'repository' => 'team/project',
        ]);
        expect($array)->not->toHaveKey('cwd')
            ->and($array)->not->toHaveKey('gitRoot')
            ->and($array)->not->toHaveKey('branch');
    });

    it('returns empty array when all fields are null', function () {
        $filter = new SessionListFilter;

        $array = $filter->toArray();

        expect($array)->toBe([]);
    });

    it('implements Arrayable interface', function () {
        $filter = new SessionListFilter;

        expect($filter)->toBeInstanceOf(\Illuminate\Contracts\Support\Arrayable::class);
    });
});
