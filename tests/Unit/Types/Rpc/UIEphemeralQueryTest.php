<?php

declare(strict_types=1);

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Types\Rpc\UIEphemeralQueryRequest;
use Revolution\Copilot\Types\Rpc\UIEphemeralQueryResult;

describe('UIEphemeralQueryRequest', function () {
    it('can be created from array', function () {
        $request = UIEphemeralQueryRequest::fromArray(['question' => 'What is the answer?']);

        expect($request->question)->toBe('What is the answer?');
    });

    it('converts to array and implements Arrayable', function () {
        $request = new UIEphemeralQueryRequest(question: 'What is the answer?');

        expect($request->toArray())->toBe(['question' => 'What is the answer?'])
            ->and($request)->toBeInstanceOf(Arrayable::class);
    });
});

describe('UIEphemeralQueryResult', function () {
    it('can be created from array', function () {
        $result = UIEphemeralQueryResult::fromArray(['answer' => '42']);

        expect($result->answer)->toBe('42');
    });

    it('converts to array and implements Arrayable', function () {
        $result = new UIEphemeralQueryResult(answer: '42');

        expect($result->toArray())->toBe(['answer' => '42'])
            ->and($result)->toBeInstanceOf(Arrayable::class);
    });
});
