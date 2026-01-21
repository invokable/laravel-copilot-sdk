<?php

declare(strict_types=1);

namespace Revolution\Copilot\Process;

/**
 * A simple wrapper for a raw proc_open process resource.
 */
class ProcessWrapper
{
    /**
     * The raw process resource.
     *
     * @var resource|null
     */
    private mixed $rawProcess;

    /**
     * Create a new ProcessWrapper instance.
     *
     * @param  resource  $rawProcess
     */
    public function __construct(mixed $rawProcess)
    {
        $this->rawProcess = $rawProcess;
    }

    /**
     * Check if the process is running.
     */
    public function isRunning(): bool
    {
        if (! is_resource($this->rawProcess)) {
            return false;
        }

        $status = proc_get_status($this->rawProcess);

        return $status['running'] ?? false;
    }

    /**
     * Stop the process.
     */
    public function stop(float $timeout = 10, ?int $signal = null): ?int
    {
        if (! is_resource($this->rawProcess)) {
            return null;
        }

        proc_terminate($this->rawProcess, $signal ?? 15);

        $waited = 0;
        $status = proc_get_status($this->rawProcess);

        while ($status['running'] && $waited < $timeout * 10) {
            usleep(100000); // 100ms
            $status = proc_get_status($this->rawProcess);
            $waited++;
        }

        if ($status['running']) {
            proc_terminate($this->rawProcess, 9); // SIGKILL
        }

        $exitCode = proc_close($this->rawProcess);
        $this->rawProcess = null;

        return $exitCode;
    }
}
