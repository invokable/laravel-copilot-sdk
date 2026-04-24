<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Authentication type for session auth status.
 */
enum AuthInfoType: string
{
    case API_KEY = 'api-key';
    case COPILOT_API_TOKEN = 'copilot-api-token';
    case ENV = 'env';
    case GH_CLI = 'gh-cli';
    case HMAC = 'hmac';
    case TOKEN = 'token';
    case USER = 'user';
}
