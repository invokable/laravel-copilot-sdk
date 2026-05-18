<?php

declare(strict_types=1);

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Types\CloudSessionOptions;
use Revolution\Copilot\Types\CloudSessionRepository;

describe('CloudSessionOptions', function () {
    it('can be created with all fields', function () {
        $repo = new CloudSessionRepository(owner: 'myorg', name: 'myrepo');
        $options = new CloudSessionOptions(repository: $repo);

        expect($options->repository)->toBe($repo);
    });

    it('can be created with no fields', function () {
        $options = new CloudSessionOptions;

        expect($options->repository)->toBeNull();
    });

    it('can be created from array with repository as array', function () {
        $options = CloudSessionOptions::fromArray([
            'repository' => [
                'owner' => 'myorg',
                'name' => 'myrepo',
                'branch' => 'main',
            ],
        ]);

        expect($options->repository)->toBeInstanceOf(CloudSessionRepository::class)
            ->and($options->repository->owner)->toBe('myorg')
            ->and($options->repository->name)->toBe('myrepo')
            ->and($options->repository->branch)->toBe('main');
    });

    it('can be created from array with repository as CloudSessionRepository instance', function () {
        $repo = new CloudSessionRepository(owner: 'myorg', name: 'myrepo');
        $options = CloudSessionOptions::fromArray(['repository' => $repo]);

        expect($options->repository)->toBe($repo);
    });

    it('can be created from empty array', function () {
        $options = CloudSessionOptions::fromArray([]);

        expect($options->repository)->toBeNull();
    });

    it('can convert to array with repository', function () {
        $repo = new CloudSessionRepository(owner: 'myorg', name: 'myrepo', branch: 'main');
        $options = new CloudSessionOptions(repository: $repo);

        expect($options->toArray())->toBe([
            'repository' => [
                'owner' => 'myorg',
                'name' => 'myrepo',
                'branch' => 'main',
            ],
        ]);
    });

    it('converts to empty array when no repository', function () {
        $options = new CloudSessionOptions;

        expect($options->toArray())->toBe([]);
    });

    it('implements Arrayable interface', function () {
        $options = new CloudSessionOptions;

        expect($options)->toBeInstanceOf(Arrayable::class);
    });
});
