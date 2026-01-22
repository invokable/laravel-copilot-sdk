<?php

declare(strict_types=1);

namespace Revolution\Copilot\Events\Session;

use Illuminate\Foundation\Events\Dispatchable;
use Revolution\Copilot\Types\SessionEvent;

class MessageSendAndWait
{
    use Dispatchable;

    public function __construct(
        public string $sessionId,
        public ?SessionEvent $lastAssistantMessage,
        public string $prompt,
        public ?array $attachments = null,
        public ?string $mode = null,
    ) {}
}
