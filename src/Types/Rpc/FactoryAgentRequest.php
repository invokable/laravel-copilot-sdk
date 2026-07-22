<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Parameters for one factory-scoped subagent call.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class FactoryAgentRequest implements Arrayable
{
    /**
     * @param  string  $factoryRunId  Factory run identifier that owns the subagent.
     * @param  FactoryAgentOptions|array  $opts  Subagent execution options.
     * @param  string  $prompt  Prompt to send to the subagent.
     */
    public function __construct(
        public string $factoryRunId,
        public FactoryAgentOptions|array $opts,
        public string $prompt,
    ) {}

    public static function fromArray(array $data): self
    {
        $opts = $data['opts'] ?? [];

        return new self(
            factoryRunId: Arr::string($data, 'factoryRunId'),
            opts: $opts instanceof FactoryAgentOptions ? $opts : FactoryAgentOptions::fromArray($opts),
            prompt: Arr::string($data, 'prompt'),
        );
    }

    public function toArray(): array
    {
        $opts = $this->opts instanceof FactoryAgentOptions ? $this->opts->toArray() : $this->opts;

        return [
            'factoryRunId' => $this->factoryRunId,
            'opts' => $opts,
            'prompt' => $this->prompt,
        ];
    }
}
