<?php

declare(strict_types=1);

use Revolution\Copilot\Contracts\CopilotSession;
use Revolution\Copilot\CopilotManager;
use Revolution\Copilot\Exceptions\StrayRequestException;
use Revolution\Copilot\Facades\Copilot;

beforeEach(function () {
    // Reset the facade before each test
    Copilot::clearResolvedInstances();
    Copilot::preventStrayRequests();
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

    it('returns CopilotManager instance', function () {
        $fake = Copilot::fake();

        expect($fake)->toBeInstanceOf(CopilotManager::class)
            ->and($fake->isFake())->toBeTrue();
    });
});

describe('Copilot::sequence()', function () {
    it('can push multiple responses in sequence', function () {
        Copilot::fake([
            '*' => Copilot::sequence()
                ->push(Copilot::response('First'))
                ->push(Copilot::response('Second'))
                ->push(Copilot::response('Third')),
        ]);

        $first = Copilot::run('1');
        $second = Copilot::run('2');
        $third = Copilot::run('3');

        expect($first->getContent())->toBe('First')
            ->and($second->getContent())->toBe('Second')
            ->and($third->getContent())->toBe('Third');
    });

    it('returns null when sequence is exhausted without fallback', function () {
        Copilot::fake(
            [
                '*' => Copilot::sequence()
                    ->push(Copilot::response('Only One')),
            ],
        );

        $first = Copilot::run('1');
        $second = Copilot::run('2');

        expect($first->getContent())->toBe('Only One')
            ->and($second)->toBeNull();
    });

    it('uses fallback when sequence is exhausted', function () {
        Copilot::fake([
            '*' => Copilot::sequence()
                ->push(Copilot::response('First'))
                ->whenEmpty(Copilot::response('Fallback')),
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
        Copilot::fake([
            '*' => Copilot::sequence()
                ->push(Copilot::response('2'))
                ->push(Copilot::response('4')),
        ]);

        $results = [];

        Copilot::start(function (CopilotSession $session) use (&$results) {
            $results[] = $session->sendAndWait('1 + 1')?->getContent();
            $results[] = $session->sendAndWait('2 + 2')?->getContent();
        });

        expect($results)->toBe(['2', '4']);
    });

    it('records prompts from session', function () {
        Copilot::fake('response');

        Copilot::start(function (CopilotSession $session) {
            $session->sendAndWait('Hello');
            $session->sendAndWait('World');
        });

        $recorded = Copilot::recorded();

        expect($recorded)->toHaveCount(2)
            ->and($recorded[0]['prompt'])->toBe('Hello')
            ->and($recorded[1]['prompt'])->toBe('World');
    });
});

describe('Assertions', function () {
    it('can assert prompt was sent', function () {
        Copilot::fake('response');

        Copilot::run('What is 1 + 1?');

        Copilot::assertPrompt('What is 1 + 1?');
    });

    it('can assert prompt with wildcard pattern', function () {
        Copilot::fake('response');

        Copilot::run('What is 1 + 1?');

        Copilot::assertPrompt('What is *');
    });

    it('can assert prompt was NOT sent', function () {
        Copilot::fake('response');

        Copilot::run('Hello');

        Copilot::assertNotPrompt('Goodbye');
    });

    it('can assert prompt count', function () {
        Copilot::fake('response');

        Copilot::run('One');
        Copilot::run('Two');
        Copilot::run('Three');

        Copilot::assertPromptCount(3);
    });

    it('can assert nothing was sent', function () {
        Copilot::fake('response');

        Copilot::assertNothingSent();
    });
});

describe('preventStrayRequests()', function () {
    it('throws exception when no fake response matches', function () {
        Copilot::preventStrayRequests();

        expect(fn () => Copilot::run('Unexpected prompt'))
            ->toThrow(StrayRequestException::class, 'Attempted request to [ping] without a matching fake.');
    });

    it('allows requests that have matching responses', function () {
        Copilot::fake([
            '*' => 'response',
        ]);
        Copilot::preventStrayRequests();

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
