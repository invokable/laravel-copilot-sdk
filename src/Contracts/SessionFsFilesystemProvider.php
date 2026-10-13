<?php

declare(strict_types=1);

namespace Revolution\Copilot\Contracts;

use Revolution\Copilot\Types\Rpc\SessionFsEntry;
use Revolution\Copilot\Types\Rpc\SessionFsStatResult;

/**
 * Adds the full filesystem surface the runtime may call on a SessionFs provider.
 *
 * Providers signal errors by throwing. An exception whose `code` is `ENOENT` (or the
 * POSIX errno `2`) is mapped to the ENOENT error code; everything else maps to UNKNOWN.
 *
 * This is a separate interface so providers that only implement {@see SessionFsProvider}
 * keep working; calls to the methods below then return an "unsupported" error.
 */
interface SessionFsFilesystemProvider extends SessionFsProvider
{
    /**
     * Append content to a file, creating parent directories if needed.
     */
    public function appendFile(string $path, string $content, ?int $mode = null): void;

    /**
     * Check whether a path exists.
     */
    public function exists(string $path): bool;

    /**
     * Get metadata about a file or directory. Throw if it does not exist.
     */
    public function stat(string $path): SessionFsStatResult;

    /**
     * Create a directory. When $recursive is true, create parents as needed.
     */
    public function mkdir(string $path, bool $recursive = false, ?int $mode = null): void;

    /**
     * List entry names in a directory. Throw if it does not exist.
     *
     * @return list<string>
     */
    public function readdir(string $path): array;

    /**
     * List directory entries with type info. Throw if the directory does not exist.
     *
     * @return list<SessionFsEntry>
     */
    public function readdirWithTypes(string $path): array;

    /**
     * Remove a file or directory. When $force is true, do not throw on ENOENT.
     */
    public function rm(string $path, bool $recursive = false, bool $force = false): void;

    /**
     * Rename or move a file or directory.
     */
    public function rename(string $src, string $dest): void;
}
