<?php

declare(strict_types=1);

namespace Revolution\Copilot\Process;

use Illuminate\Process\InvokedProcess;
use Illuminate\Support\Facades\Process;
use Revolution\Copilot\Types\ConnectionState;
use RuntimeException;
use Symfony\Component\Process\ExecutableFinder;

/**
 * Manages the Copilot CLI server process.
 */
class ProcessManager
{
    protected const int SDK_PROTOCOL_VERSION = 1;

    /**
     * The running CLI process.
     */
    protected ?InvokedProcess $process = null;

    /**
     * Process stdin stream.
     *
     * @var resource|null
     */
    protected mixed $stdin = null;

    /**
     * Process stdout stream.
     *
     * @var resource|null
     */
    protected mixed $stdout = null;

    /**
     * Raw process handle for pipe access.
     *
     * @var resource|null
     */
    protected mixed $rawProcess = null;

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
        $this->cwd ??= getcwd();
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
        if ($this->rawProcess !== null) {
            // Close pipes first
            if (is_resource($this->stdin)) {
                fclose($this->stdin);
                $this->stdin = null;
            }

            if (is_resource($this->stdout)) {
                fclose($this->stdout);
                $this->stdout = null;
            }

            // Terminate the process
            proc_terminate($this->rawProcess, 15); // SIGTERM

            // Wait briefly for graceful shutdown
            $status = proc_get_status($this->rawProcess);
            $waited = 0;

            while ($status['running'] && $waited < 50) {
                usleep(100000); // 100ms
                $status = proc_get_status($this->rawProcess);
                $waited++;
            }

            // Force kill if still running
            if ($status['running']) {
                proc_terminate($this->rawProcess, 9); // SIGKILL
            }

            proc_close($this->rawProcess);
            $this->rawProcess = null;
        }

        $this->process = null;
    }

    /**
     * Check if the CLI server is running.
     */
    public function isRunning(): bool
    {
        if ($this->rawProcess === null) {
            return false;
        }

        $status = proc_get_status($this->rawProcess);

        return $status['running'] ?? false;
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
     * Get the SDK protocol version.
     */
    public static function getProtocolVersion(): int
    {
        return self::SDK_PROTOCOL_VERSION;
    }

    /**
     * Start the CLI process using proc_open for pipe access.
     *
     * @throws RuntimeException
     */
    protected function startProcess(): void
    {
        if (empty($this->cliPath)) {
            $this->cliPath = new ExecutableFinder()->find(name: 'copilot', default: 'copilot');
            // info('Using copilot CLI path: '.$this->cliPath);
        }

        $commands = array_merge(
            [$this->cliPath],
            $this->cliArgs,
            ['--server', '--stdio', '--log-level', $this->logLevel],
        );

        $descriptorSpec = [
            0 => ['pipe', 'r'],  // stdin
            1 => ['pipe', 'w'],  // stdout
            2 => ['pipe', 'w'],  // stderr
        ];

        $env = $this->env ?? getenv();

        // Remove NODE_DEBUG to suppress debug output
        unset($env['NODE_DEBUG']);

        $this->rawProcess = proc_open(
            $commands,
            $descriptorSpec,
            $pipes,
            $this->cwd,
            $env,
        );

        if (! is_resource($this->rawProcess)) {
            throw new RuntimeException('Failed to start CLI server');
        }

        $this->stdin = $pipes[0];
        $this->stdout = $pipes[1];

        // Set stdout to non-blocking mode
        stream_set_blocking($this->stdout, false);

        // Wait briefly for process to initialize
        usleep(100000); // 100ms

        // Check if process started successfully
        $status = proc_get_status($this->rawProcess);

        if (! $status['running']) {
            // Read any error output
            $stderr = stream_get_contents($pipes[2]);
            fclose($pipes[2]);
            $this->stop();

            throw new RuntimeException("CLI server failed to start: {$stderr}");
        }

        // Close stderr pipe (we don't use it)
        fclose($pipes[2]);
    }
}
