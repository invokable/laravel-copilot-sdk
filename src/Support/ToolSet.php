<?php

declare(strict_types=1);

namespace Revolution\Copilot\Support;

use Illuminate\Contracts\Support\Arrayable;
use InvalidArgumentException;

/**
 * Builder for source-qualified session tool filters.
 *
 * @implements Arrayable<int, string>
 */
class ToolSet implements Arrayable
{
    /**
     * @var list<string>
     */
    protected array $items = [];

    /**
     * Add one or more built-in tool filters.
     *
     * @param  string|list<string>  $name
     */
    public function addBuiltIn(string|array $name): static
    {
        $names = is_string($name) ? [$name] : $name;

        foreach ($names as $item) {
            $this->validateName('builtin', $item);
            $this->items[] = "builtin:{$item}";
        }

        return $this;
    }

    public function addCustom(string $name): static
    {
        $this->validateName('custom', $name);
        $this->items[] = "custom:{$name}";

        return $this;
    }

    public function addMcp(string $name): static
    {
        $this->validateName('mcp', $name);
        $this->items[] = "mcp:{$name}";

        return $this;
    }

    /**
     * Return a copy of the accumulated tool filters.
     *
     * @return list<string>
     */
    public function toArray(): array
    {
        return $this->items;
    }

    private function validateName(string $kind, string $name): void
    {
        if ($name !== '*' && preg_match('/^[a-zA-Z0-9_-]+$/', $name) !== 1) {
            throw new InvalidArgumentException(
                "Invalid {$kind} tool name '{$name}': tool names must match /^[a-zA-Z0-9_-]+$/ or be the wildcard '*'.",
            );
        }
    }
}
