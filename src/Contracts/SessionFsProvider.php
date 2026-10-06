<?php

declare(strict_types=1);

namespace Revolution\Copilot\Contracts;

/**
 * Session-scoped backing store for SessionFs text and optional binary files.
 *
 * Binary methods are optional; advertise the binary capability only when both
 * methods are implemented.
 */
interface SessionFsProvider
{
    public function readFile(string $path): string;

    public function writeFile(string $path, string $content, ?int $mode = null): void;
}
