<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\AuthLoginStepKind;

readonly class AuthLoginStep implements Arrayable
{
    public function __construct(
        public AuthLoginStepKind|string $kind,
        public ?string $url = null,
        public ?string $prompt = null,
        public ?string $message = null,
        public ?AuthLoginResultDto $result = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            kind: AuthLoginStepKind::tryFrom($data['kind'] ?? '') ?? ($data['kind'] ?? ''),
            url: $data['url'] ?? null,
            prompt: $data['prompt'] ?? null,
            message: $data['message'] ?? null,
            result: isset($data['result']) && is_array($data['result'])
                ? AuthLoginResultDto::fromArray($data['result'])
                : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'kind' => $this->kind instanceof AuthLoginStepKind ? $this->kind->value : $this->kind,
            'url' => $this->url,
            'prompt' => $this->prompt,
            'message' => $this->message,
            'result' => $this->result?->toArray(),
        ], static fn ($value): bool => $value !== null);
    }
}
