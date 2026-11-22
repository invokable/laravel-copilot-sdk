<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/** @experimental */
readonly class SessionImportCounts implements Arrayable
{
    public function __construct(
        public int $sessions,
        public int $messages,
        public int $toolCalls,
        public int $toolResults,
        public int $attachments,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            sessions: Arr::integer($data, 'sessions'),
            messages: Arr::integer($data, 'messages'),
            toolCalls: Arr::integer($data, 'toolCalls'),
            toolResults: Arr::integer($data, 'toolResults'),
            attachments: Arr::integer($data, 'attachments'),
        );
    }

    public function toArray(): array
    {
        return [
            'sessions' => $this->sessions,
            'messages' => $this->messages,
            'toolCalls' => $this->toolCalls,
            'toolResults' => $this->toolResults,
            'attachments' => $this->attachments,
        ];
    }
}
