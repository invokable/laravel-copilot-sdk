<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Category of instruction source — used for merge logic.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
enum InstructionSourceType: string
{
    case HOME = 'home';
    case REPO = 'repo';
    case MODEL = 'model';
    case VSCODE = 'vscode';
    case NESTED_AGENTS = 'nested-agents';
    case CHILD_INSTRUCTIONS = 'child-instructions';
}
