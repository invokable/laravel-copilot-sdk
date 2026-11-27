<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * @experimental
 */
readonly class SkillsLoadDiagnostics implements Arrayable
{
    /**
     * @param  array<int, string>  $warnings
     * @param  array<int, string>  $errors
     */
    public function __construct(
        public array $warnings,
        public array $errors,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            warnings: Arr::array($data, 'warnings'),
            errors: Arr::array($data, 'errors'),
        );
    }

    public function toArray(): array
    {
        return [
            'warnings' => $this->warnings,
            'errors' => $this->errors,
        ];
    }
}
