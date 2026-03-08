<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Parameters for handling a pending permission request.
 *
 * The result array must contain a "kind" key with one of:
 * - "approved"
 * - "denied-by-rules" (with optional "rules" array)
 * - "denied-no-approval-rule-and-could-not-request-from-user"
 * - "denied-interactively-by-user" (with optional "feedback" string)
 * - "denied-by-content-exclusion-policy" (with required "path" and "message" strings)
 *
 * Use {@see \Revolution\Copilot\Support\PermissionRequestResultKind} for building result arrays.
 */
readonly class SessionPermissionsHandlePendingPermissionRequestParams implements Arrayable
{
    public function __construct(
        public string $requestId,
        /** Permission decision result; see class docblock for structure */
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
