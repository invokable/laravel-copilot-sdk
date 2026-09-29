<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/** @experimental */
readonly class AuthReadValue implements Arrayable
{
    /**
     * @param  array<AuthValidationError>  $errors
     */
    public function __construct(
        public string $kind,
        public ?AccountStatus $account = null,
        public ?AuthStatusDto $status = null,
        public array $errors = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            kind: (string) ($data['kind'] ?? ''),
            account: isset($data['account']) && is_array($data['account'])
                ? AccountStatus::fromArray($data['account'])
                : null,
            status: isset($data['status']) && is_array($data['status'])
                ? AuthStatusDto::fromArray($data['status'])
                : null,
            errors: array_map(
                static fn (array $error): AuthValidationError => AuthValidationError::fromArray($error),
                array_values(array_filter($data['errors'] ?? [], 'is_array')),
            ),
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'kind' => $this->kind,
            'account' => $this->account?->toArray(),
            'status' => $this->status?->toArray(),
            'errors' => array_map(
                static fn (AuthValidationError $error): array => $error->toArray(),
                $this->errors,
            ),
        ], static fn ($value): bool => $value !== null);
    }
}
