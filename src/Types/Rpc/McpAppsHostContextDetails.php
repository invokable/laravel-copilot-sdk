<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\MCPAppsDisplayMode;
use Revolution\Copilot\Enums\MCPAppsHostContextDetailsPlatform;

/**
 * Current host context advertised to MCP App guests.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class McpAppsHostContextDetails implements Arrayable
{
    private const KNOWN_KEYS = [
        'theme',
        'locale',
        'timeZone',
        'displayMode',
        'availableDisplayModes',
        'platform',
        'userAgent',
    ];

    /**
     * @param  array<MCPAppsDisplayMode|string>|null  $availableDisplayModes  Display modes the host supports.
     * @param  array<string, mixed>  $additionalProperties  Unrecognized host context fields.
     */
    public function __construct(
        public ?string $theme = null,
        public ?string $locale = null,
        public ?string $timeZone = null,
        public MCPAppsDisplayMode|string|null $displayMode = null,
        public ?array $availableDisplayModes = null,
        public MCPAppsHostContextDetailsPlatform|string|null $platform = null,
        public ?string $userAgent = null,
        public array $additionalProperties = [],
    ) {}

    public static function fromArray(array $data): self
    {
        $displayMode = $data['displayMode'] ?? null;
        $platform = $data['platform'] ?? null;

        return new self(
            theme: isset($data['theme']) ? (string) $data['theme'] : null,
            locale: isset($data['locale']) ? (string) $data['locale'] : null,
            timeZone: isset($data['timeZone']) ? (string) $data['timeZone'] : null,
            displayMode: is_string($displayMode) ? MCPAppsDisplayMode::tryFrom($displayMode) ?? $displayMode : null,
            availableDisplayModes: isset($data['availableDisplayModes'])
                ? array_map(fn (string $mode) => MCPAppsDisplayMode::tryFrom($mode) ?? $mode, $data['availableDisplayModes'])
                : null,
            platform: is_string($platform) ? MCPAppsHostContextDetailsPlatform::tryFrom($platform) ?? $platform : null,
            userAgent: isset($data['userAgent']) ? (string) $data['userAgent'] : null,
            additionalProperties: array_diff_key($data, array_flip(self::KNOWN_KEYS)),
        );
    }

    public function toArray(): array
    {
        return array_merge(
            array_filter([
                'theme' => $this->theme,
                'locale' => $this->locale,
                'timeZone' => $this->timeZone,
                'displayMode' => $this->displayMode instanceof MCPAppsDisplayMode ? $this->displayMode->value : $this->displayMode,
                'availableDisplayModes' => $this->availableDisplayModes === null
                    ? null
                    : array_map(
                        fn (MCPAppsDisplayMode|string $mode) => $mode instanceof MCPAppsDisplayMode ? $mode->value : $mode,
                        $this->availableDisplayModes,
                    ),
                'platform' => $this->platform instanceof MCPAppsHostContextDetailsPlatform ? $this->platform->value : $this->platform,
                'userAgent' => $this->userAgent,
            ], fn ($value) => $value !== null),
            $this->additionalProperties,
        );
    }
}
