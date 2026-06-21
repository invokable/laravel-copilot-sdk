<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

enum LlmInferenceHttpRequestStartTransport: string
{
    case Http = 'http';
    case Websocket = 'websocket';
}
