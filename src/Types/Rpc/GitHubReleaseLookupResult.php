<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\GitHubReleaseLookupKind;

/** @experimental */
readonly class GitHubReleaseLookupResult implements Arrayable
{
    public function __construct(
        public GitHubReleaseLookupKind $kind,
        public ?string $releaseJson = null,
        public ?string $message = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            kind: GitHubReleaseLookupKind::from(Arr::string($data, 'kind', '')),
            releaseJson: isset($data['releaseJson']) ? Arr::string($data, 'releaseJson') : null,
            message: isset($data['message']) ? Arr::string($data, 'message') : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'kind' => $this->kind->value,
            'releaseJson' => $this->releaseJson,
            'message' => $this->message,
        ], static fn ($value): bool => $value !== null);
    }
}
