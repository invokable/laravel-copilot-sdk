<?php

declare(strict_types=1);

namespace Revolution\Copilot\Contracts;

/**
 * Adds exact binary IO to a SessionFs provider.
 */
interface SessionFsBinaryProvider extends SessionFsProvider
{
    public function readFileBytes(string $path): string;

    public function writeFileBytes(string $path, string $content, ?int $mode = null): void;
}
