<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/** Queued repository startup prompts and the hook command count after loading. */
readonly class SessionLoadDeferredRepoHooksResult implements Arrayable
{
    /**
     * @param  array<int, string>  $startupPrompts
     */
    public function __construct(
        public int $hookCount,
        public array $startupPrompts = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            startupPrompts: $data['startupPrompts'] ?? [],
            hookCount: Arr::integer($data, 'hookCount'),
        );
    }

    public function toArray(): array
    {
        return [
            'startupPrompts' => $this->startupPrompts,
            'hookCount' => $this->hookCount,
        ];
    }
}
