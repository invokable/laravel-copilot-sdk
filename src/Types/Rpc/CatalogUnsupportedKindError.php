<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\CatalogCandidateKind;

/**
 * The request asked for a candidate kind this runtime does not serve.
 *
 * @experimental
 */
readonly class CatalogUnsupportedKindError implements Arrayable
{
    /** @var string */
    public string $kind;

    /**
     * @param  CatalogCandidateKind[]  $requestedKinds
     * @param  CatalogCandidateKind[]  $supportedKinds
     */
    public function __construct(
        public array $requestedKinds,
        public array $supportedKinds,
        public string $message,
    ) {
        $this->kind = 'unsupported-kind';
    }

    public static function fromArray(array $data): static
    {
        return new static(
            requestedKinds: array_map(
                fn (string $kind) => CatalogCandidateKind::from($kind),
                $data['requestedKinds'] ?? [],
            ),
            supportedKinds: array_map(
                fn (string $kind) => CatalogCandidateKind::from($kind),
                $data['supportedKinds'] ?? [],
            ),
            message: $data['message'],
        );
    }

    public function toArray(): array
    {
        return [
            'kind' => $this->kind,
            'requestedKinds' => array_map(fn (CatalogCandidateKind $kind) => $kind->value, $this->requestedKinds),
            'supportedKinds' => array_map(fn (CatalogCandidateKind $kind) => $kind->value, $this->supportedKinds),
            'message' => $this->message,
        ];
    }
}
