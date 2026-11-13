<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Soft plugin defaults for a session, without persistent user preferences or hard policy.
 *
 * @experimental
 */
readonly class SessionPluginDefaults implements Arrayable
{
    /**
     * @param  array<string, bool>  $defaultEnabledPlugins
     * @param  array<string, bool>  $teamDefaultEnabledPlugins
     * @param  array<string, TeamPluginMarketplace>  $extraKnownMarketplaces
     */
    public function __construct(
        public string $generation,
        public array $defaultEnabledPlugins,
        public array $teamDefaultEnabledPlugins,
        public array $extraKnownMarketplaces,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            generation: $data['generation'],
            defaultEnabledPlugins: $data['defaultEnabledPlugins'],
            teamDefaultEnabledPlugins: $data['teamDefaultEnabledPlugins'],
            extraKnownMarketplaces: array_map(
                static fn (TeamPluginMarketplace|array $marketplace) => $marketplace instanceof TeamPluginMarketplace
                    ? $marketplace
                    : TeamPluginMarketplace::fromArray($marketplace),
                $data['extraKnownMarketplaces'],
            ),
        );
    }

    public function toArray(): array
    {
        return [
            'generation' => $this->generation,
            'defaultEnabledPlugins' => $this->defaultEnabledPlugins,
            'teamDefaultEnabledPlugins' => $this->teamDefaultEnabledPlugins,
            'extraKnownMarketplaces' => array_map(
                static fn (TeamPluginMarketplace $marketplace) => $marketplace->toArray(),
                $this->extraKnownMarketplaces,
            ),
        ];
    }
}
