<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\FileLogLevel;

/** @experimental */
readonly class LoggingFileOpenRequest implements Arrayable
{
    /**
     * @param  string[]|null  $debugEnvironmentVariables
     * @param  string[]|null  $secretValues
     */
    public function __construct(
        public ?FileLogLevel $level = null,
        public ?array $debugEnvironmentVariables = null,
        public ?array $secretValues = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            level: isset($data['level']) ? FileLogLevel::from(Arr::string($data, 'level')) : null,
            debugEnvironmentVariables: isset($data['debugEnvironmentVariables'])
                ? array_map(
                    static fn (string $name): string => $name,
                    Arr::array($data, 'debugEnvironmentVariables'),
                )
                : null,
            secretValues: isset($data['secretValues'])
                ? array_map(
                    static fn (string $value): string => $value,
                    Arr::array($data, 'secretValues'),
                )
                : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'level' => $this->level?->value,
            'debugEnvironmentVariables' => $this->debugEnvironmentVariables,
            'secretValues' => $this->secretValues,
        ], static fn ($value): bool => $value !== null);
    }
}
