<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\AgentToolCategoryId;

/** A selectable custom-agent tool group. */
readonly class AgentToolCategory implements Arrayable
{
    /**
     * @param  string[]  $tools
     */
    public function __construct(
        public AgentToolCategoryId $id,
        public string $label,
        public array $tools,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: AgentToolCategoryId::from(Arr::string($data, 'id')),
            label: Arr::string($data, 'label'),
            tools: Arr::array($data, 'tools'),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id->value,
            'label' => $this->label,
            'tools' => $this->tools,
        ];
    }
}
