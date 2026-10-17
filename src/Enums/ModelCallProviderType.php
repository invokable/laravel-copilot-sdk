<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

enum ModelCallProviderType: string
{
    case OPENAI = 'openai';
    case AZURE = 'azure';
    case ANTHROPIC = 'anthropic';
    case LOCAL_MODEL = 'local_model';
    case WINML = 'winml';
    case OTHER = 'other';
}
