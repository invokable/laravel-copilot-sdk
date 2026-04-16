<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Request for disabling specific skills globally.
 */
readonly class SkillsConfigSetDisabledSkillsRequest implements Arrayable
{
    /**
     * @param  array<string>  $disabledSkills  List of skill names to disable
     */
    public function __construct(
        public array $disabledSkills,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            disabledSkills: $data['disabledSkills'] ?? [],
        );
    }

    public function toArray(): array
    {
        return [
            'disabledSkills' => $this->disabledSkills,
        ];
    }
}
