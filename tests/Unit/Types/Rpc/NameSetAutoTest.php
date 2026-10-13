<?php

declare(strict_types=1);

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Types\Rpc\NameSetAutoRequest;
use Revolution\Copilot\Types\Rpc\NameSetAutoResult;

describe('NameSetAutoRequest', function () {
    it('round trips the auto-generated summary', function () {
        $request = NameSetAutoRequest::fromArray(['summary' => 'A useful session summary']);

        expect($request)->toBeInstanceOf(Arrayable::class)
            ->and($request->summary)->toBe('A useful session summary')
            ->and($request->toArray())->toBe(['summary' => 'A useful session summary']);
    });
});

describe('NameSetAutoResult', function () {
    it('preserves whether the summary was applied', function () {
        $result = NameSetAutoResult::fromArray(['applied' => true]);
        $notApplied = NameSetAutoResult::fromArray(['applied' => false]);

        expect($result)->toBeInstanceOf(Arrayable::class)
            ->and($result->applied)->toBeTrue()
            ->and($result->toArray())->toBe(['applied' => true])
            ->and($notApplied->applied)->toBeFalse()
            ->and($notApplied->toArray())->toBe(['applied' => false]);
    });
});
