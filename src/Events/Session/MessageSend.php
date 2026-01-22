<?php

declare(strict_types=1);

namespace Revolution\Copilot\Events\Session;

use Illuminate\Foundation\Events\Dispatchable;

class MessageSend
{
    use Dispatchable;

    public function __construct(
        public string $session_id,
        public string $message_id,
        public string $prompt,
        public ?array $attachments = null,
        public ?string $mode = null,
    ) {}
}
