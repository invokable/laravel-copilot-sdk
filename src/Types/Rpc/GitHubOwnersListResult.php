<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

readonly class GitHubOwnersListResult implements Arrayable
{
    /** @param GitHubOwnerOption[]|null $owners */
    public function __construct(
        public ?array $owners = null,
        public ?string $message = null,
        public ?string $throwError = null,
        public ?string $warning = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            owners: isset($data['owners'])
                ? array_map(fn (array $owner) => GitHubOwnerOption::fromArray($owner), $data['owners'])
                : null,
            message: $data['message'] ?? null,
            throwError: $data['throwError'] ?? null,
            warning: $data['warning'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'owners' => $this->owners === null
                ? null
                : array_map(fn (GitHubOwnerOption $owner) => $owner->toArray(), $this->owners),
            'message' => $this->message,
            'throwError' => $this->throwError,
            'warning' => $this->warning,
        ], static fn ($value) => $value !== null);
    }
}
