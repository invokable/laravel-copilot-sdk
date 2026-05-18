<?php

declare(strict_types=1);

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Types\CloudSessionRepository;

describe('CloudSessionRepository', function () {
    it('can be created with all fields', function () {
        $repo = new CloudSessionRepository(
            owner: 'myorg',
            name: 'myrepo',
            branch: 'main',
        );

        expect($repo->owner)->toBe('myorg')
            ->and($repo->name)->toBe('myrepo')
            ->and($repo->branch)->toBe('main');
    });

    it('can be created with minimal fields', function () {
        $repo = new CloudSessionRepository(
            owner: 'myorg',
            name: 'myrepo',
        );

        expect($repo->owner)->toBe('myorg')
            ->and($repo->name)->toBe('myrepo')
            ->and($repo->branch)->toBeNull();
    });

    it('can be created from array with all fields', function () {
        $repo = CloudSessionRepository::fromArray([
            'owner' => 'myorg',
            'name' => 'myrepo',
            'branch' => 'feature/new',
        ]);

        expect($repo->owner)->toBe('myorg')
            ->and($repo->name)->toBe('myrepo')
            ->and($repo->branch)->toBe('feature/new');
    });

    it('can be created from array with defaults', function () {
        $repo = CloudSessionRepository::fromArray([]);

        expect($repo->owner)->toBe('')
            ->and($repo->name)->toBe('')
            ->and($repo->branch)->toBeNull();
    });

    it('can convert to array with all fields', function () {
        $repo = new CloudSessionRepository(
            owner: 'myorg',
            name: 'myrepo',
            branch: 'main',
        );

        expect($repo->toArray())->toBe([
            'owner' => 'myorg',
            'name' => 'myrepo',
            'branch' => 'main',
        ]);
    });

    it('filters null branch from toArray', function () {
        $repo = new CloudSessionRepository(
            owner: 'myorg',
            name: 'myrepo',
        );

        expect($repo->toArray())->toBe([
            'owner' => 'myorg',
            'name' => 'myrepo',
        ]);
    });

    it('implements Arrayable interface', function () {
        $repo = CloudSessionRepository::fromArray([]);

        expect($repo)->toBeInstanceOf(Arrayable::class);
    });
});
