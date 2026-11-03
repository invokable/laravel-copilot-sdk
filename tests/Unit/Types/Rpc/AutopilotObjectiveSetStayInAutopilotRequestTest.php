<?php

declare(strict_types=1);

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Types\Rpc\AutopilotObjectiveSetStayInAutopilotRequest;

describe('AutopilotObjectiveSetStayInAutopilotRequest', function () {
    it('round-trips the preference value', function () {
        $data = ['value' => true];

        $request = AutopilotObjectiveSetStayInAutopilotRequest::fromArray($data);

        expect($request->value)->toBe(true)
            ->and($request->toArray())->toBe($data)
            ->and($request)->toBeInstanceOf(Arrayable::class);
    });
});
