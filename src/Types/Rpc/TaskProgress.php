<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

/**
 * Factory and serializer for the task progress union.
 *
 * @experimental This API is experimental and may change or be removed.
 */
final class TaskProgress
{
    public static function fromArray(?array $data): TaskAgentProgress|TaskClientProgress|TaskShellProgress|null
    {
        if ($data === null) {
            return null;
        }

        return match ($data['type'] ?? '') {
            'client' => TaskClientProgress::fromArray($data),
            'shell' => TaskShellProgress::fromArray($data),
            default => TaskAgentProgress::fromArray($data),
        };
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function toArray(TaskAgentProgress|TaskClientProgress|TaskShellProgress|null $progress): ?array
    {
        return $progress?->toArray();
    }
}
