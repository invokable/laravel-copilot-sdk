<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * One language server in the runtime's configuration inventory.
 *
 * @experimental
 */
readonly class LspServerSummary implements Arrayable
{
    /**
     * @param  array<string, string>  $fileExtensions
     */
    public function __construct(
        public string $id,
        public array $fileExtensions,
        public bool $disabled,
        public ?string $rootUri = null,
        public ?float $requestTimeoutMs = null,
        public ?float $initializationTimeoutMs = null,
        public ?float $spawnTimeoutMs = null,
        public ?float $warmupTimeoutMs = null,
        public ?string $sourcePlugin = null,
        public ?string $launchDescription = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: Arr::string($data, 'id', ''),
            fileExtensions: $data['fileExtensions'] ?? [],
            disabled: Arr::boolean($data, 'disabled', false),
            rootUri: isset($data['rootUri']) ? Arr::string($data, 'rootUri') : null,
            requestTimeoutMs: isset($data['requestTimeoutMs'])
                ? (is_int($data['requestTimeoutMs'])
                    ? (float) Arr::integer($data, 'requestTimeoutMs')
                    : Arr::float($data, 'requestTimeoutMs'))
                : null,
            initializationTimeoutMs: isset($data['initializationTimeoutMs'])
                ? (is_int($data['initializationTimeoutMs'])
                    ? (float) Arr::integer($data, 'initializationTimeoutMs')
                    : Arr::float($data, 'initializationTimeoutMs'))
                : null,
            spawnTimeoutMs: isset($data['spawnTimeoutMs'])
                ? (is_int($data['spawnTimeoutMs'])
                    ? (float) Arr::integer($data, 'spawnTimeoutMs')
                    : Arr::float($data, 'spawnTimeoutMs'))
                : null,
            warmupTimeoutMs: isset($data['warmupTimeoutMs'])
                ? (is_int($data['warmupTimeoutMs'])
                    ? (float) Arr::integer($data, 'warmupTimeoutMs')
                    : Arr::float($data, 'warmupTimeoutMs'))
                : null,
            sourcePlugin: isset($data['sourcePlugin']) ? Arr::string($data, 'sourcePlugin') : null,
            launchDescription: isset($data['launchDescription']) ? Arr::string($data, 'launchDescription') : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'fileExtensions' => $this->fileExtensions,
            'disabled' => $this->disabled,
            'rootUri' => $this->rootUri,
            'requestTimeoutMs' => $this->requestTimeoutMs,
            'initializationTimeoutMs' => $this->initializationTimeoutMs,
            'spawnTimeoutMs' => $this->spawnTimeoutMs,
            'warmupTimeoutMs' => $this->warmupTimeoutMs,
            'sourcePlugin' => $this->sourcePlugin,
            'launchDescription' => $this->launchDescription,
        ], static fn ($value): bool => $value !== null);
    }
}
