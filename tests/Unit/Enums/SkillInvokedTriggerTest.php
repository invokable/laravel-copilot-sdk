<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\SkillInvokedTrigger;

describe('SkillInvokedTrigger', function () {
    it('has correct string values', function () {
        expect(SkillInvokedTrigger::USER_INVOKED->value)->toBe('user-invoked')
            ->and(SkillInvokedTrigger::AGENT_INVOKED->value)->toBe('agent-invoked')
            ->and(SkillInvokedTrigger::CONTEXT_LOAD->value)->toBe('context-load');
    });

    it('can be created from string', function () {
        expect(SkillInvokedTrigger::from('user-invoked'))->toBe(SkillInvokedTrigger::USER_INVOKED)
            ->and(SkillInvokedTrigger::from('agent-invoked'))->toBe(SkillInvokedTrigger::AGENT_INVOKED)
            ->and(SkillInvokedTrigger::from('context-load'))->toBe(SkillInvokedTrigger::CONTEXT_LOAD);
    });

    it('has all expected cases', function () {
        $cases = SkillInvokedTrigger::cases();

        expect($cases)->toHaveCount(3)
            ->and($cases)->toContain(SkillInvokedTrigger::USER_INVOKED)
            ->and($cases)->toContain(SkillInvokedTrigger::AGENT_INVOKED)
            ->and($cases)->toContain(SkillInvokedTrigger::CONTEXT_LOAD);
    });
});
