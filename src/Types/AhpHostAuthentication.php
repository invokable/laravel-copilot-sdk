<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types;

use Closure;
use InvalidArgumentException;
use Revolution\Copilot\Enums\HostAuthenticationMode;
use Revolution\Copilot\Types\Rpc\HostAuthentication;

/** High-level AHP authentication configuration, including the local validator callback. */
final readonly class AhpHostAuthentication
{
    private function __construct(
        public HostAuthenticationMode $mode,
        public ?string $requireIdentity = null,
        public ?Closure $authenticate = null,
    ) {}

    public static function github(): self
    {
        return new self(HostAuthenticationMode::GitHub);
    }

    public static function none(): self
    {
        return new self(HostAuthenticationMode::None);
    }

    public static function custom(string $requireIdentity, callable $authenticate): self
    {
        if (trim($requireIdentity) === '') {
            throw new InvalidArgumentException('Custom AHP authentication requires a nonempty requireIdentity.');
        }

        return new self(
            mode: HostAuthenticationMode::Custom,
            requireIdentity: $requireIdentity,
            authenticate: Closure::fromCallable($authenticate),
        );
    }

    public static function fromArray(array $data): self
    {
        $mode = HostAuthenticationMode::from($data['mode'] ?? '');

        if ($mode !== HostAuthenticationMode::Custom
            && (isset($data['requireIdentity']) || isset($data['authenticate']))) {
            throw new InvalidArgumentException('requireIdentity and authenticate are only valid for custom authentication.');
        }

        return match ($mode) {
            HostAuthenticationMode::GitHub => self::github(),
            HostAuthenticationMode::None => self::none(),
            HostAuthenticationMode::Custom => is_string($data['requireIdentity'] ?? null)
                && is_callable($data['authenticate'] ?? null)
                ? self::custom($data['requireIdentity'], $data['authenticate'])
                : throw new InvalidArgumentException('Custom AHP authentication requires a nonempty identity and authenticate callback.'),
        };
    }

    public function toRpcType(): HostAuthentication
    {
        return new HostAuthentication(
            mode: $this->mode,
            requireIdentity: $this->requireIdentity,
        );
    }
}
