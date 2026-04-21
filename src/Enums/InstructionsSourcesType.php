<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Category of instruction source — used for merge logic.
 */
enum InstructionsSourcesType: string
{
    case HOME = 'home';
    case REPO = 'repo';
    case MODEL = 'model';
    case VSCODE = 'vscode';
    case NESTED_AGENTS = 'nested-agents';
    case CHILD_INSTRUCTIONS = 'child-instructions';
}
