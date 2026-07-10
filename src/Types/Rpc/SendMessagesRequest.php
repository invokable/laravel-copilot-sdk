<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Parameters for sending zero or more user messages to the session in a single turn.
 *
 * Remote-backed (Mission Control) sessions do not support this method and will return an error.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SendMessagesRequest implements Arrayable
{
    /**
     * @param  SendMessageItem[]  $messages  The user messages to append to the conversation, in order
     * @param  string|null  $mode  How to deliver the messages (`enqueue` or `immediate`)
     * @param  bool|null  $prepend  If true, adds the messages to the front of the queue
     * @param  string|null  $agentMode  The UI mode the agent was in when these messages were sent
     * @param  array|null  $requestHeaders  Custom HTTP headers to include in outbound model requests
     * @param  string|null  $traceparent  W3C Trace Context traceparent header
     * @param  string|null  $tracestate  W3C Trace Context tracestate header
     * @param  bool|null  $wait  If true, await completion of the agentic loop before returning
     */
    public function __construct(
        public array $messages,
        public ?string $mode = null,
        public ?bool $prepend = null,
        public ?string $agentMode = null,
        public ?array $requestHeaders = null,
        public ?string $traceparent = null,
        public ?string $tracestate = null,
        public ?bool $wait = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            messages: array_map(
                fn (array $m) => SendMessageItem::fromArray($m),
                $data['messages'] ?? [],
            ),
            mode: $data['mode'] ?? null,
            prepend: $data['prepend'] ?? null,
            agentMode: $data['agentMode'] ?? null,
            requestHeaders: $data['requestHeaders'] ?? null,
            traceparent: $data['traceparent'] ?? null,
            tracestate: $data['tracestate'] ?? null,
            wait: $data['wait'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'messages' => array_map(fn (SendMessageItem $m) => $m->toArray(), $this->messages),
            'mode' => $this->mode,
            'prepend' => $this->prepend,
            'agentMode' => $this->agentMode,
            'requestHeaders' => $this->requestHeaders,
            'traceparent' => $this->traceparent,
            'tracestate' => $this->tracestate,
            'wait' => $this->wait,
        ], fn ($v) => $v !== null);
    }
}
