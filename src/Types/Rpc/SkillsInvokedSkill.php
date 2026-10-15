<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Information about a skill invoked during a session.
 */
readonly class SkillsInvokedSkill implements Arrayable
{
    /**
     * @param  string[]|null  $allowedTools
     */
    public function __construct(
        public string $name,
        public string $path,
        public string $content,
        public int $invokedAtTurn,
        public ?array $allowedTools = null,
        public ?bool $disableModelInvocation = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: Arr::string($data, 'name'),
            path: Arr::string($data, 'path'),
            content: Arr::string($data, 'content'),
            allowedTools: $data['allowedTools'] ?? null,
            disableModelInvocation: $data['disableModelInvocation'] ?? null,
            invokedAtTurn: Arr::integer($data, 'invokedAtTurn'),
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'path' => $this->path,
            'content' => $this->content,
            'allowedTools' => $this->allowedTools,
            'disableModelInvocation' => $this->disableModelInvocation,
            'invokedAtTurn' => $this->invokedAtTurn,
        ], fn ($value) => $value !== null);
    }
}
