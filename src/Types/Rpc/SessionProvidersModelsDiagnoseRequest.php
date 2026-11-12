<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Request for checking cached availability of a provider model.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionProvidersModelsDiagnoseRequest implements Arrayable
{
    /**
     * @param  string  $selectionId  Provider-qualified model selection identifier.
     * @param  ?string  $providerId  Optional provider identity used to disambiguate a selection.
     */
    public function __construct(
        public string $selectionId,
        public ?string $providerId = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            selectionId: Arr::string($data, 'selectionId'),
            providerId: isset($data['providerId']) ? Arr::string($data, 'providerId') : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'selectionId' => $this->selectionId,
            'providerId' => $this->providerId,
        ], fn ($value) => $value !== null);
    }
}
