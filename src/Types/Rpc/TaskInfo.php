<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

/**
 * Factory and serializer for the task info union.
 *
 * @experimental This API is experimental and may change or be removed.
 */
final class TaskInfo
{
    public static function fromArray(array $data): TaskAgentInfo|TaskClientInfo|TaskShellInfo
    {
        return match ($data['type'] ?? '') {
            'client' => TaskClientInfo::fromArray($data),
            'shell' => TaskShellInfo::fromArray($data),
            default => TaskAgentInfo::fromArray($data),
        };
    }

    /**
     * @return array<string, mixed>
     */
    public static function toArray(TaskAgentInfo|TaskClientInfo|TaskShellInfo $task): array
    {
        return $task->toArray();
    }
}
