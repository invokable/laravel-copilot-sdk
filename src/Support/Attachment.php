<?php

declare(strict_types=1);

namespace Revolution\Copilot\Support;

use Revolution\Copilot\Enums\AttachmentType;

class Attachment
{
    public static function file(string $path, ?string $displayName = null): array
    {
        return array_filter([
            'type' => AttachmentType::FILE->value,
            'path' => $path,
            'displayName' => $displayName,
        ]);
    }

    public static function directory(string $path, ?string $displayName = null): array
    {
        return array_filter([
            'type' => AttachmentType::DIRECTORY->value,
            'path' => $path,
            'displayName' => $displayName,
        ]);
    }

    public static function selection(string $filePath, string $displayName, ?array $selection = null, ?string $text = null): array
    {
        return array_filter([
            'type' => AttachmentType::SELECTION->value,
            'filePath' => $filePath,
            'displayName' => $displayName,
            'selection' => $selection,
            'text' => $text,
        ]);
    }

    /**
     * Create a blob attachment for inline base64-encoded content (e.g. images).
     */
    public static function blob(string $data, string $mimeType, ?string $displayName = null): array
    {
        return array_filter([
            'type' => AttachmentType::BLOB->value,
            'data' => $data,
            'mimeType' => $mimeType,
            'displayName' => $displayName,
        ]);
    }
}
