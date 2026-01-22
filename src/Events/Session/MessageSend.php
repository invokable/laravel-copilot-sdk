<?php

declare(strict_types=1);

namespace Revolution\Copilot\Events\Session;

use Illuminate\Foundation\Events\Dispatchable;

class MessageSend
{
    use Dispatchable;

    public function __construct(
        public string $sessionId,
        public string $messageId,
        public string $prompt,
        public ?array $attachments = null,
        public ?string $mode = null,
    ) {}
}
