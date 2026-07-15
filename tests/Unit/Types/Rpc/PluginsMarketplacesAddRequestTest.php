<?php

declare(strict_types=1);

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Types\Rpc\PluginsMarketplacesAddRequest;

describe('PluginsMarketplacesAddRequest', function () {
    it('can be created from array with all fields', function () {
        $request = PluginsMarketplacesAddRequest::fromArray([
            'source' => 'owner/repo',
            'workingDirectory' => '/home/user/project',
        ]);

        expect($request->source)->toBe('owner/repo')
            ->and($request->workingDirectory)->toBe('/home/user/project');
    });

    it('can be created from array with minimal fields', function () {
        $request = PluginsMarketplacesAddRequest::fromArray([
            'source' => 'https://github.com/owner/repo',
        ]);

        expect($request->source)->toBe('https://github.com/owner/repo')
            ->and($request->workingDirectory)->toBeNull();
    });

    it('converts to array correctly', function () {
        $request = new PluginsMarketplacesAddRequest(
            source: 'owner/repo#main',
            workingDirectory: '/tmp/project',
        );

        $array = $request->toArray();

        expect($array)->toHaveKey('source', 'owner/repo#main')
            ->and($array)->toHaveKey('workingDirectory', '/tmp/project');
    });

    it('excludes null optional fields from array', function () {
        $request = new PluginsMarketplacesAddRequest(source: 'owner/repo');

        $array = $request->toArray();

        expect($array)->toHaveKey('source')
            ->and($array)->not->toHaveKey('workingDirectory');
    });

    it('implements Arrayable', function () {
        expect(new PluginsMarketplacesAddRequest(source: 'owner/repo'))->toBeInstanceOf(Arrayable::class);
    });
});
