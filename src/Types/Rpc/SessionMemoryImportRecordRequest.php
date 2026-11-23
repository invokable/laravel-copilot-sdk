<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\MemoryImportScope;

/** @experimental */
readonly class SessionMemoryImportRecordRequest implements Arrayable
{
    public function __construct(
        public MemoryImportScope|string $scope,
        public string $subject,
        public string $fact,
        public string $reason,
        public string $citations,
    ) {}

    public static function fromArray(array $data): self
    {
        $scope = Arr::string($data, 'scope');

        return new self(
            scope: MemoryImportScope::tryFrom($scope) ?? $scope,
            subject: Arr::string($data, 'subject'),
            fact: Arr::string($data, 'fact'),
            reason: Arr::string($data, 'reason'),
            citations: Arr::string($data, 'citations'),
        );
    }

    public function toArray(): array
    {
        return [
            'scope' => $this->scope instanceof MemoryImportScope ? $this->scope->value : $this->scope,
            'subject' => $this->subject,
            'fact' => $this->fact,
            'reason' => $this->reason,
            'citations' => $this->citations,
        ];
    }
}
