<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/** @experimental */
readonly class GitHubFetchHttpError implements Arrayable
{
    public function __construct(
        public string $message,
        public ?string $responseBodyJson = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            message: Arr::string($data, 'message', ''),
            responseBodyJson: isset($data['responseBodyJson']) ? Arr::string($data, 'responseBodyJson') : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'message' => $this->message,
            'responseBodyJson' => $this->responseBodyJson,
        ], static fn ($value): bool => $value !== null);
    }
}
