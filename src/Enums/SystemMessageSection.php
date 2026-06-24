<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * System message section identifiers.
 *
 * These sections make up the structured system message that guides agent behavior.
 * Sections can be overridden using SystemMessageConfig in customize mode.
 */
enum SystemMessageSection: string
{
    /**
     * Agent identity preamble and mode statement
     */
    case PREAMBLE = 'preamble';

    /**
     * Section group covering the identity preamble and its sibling sub-sections (tone, tool efficiency, etc.)
     */
    case IDENTITY = 'identity';

    /**
     * Response style, conciseness rules, output formatting preferences
     */
    case TONE = 'tone';

    /**
     * Tool usage patterns, parallel calling, batching guidelines
     */
    case TOOL_EFFICIENCY = 'tool_efficiency';

    /**
     * CWD, OS, git root, directory listing, available tools
     */
    case ENVIRONMENT_CONTEXT = 'environment_context';

    /**
     * Coding rules, linting/testing, ecosystem tools, style
     */
    case CODE_CHANGE_RULES = 'code_change_rules';

    /**
     * Tips, behavioral best practices, behavioral guidelines
     */
    case GUIDELINES = 'guidelines';

    /**
     * Environment limitations, prohibited actions, security policies
     */
    case SAFETY = 'safety';

    /**
     * Per-tool usage instructions
     */
    case TOOL_INSTRUCTIONS = 'tool_instructions';

    /**
     * Repository and organization custom instructions
     */
    case CUSTOM_INSTRUCTIONS = 'custom_instructions';

    /**
     * Runtime-provided context and instructions
     * (e.g. system notifications, memories, workspace context,
     * mode-specific instructions, content-exclusion policy)
     */
    case RUNTIME_INSTRUCTIONS = 'runtime_instructions';

    /**
     * End-of-prompt instructions: parallel tool calling, persistence, task completion
     */
    case LAST_INSTRUCTIONS = 'last_instructions';

    /**
     * Get all section names with their descriptions.
     *
     * @return array<string, string>
     */
    public static function descriptions(): array
    {
        return [
            self::PREAMBLE->value => 'Agent identity preamble and mode statement',
            self::IDENTITY->value => 'Section group covering the identity preamble and its sibling sub-sections (tone, tool efficiency, etc.)',
            self::TONE->value => 'Response style, conciseness rules, output formatting preferences',
            self::TOOL_EFFICIENCY->value => 'Tool usage patterns, parallel calling, batching guidelines',
            self::ENVIRONMENT_CONTEXT->value => 'CWD, OS, git root, directory listing, available tools',
            self::CODE_CHANGE_RULES->value => 'Coding rules, linting/testing, ecosystem tools, style',
            self::GUIDELINES->value => 'Tips, behavioral best practices, behavioral guidelines',
            self::SAFETY->value => 'Environment limitations, prohibited actions, security policies',
            self::TOOL_INSTRUCTIONS->value => 'Per-tool usage instructions',
            self::CUSTOM_INSTRUCTIONS->value => 'Repository and organization custom instructions',
            self::RUNTIME_INSTRUCTIONS->value => 'Runtime-provided context and instructions (e.g. system notifications, memories, workspace context, mode-specific instructions, content-exclusion policy)',
            self::LAST_INSTRUCTIONS->value => 'End-of-prompt instructions: parallel tool calling, persistence, task completion',
        ];
    }
}
