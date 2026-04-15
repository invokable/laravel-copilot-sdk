<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Parameters for responding to a UI elicitation request.
 */
readonly class UIElicitationRequest implements Arrayable
{
    /**
     * @param  string  $message  Message describing what information is needed from the user
     * @param  array  $requestedSchema  JSON Schema describing the form fields to present to the user
     */
    public function __construct(
        public string $message,
        public array $requestedSchema,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            message: $data['message'],
            requestedSchema: $data['requestedSchema'],
        );
    }

    public function toArray(): array
    {
        return [
            'message' => $this->message,
            'requestedSchema' => $this->requestedSchema,
        ];
    }
}
