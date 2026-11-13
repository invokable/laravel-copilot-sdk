<?php

declare(strict_types=1);

use Revolution\Copilot\Contracts\CopilotSession;
use Revolution\Copilot\Enums\AgentMode;
use Revolution\Copilot\Enums\ElicitationAction;
use Revolution\Copilot\Enums\ModelSwitchAutoTierStatus;
use Revolution\Copilot\Enums\SessionEventType;
use Revolution\Copilot\Testing\FakeSession;
use Revolution\Copilot\Testing\ResponseSequence;
use Revolution\Copilot\Types\Rpc\UIElicitationResponse;
use Revolution\Copilot\Types\SessionCapabilities;
use Revolution\Copilot\Types\SessionEvent;

describe('FakeSession', function () {
    it('exposes its id and default session state', function () {
        $session = new FakeSession('session-123', new ResponseSequence);

        expect($session)->toBeInstanceOf(CopilotSession::class)
            ->and($session->id())->toBe('session-123')
            ->and($session->capabilities())->toBeInstanceOf(SessionCapabilities::class)
            ->and($session->transcriptRecovery())->toBeNull()
            ->and($session->permissionModeResult())->toBeNull()
            ->and($session->openCanvases())->toBe([]);
    });

    it('returns a disabled session creation policy', function () {
        $policy = (new FakeSession('session-123', new ResponseSequence))->setSessionCreationHandler(null);

        expect($policy->enabled)->toBeFalse()
            ->and($policy->list)->toBeFalse()
            ->and($policy->create)->toBeFalse()
            ->and($policy->message)->toBeFalse()
            ->and($policy->close)->toBeFalse();
    });

    it('returns fake UI responses', function () {
        $session = new FakeSession('session-123', new ResponseSequence);
        $elicitation = $session->elicitation('Choose', ['type' => 'object']);

        expect($elicitation)->toBeInstanceOf(UIElicitationResponse::class)
            ->and($elicitation->action)->toBe(ElicitationAction::CANCEL)
            ->and($session->confirm('Continue?'))->toBeFalse()
            ->and($session->select('Choose', ['a', 'b']))->toBeNull()
            ->and($session->input('Name?'))->toBeNull();
    });

    it('records sent prompt options and returns a fake message id', function () {
        $session = new FakeSession('session-123', new ResponseSequence);
        $attachments = [['type' => 'text', 'content' => 'context']];

        $messageId = $session->send(
            prompt: 'Plan this',
            attachments: $attachments,
            mode: 'interactive',
            agentMode: AgentMode::PLAN,
            source: 'test',
        );

        expect($messageId)->toBe('fake-message-id')
            ->and($session->recorded())->toBe([
                [
                    'prompt' => 'Plan this',
                    'attachments' => $attachments,
                    'mode' => 'interactive',
                    'agentMode' => AgentMode::PLAN->value,
                    'source' => 'test',
                ],
            ]);
    });

    it('returns and consumes the next response from sendAndWait', function () {
        $response = ResponseSequence::responseFromContent('Done');
        $session = new FakeSession('session-123', (new ResponseSequence)->push($response));

        $result = $session->sendAndWait('Finish this', source: 'test');

        expect($result)->toBe($response)
            ->and($session->recorded()[0])->toBe([
                'prompt' => 'Finish this',
                'attachments' => null,
                'mode' => null,
                'agentMode' => null,
                'source' => 'test',
            ])
            ->and($session->getEvents())->toBe([])
            ->and($session->sendAndWait('No response'))->toBeNull();
    });

    it('yields responses from sendAndStream and stream', function () {
        $first = ResponseSequence::responseFromContent('First');
        $second = ResponseSequence::responseFromContent('Second');
        $session = new FakeSession('session-123', (new ResponseSequence)->push($first)->push($second));

        expect(iterator_to_array($session->sendAndStream('Stream one')))->toBe([$first])
            ->and(iterator_to_array($session->stream()))->toBe([$second])
            ->and(iterator_to_array($session->stream()))->toBe([]);
    });

    it('returns no-op results for unsupported operations', function () {
        $session = new FakeSession('session-123', new ResponseSequence);
        $handler = $session->on(SessionEventType::ASSISTANT_MESSAGE);

        $session->setTools([]);
        $session->registerShellAuthenticationHandler(null);
        $session->off($handler);
        $session->setModel('gpt-5');
        $session->disconnect();

        expect($session->log('Processing'))->toBe('')
            ->and($session->setAutoTier(null)->status)->toBe(ModelSwitchAutoTierStatus::UNCHANGED)
            ->and($session->setFusionTier('balanced')->status)->toBe(ModelSwitchAutoTierStatus::UNCHANGED);
    });

    it('creates response events from content', function () {
        $response = ResponseSequence::responseFromContent('Hello');

        expect($response)->toBeInstanceOf(SessionEvent::class)
            ->and($response->type)->toBe(SessionEventType::ASSISTANT_MESSAGE)
            ->and($response->data)->toBe(['content' => 'Hello']);
    });
});
