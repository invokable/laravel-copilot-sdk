<?php

declare(strict_types=1);

namespace Revolution\Copilot\Process;

use RuntimeException;
use Symfony\Component\Process\ExecutableFinder;

/**
 * Manages the Copilot CLI server process.
 */
class ProcessManager
{
    /**
     * The running process wrapper.
     */
    protected ?ProcessWrapper $process = null;

    /**
     * Process stdin stream (pipe).
     *
     * @var resource|null
     */
    protected mixed $stdin = null;

    /**
     * Process stdout stream (pipe).
     *
     * @var resource|null
     */
    protected mixed $stdout = null;

    /**
     * Create a new ProcessManager.
     */
    public function __construct(
        protected ?string $cliPath = null,
        protected array $cliArgs = [],
        protected ?string $cwd = null,
        protected string $logLevel = 'info',
        protected ?array $env = null,
    ) {
        $this->cwd ??= getcwd() ?: null;
    }

    /**
     * Start the Copilot CLI server in stdio mode.
     *
     * @throws RuntimeException
     */
    public function start(): void
    {
        if ($this->isRunning()) {
            return;
        }

        $this->startProcess();
    }

    /**
     * Stop the CLI server process.
     */
    public function stop(): void
    {
        // Close streams
        if (is_resource($this->stdin)) {
            fclose($this->stdin);
            $this->stdin = null;
        }

        if (is_resource($this->stdout)) {
            fclose($this->stdout);
            $this->stdout = null;
        }

        // Stop the process
        if ($this->process !== null) {
            $this->process->stop(5);
            $this->process = null;
        }
    }

    /**
     * Check if the CLI server is running.
     */
    public function isRunning(): bool
    {
        return $this->process?->isRunning() ?? false;
    }

    /**
     * Get the stdin stream for writing to the server.
     *
     * @return resource
     *
     * @throws RuntimeException
     */
    public function getStdin(): mixed
    {
        if (! is_resource($this->stdin)) {
            throw new RuntimeException('Process not started or stdin not available');
        }

        return $this->stdin;
    }

    /**
     * Get the stdout stream for reading from the server.
     *
     * @return resource
     *
     * @throws RuntimeException
     */
    public function getStdout(): mixed
    {
        if (! is_resource($this->stdout)) {
            throw new RuntimeException('Process not started or stdout not available');
        }

        return $this->stdout;
    }

    /**
     * Start the CLI process using proc_open for direct pipe access.
     *
     * @throws RuntimeException
     */
    protected function startProcess(): void
    {
        if (empty($this->cliPath)) {
            $this->cliPath = (new ExecutableFinder)->find(name: 'copilot', default: 'copilot');
        }

        $descriptorSpec = [
            0 => ['pipe', 'r'],  // stdin
            1 => ['pipe', 'w'],  // stdout
            2 => ['pipe', 'w'],  // stderr
        ];

        $env = $this->env ?? getenv();
        unset($env['NODE_DEBUG']);

        $command = array_merge(
            [$this->cliPath],
            $this->cliArgs,
            ['--server', '--stdio', '--log-level', $this->logLevel],
        );

        $rawProcess = proc_open(
            $command,
            $descriptorSpec,
            $pipes,
            $this->cwd,
            $env,
        );

        if (! is_resource($rawProcess)) {
            throw new RuntimeException('Failed to start CLI server');
        }

        $this->stdin = $pipes[0];
        $this->stdout = $pipes[1];

        // Set stdout to non-blocking mode
        stream_set_blocking($this->stdout, false);

        // Wait briefly for process to initialize
        usleep(100000); // 100ms

        // Check if process started successfully
        $status = proc_get_status($rawProcess);

        if (! $status['running']) {
            $stderr = stream_get_contents($pipes[2]);
            fclose($pipes[2]);
            fclose($pipes[0]);
            fclose($pipes[1]);
            proc_close($rawProcess);

            throw new RuntimeException("CLI server failed to start: {$stderr}");
        }

        // Close stderr pipe (we don't use it)
        fclose($pipes[2]);

        // Create a wrapper for status checking
        $this->process = new ProcessWrapper($rawProcess);
    }
}
