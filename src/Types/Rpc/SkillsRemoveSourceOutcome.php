<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\SkillsRemoveSourceOutcomeKind;

/**
 * Direct removal result or a request for the inventory needed to resolve a file.
 *
 * @experimental
 */
readonly class SkillsRemoveSourceOutcome implements Arrayable
{
    public function __construct(
        public SkillsRemoveSourceOutcomeKind $kind,
        public ?SkillsRemoveSourceResult $result = null,
    ) {}

    public static function fromArray(array $data): static
    {
        $kind = SkillsRemoveSourceOutcomeKind::from($data['kind']);

        return new static(
            kind: $kind,
            result: $kind === SkillsRemoveSourceOutcomeKind::Removed
                ? SkillsRemoveSourceResult::fromArray($data['result'])
                : null,
        );
    }

    public function toArray(): array
    {
        $data = ['kind' => $this->kind->value];

        if ($this->result !== null) {
            $data['result'] = $this->result->toArray();
        }

        return $data;
    }
}
