<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\ProviderConfigTransport;

/** @experimental */
readonly class SavedProviderConfigurationProviderInput implements Arrayable
{
    /**
     * @param  array{apiVersion?: string}|null  $azure
     */
    public function __construct(
        public string $name,
        public string $baseUrl,
        public ?string $type = null,
        public ?string $wireApi = null,
        public ProviderConfigTransport|string|null $transport = null,
        public mixed $localModel = null,
        public ?array $azure = null,
        public ?string $modelProvider = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: Arr::string($data, 'name', ''),
            baseUrl: Arr::string($data, 'baseUrl', ''),
            type: $data['type'] ?? null,
            wireApi: $data['wireApi'] ?? null,
            transport: $data['transport'] ?? null,
            localModel: $data['localModel'] ?? null,
            azure: $data['azure'] ?? null,
            modelProvider: $data['modelProvider'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'baseUrl' => $this->baseUrl,
            'type' => $this->type,
            'wireApi' => $this->wireApi,
            'transport' => $this->transport instanceof ProviderConfigTransport ? $this->transport->value : $this->transport,
            'localModel' => $this->localModel,
            'azure' => $this->azure,
            'modelProvider' => $this->modelProvider,
        ], fn ($value) => $value !== null);
    }
}
