<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/** The selectable tool groups in canonical display order. */
readonly class AgentsListToolCategoriesResult implements Arrayable
{
    /** @param AgentToolCategory[] $categories */
    public function __construct(public array $categories = []) {}

    public static function fromArray(array $data): self
    {
        return new self(categories: array_map(
            static fn (array $category): AgentToolCategory => AgentToolCategory::fromArray($category),
            Arr::array($data, 'categories', []),
        ));
    }

    public function toArray(): array
    {
        return [
            'categories' => array_map(
                static fn (AgentToolCategory $category): array => $category->toArray(),
                $this->categories,
            ),
        ];
    }
}
