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
            ->and(AgentDiscoveryPathScope::Project->value)->toBe('project');
    });

    it('can be created from string', function () {
        expect(AgentDiscoveryPathScope::from('user'))->toBe(AgentDiscoveryPathScope::User)
            ->and(AgentDiscoveryPathScope::from('project'))->toBe(AgentDiscoveryPathScope::Project);
    });

    it('returns null for unknown value with tryFrom', function () {
        expect(AgentDiscoveryPathScope::tryFrom('unknown'))->toBeNull();
    });
});

describe('SkillDiscoveryScope', function () {
    it('has all expected cases', function () {
        expect(SkillDiscoveryScope::Project->value)->toBe('project')
            ->and(SkillDiscoveryScope::PersonalCopilot->value)->toBe('personal-copilot')
            ->and(SkillDiscoveryScope::PersonalAgents->value)->toBe('personal-agents')
            ->and(SkillDiscoveryScope::Custom->value)->toBe('custom');
    });

    it('can be created from string', function () {
        expect(SkillDiscoveryScope::from('project'))->toBe(SkillDiscoveryScope::Project)
            ->and(SkillDiscoveryScope::from('personal-copilot'))->toBe(SkillDiscoveryScope::PersonalCopilot)
            ->and(SkillDiscoveryScope::from('personal-agents'))->toBe(SkillDiscoveryScope::PersonalAgents)
            ->and(SkillDiscoveryScope::from('custom'))->toBe(SkillDiscoveryScope::Custom);
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
        expect(SubagentSettingsEntryContextTier::Inherit->value)->toBe('inherit')
            ->and(SubagentSettingsEntryContextTier::Default->value)->toBe('default')
            ->and(SubagentSettingsEntryContextTier::LongContext->value)->toBe('long_context');
    });

    it('can be created from string', function () {
        expect(SubagentSettingsEntryContextTier::from('inherit'))->toBe(SubagentSettingsEntryContextTier::Inherit)
            ->and(SubagentSettingsEntryContextTier::from('default'))->toBe(SubagentSettingsEntryContextTier::Default)
            ->and(SubagentSettingsEntryContextTier::from('long_context'))->toBe(SubagentSettingsEntryContextTier::LongContext);
    });

    it('returns null for unknown value with tryFrom', function () {
        expect(SubagentSettingsEntryContextTier::tryFrom('unknown'))->toBeNull();
    });
});

describe('McpServerConfigDeferTools', function () {
    it('has all expected cases', function () {
        expect(McpServerConfigDeferTools::Auto->value)->toBe('auto')
            ->and(McpServerConfigDeferTools::Never->value)->toBe('never');
    });

    it('can be created from string', function () {
        expect(McpServerConfigDeferTools::from('auto'))->toBe(McpServerConfigDeferTools::Auto)
            ->and(McpServerConfigDeferTools::from('never'))->toBe(McpServerConfigDeferTools::Never);
    });

    it('returns null for unknown value with tryFrom', function () {
        expect(McpServerConfigDeferTools::tryFrom('unknown'))->toBeNull();
    });
});
