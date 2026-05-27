<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Support\PermissionDecision;

/**
 * Parameters for handling a pending permission request.
 *
 * The result array must contain a "kind" key matching the generated
 * PermissionDecision union, such as "approve-once", "approve-for-session",
 * "approve-permanently", "reject", or "user-not-available".
 *
 * Use {@see PermissionDecision} for building result arrays.
 */
readonly class PermissionDecisionRequest implements Arrayable
{
    /**
     * @param  string  $requestId  The ID of the pending permission request to handle
     * @param  array  $result  Permission decision result; see class docblock for structure
     */
    public function __construct(
        public string $requestId,
        public array $result,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            requestId: $data['requestId'],
            result: $data['result'],
        );
    }

    public function toArray(): array
    {
        return [
            'requestId' => $this->requestId,
            'result' => $this->result,
        ];
    }
}
