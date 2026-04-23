<?php

declare(strict_types=1);

namespace Revolution\Copilot\Process;

use Revolution\Copilot\Events\Process\ProcessStarted;
use Revolution\Copilot\Transport\StdioTransport;
use Revolution\Copilot\Types\TelemetryConfig;
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
        protected ?string $githubToken = null,
        protected ?bool $useLoggedInUser = null,
        protected TelemetryConfig|array|null $telemetry = null,
        protected int $sessionIdleTimeoutSeconds = 0,
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
     * Get the transport for reading and writing to the server.
     */
    public function getStdioTransport(): StdioTransport
    {
        return app(StdioTransport::class, [
            'stdin' => $this->getStdin(),
            'stdout' => $this->getStdout(),
        ]);
    }

    /**
     * Start the CLI process using proc_open for direct pipe access.
     *
     * @throws RuntimeException
     */
    protected function startProcess(): void
    {
        // Resolve CLI path: explicit > COPILOT_CLI_PATH env var > ExecutableFinder
        if (empty($this->cliPath)) {
            $this->cliPath = ($this->env ?? [])['COPILOT_CLI_PATH'] ?? null;
        }

        if (empty($this->cliPath)) {
            $this->cliPath = (new ExecutableFinder)->find(name: 'copilot');
        }

        if (empty($this->cliPath)) {
            throw new RuntimeException(
                'Path to Copilot CLI is required. Please provide it via the cli_path option, or use cli_url to rely on a remote CLI.',
            );
        }

        // Handle 'gh copilot' case
        if ($this->cliPath === 'gh copilot') {
            $this->cliPath = 'gh';
            array_unshift($this->cliArgs, 'copilot');
        }

        $descriptorSpec = [
            0 => ['pipe', 'r'],  // stdin
            1 => ['pipe', 'w'],  // stdout
            2 => ['pipe', 'w'],  // stderr
        ];

        $env = array_merge(getenv(), $this->env ?? []);
        unset($env['NODE_DEBUG']);

        // Apply OpenTelemetry environment variables if telemetry is configured
        if ($this->telemetry !== null) {
            $telemetry = $this->telemetry instanceof TelemetryConfig
                ? $this->telemetry
                : TelemetryConfig::fromArray($this->telemetry);
            $env = array_merge($env, $telemetry->toEnv());
        }

        // Set auth token in environment if provided
        if (filled($this->githubToken)) {
            $env['COPILOT_SDK_AUTH_TOKEN'] = $this->githubToken;
        }

        $args = ['--headless', '--stdio', '--log-level', $this->logLevel];

        // Add auth-related flags
        if (filled($this->githubToken)) {
            $args[] = '--auth-token-env';
            $args[] = 'COPILOT_SDK_AUTH_TOKEN';
        }

        // Default useLoggedInUser to false when githubToken is provided
        $useLoggedInUser = $this->useLoggedInUser ?? ($this->githubToken === null);
        if (! $useLoggedInUser) {
            $args[] = '--no-auto-login';
        }

        if ($this->sessionIdleTimeoutSeconds > 0) {
            $args[] = '--session-idle-timeout';
            $args[] = (string) $this->sessionIdleTimeoutSeconds;
        }

        $command = array_merge(
            [$this->cliPath],
            $this->cliArgs,
            $args,
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

        ProcessStarted::dispatch($this);
    }
}
