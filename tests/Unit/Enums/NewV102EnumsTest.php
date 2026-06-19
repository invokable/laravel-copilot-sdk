<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\AgentDiscoveryPathScope;
use Revolution\Copilot\Enums\InstructionDiscoveryPathKind;
use Revolution\Copilot\Enums\McpServerConfigDeferTools;
use Revolution\Copilot\Enums\SkillDiscoveryScope;
use Revolution\Copilot\Enums\SubagentSettingsEntryContextTier;

describe('AgentDiscoveryPathScope', function () {
    it('has all expected cases', function () {
        expect(AgentDiscoveryPathScope::User->value)->toBe('user')
            ->and(AgentDiscoveryPathScope::Project->value)->toBe('project')
            ->and(AgentDiscoveryPathScope::Plugin->value)->toBe('plugin');
    });

    it('can be created from string', function () {
        expect(AgentDiscoveryPathScope::from('user'))->toBe(AgentDiscoveryPathScope::User)
            ->and(AgentDiscoveryPathScope::from('project'))->toBe(AgentDiscoveryPathScope::Project)
            ->and(AgentDiscoveryPathScope::from('plugin'))->toBe(AgentDiscoveryPathScope::Plugin);
    });

    it('returns null for unknown value with tryFrom', function () {
        expect(AgentDiscoveryPathScope::tryFrom('unknown'))->toBeNull();
    });
});

describe('SkillDiscoveryScope', function () {
    it('has all expected cases', function () {
        expect(SkillDiscoveryScope::User->value)->toBe('user')
            ->and(SkillDiscoveryScope::Project->value)->toBe('project')
            ->and(SkillDiscoveryScope::Plugin->value)->toBe('plugin');
    });

    it('can be created from string', function () {
        expect(SkillDiscoveryScope::from('user'))->toBe(SkillDiscoveryScope::User)
            ->and(SkillDiscoveryScope::from('project'))->toBe(SkillDiscoveryScope::Project)
            ->and(SkillDiscoveryScope::from('plugin'))->toBe(SkillDiscoveryScope::Plugin);
    });

    it('returns null for unknown value with tryFrom', function () {
        expect(SkillDiscoveryScope::tryFrom('unknown'))->toBeNull();
    });
});

describe('InstructionDiscoveryPathKind', function () {
    it('has all expected cases', function () {
        expect(InstructionDiscoveryPathKind::File->value)->toBe('file')
            ->and(InstructionDiscoveryPathKind::Directory->value)->toBe('directory');
    });

    it('can be created from string', function () {
        expect(InstructionDiscoveryPathKind::from('file'))->toBe(InstructionDiscoveryPathKind::File)
            ->and(InstructionDiscoveryPathKind::from('directory'))->toBe(InstructionDiscoveryPathKind::Directory);
    });

    it('returns null for unknown value with tryFrom', function () {
        expect(InstructionDiscoveryPathKind::tryFrom('unknown'))->toBeNull();
    });
});

describe('SubagentSettingsEntryContextTier', function () {
    it('has all expected cases', function () {
        expect(SubagentSettingsEntryContextTier::Low->value)->toBe('low')
            ->and(SubagentSettingsEntryContextTier::Medium->value)->toBe('medium')
            ->and(SubagentSettingsEntryContextTier::High->value)->toBe('high');
    });

    it('can be created from string', function () {
        expect(SubagentSettingsEntryContextTier::from('low'))->toBe(SubagentSettingsEntryContextTier::Low)
            ->and(SubagentSettingsEntryContextTier::from('medium'))->toBe(SubagentSettingsEntryContextTier::Medium)
            ->and(SubagentSettingsEntryContextTier::from('high'))->toBe(SubagentSettingsEntryContextTier::High);
    });

    it('returns null for unknown value with tryFrom', function () {
        expect(SubagentSettingsEntryContextTier::tryFrom('unknown'))->toBeNull();
    });
});

describe('McpServerConfigDeferTools', function () {
    it('has all expected cases', function () {
        expect(McpServerConfigDeferTools::Eager->value)->toBe('eager')
            ->and(McpServerConfigDeferTools::Deferred->value)->toBe('deferred');
    });

    it('can be created from string', function () {
        expect(McpServerConfigDeferTools::from('eager'))->toBe(McpServerConfigDeferTools::Eager)
            ->and(McpServerConfigDeferTools::from('deferred'))->toBe(McpServerConfigDeferTools::Deferred);
    });

    it('returns null for unknown value with tryFrom', function () {
        expect(McpServerConfigDeferTools::tryFrom('unknown'))->toBeNull();
    });
});
