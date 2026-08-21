<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\CatalogInvalidRequestField;

/**
 * The request was rejected because a bounded field fell outside its permitted range.
 *
 * @experimental
 */
readonly class CatalogInvalidRequestError implements Arrayable
{
    /** @var string */
    public string $kind;

    public function __construct(
        public CatalogInvalidRequestField $field,
        public string $message,
    ) {
        $this->kind = 'invalid-request';
    }

    public static function fromArray(array $data): static
    {
        return new static(
            field: CatalogInvalidRequestField::from($data['field']),
            message: $data['message'],
        );
    }

    public function toArray(): array
    {
        return [
            'kind' => $this->kind,
            'field' => $this->field->value,
            'message' => $this->message,
        ];
    }
}
