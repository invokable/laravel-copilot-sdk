<?php

declare(strict_types=1);

use Revolution\Copilot\Contracts\CopilotSession;
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\Testing\CopilotFake;

beforeEach(function () {
    // Reset the facade before each test
    Copilot::clearResolvedInstances();
});

describe('Copilot::fake()', function () {
    it('can be faked with a simple string response', function () {
        Copilot::fake('Hello World');

        $response = Copilot::run('Hi');

        expect($response)->not->toBeNull()
            ->and($response->getContent())->toBe('Hello World');
    });

    it('can be faked with array responses', function () {
        Copilot::fake([
            '*' => '42',
        ]);

        $response = Copilot::run('What is 6 * 7?');

        expect($response->getContent())->toBe('42');
    });

    it('returns CopilotFake instance', function () {
        $fake = Copilot::fake();

        expect($fake)->toBeInstanceOf(CopilotFake::class);
    });
});

describe('Copilot::sequence()', function () {
    it('can push multiple responses in sequence', function () {
        $fake = Copilot::fake();

        $fake->fake([
            '*' => $fake->sequence()
                ->push($fake->response('First'))
                ->push($fake->response('Second'))
                ->push($fake->response('Third')),
        ]);

        $first = Copilot::run('1');
        $second = Copilot::run('2');
        $third = Copilot::run('3');

        expect($first->getContent())->toBe('First')
            ->and($second->getContent())->toBe('Second')
            ->and($third->getContent())->toBe('Third');
    });

    it('returns null when sequence is exhausted without fallback', function () {
        $fake = Copilot::fake();

        $fake->fake([
            '*' => $fake->sequence()
                ->push($fake->response('Only One')),
        ]);

        $first = Copilot::run('1');
        $second = Copilot::run('2');

        expect($first->getContent())->toBe('Only One')
            ->and($second)->toBeNull();
    });

    it('uses fallback when sequence is exhausted', function () {
        $fake = Copilot::fake();

        $fake->fake([
            '*' => $fake->sequence()
                ->push($fake->response('First'))
                ->whenEmpty($fake->response('Fallback')),
        ]);

        $first = Copilot::run('1');
        $second = Copilot::run('2');
        $third = Copilot::run('3');

        expect($first->getContent())->toBe('First')
            ->and($second->getContent())->toBe('Fallback')
            ->and($third->getContent())->toBe('Fallback');
    });
});

describe('Copilot::start()', function () {
    it('can start a session with fake', function () {
        $fake = Copilot::fake();

        $fake->fake([
            '*' => $fake->sequence()
                ->push($fake->response('2'))
                ->push($fake->response('4')),
        ]);

        $results = [];

        Copilot::start(function (CopilotSession $session) use (&$results) {
            $results[] = $session->sendAndWait('1 + 1')?->getContent();
            $results[] = $session->sendAndWait('2 + 2')?->getContent();
        });

        expect($results)->toBe(['2', '4']);
    });

    it('records prompts from session', function () {
        $fake = Copilot::fake('response');

        Copilot::start(function (CopilotSession $session) {
            $session->sendAndWait('Hello');
            $session->sendAndWait('World');
        });

        expect($fake->recorded())->toHaveCount(2)
            ->and($fake->recorded()[0]['prompt'])->toBe('Hello')
            ->and($fake->recorded()[1]['prompt'])->toBe('World');
    });
});

describe('Assertions', function () {
    it('can assert prompt was sent', function () {
        $fake = Copilot::fake('response');

        Copilot::run('What is 1 + 1?');

        $fake->assertPrompt('What is 1 + 1?');
    });

    it('can assert prompt with wildcard pattern', function () {
        $fake = Copilot::fake('response');

        Copilot::run('What is 1 + 1?');

        $fake->assertPrompt('What is *');
    });

    it('can assert prompt was NOT sent', function () {
        $fake = Copilot::fake('response');

        Copilot::run('Hello');

        $fake->assertNotPrompt('Goodbye');
    });

    it('can assert prompt count', function () {
        $fake = Copilot::fake('response');

        Copilot::run('One');
        Copilot::run('Two');
        Copilot::run('Three');

        $fake->assertPromptCount(3);
    });

    it('can assert nothing was sent', function () {
        $fake = Copilot::fake('response');

        $fake->assertNothingSent();
    });
});

describe('preventStrayRequests()', function () {
    it('throws exception when no fake response matches', function () {
        Copilot::fake()
            ->preventStrayRequests();

        expect(fn () => Copilot::run('Unexpected prompt'))
            ->toThrow(RuntimeException::class, 'Attempted Copilot request without matching fake response');
    });

    it('allows requests that have matching responses', function () {
        Copilot::fake([
            '*' => 'response',
        ])->preventStrayRequests();

        $response = Copilot::run('Any prompt');

        expect($response->getContent())->toBe('response');
    });
});

describe('createSession()', function () {
    it('can create a fake session', function () {
        Copilot::fake('test');

        $session = Copilot::createSession();

        expect($session)->toBeInstanceOf(CopilotSession::class)
            ->and($session->id())->toStartWith('fake-session-');
    });
});
