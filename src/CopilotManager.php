<?php

declare(strict_types=1);

namespace Revolution\Copilot;

use Illuminate\Support\Arr;
use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Macroable;
use Revolution\Copilot\Contracts\CopilotClient;
use Revolution\Copilot\Contracts\CopilotSession;
use Revolution\Copilot\Contracts\Factory;
use Revolution\Copilot\Support\PermissionHandler;
use Revolution\Copilot\Testing\WithFake;
use Revolution\Copilot\Types\ResumeSessionConfig;
use Revolution\Copilot\Types\SessionConfig;
use Revolution\Copilot\Types\SessionEvent;
use RuntimeException;

/**
 * Copilot Manager - Laravel-friendly wrapper for CopilotClient.
 */
class CopilotManager implements Factory
{
    use Conditionable;
    use Macroable;
    use WithFake;

    protected ?CopilotClient $client = null;

    /**
     * The process ID that created this instance.
     * Used to prevent cleanup in forked child processes.
     */
    protected int $ownerPid;

    public function __construct(
        protected array $config = [],
    ) {
        $this->ownerPid = getmypid();
    }

    /**
     * Run a single prompt and return the response.
     *
     * @param  string  $prompt  The prompt/message to send
     * @param  array<array{type: string, path: string, displayName?: string}>|null  $attachments  File or directory attachments. type: "file" | "directory"
     * @param  ?string  $mode  Message delivery mode. "enqueue": Add to queue (default), "immediate": Send immediately
     */
    public function run(string $prompt, ?array $attachments = null, ?string $mode = null, SessionConfig|array $config = []): ?SessionEvent
    {
        if ($this->isFake()) {
            return $this->fake->run($prompt, $attachments, $mode, $config);
        }

        return $this->start(
            fn (CopilotSession $session) => $session->sendAndWait(
                prompt: $prompt,
                attachments: $attachments,
                mode: $mode,
                timeout: $this->config['timeout'] ?? null,
            ),
            config: $config,
        );
    }

    /**
     * Start a session and execute a callback.
     *
     * @param  callable(CopilotSession): mixed  $callback
     * @param  ?string  $resume  Session ID to resume
     */
    public function start(callable $callback, SessionConfig|ResumeSessionConfig|array $config = [], ?string $resume = null): mixed
    {
        if ($this->isFake()) {
            return $this->fake->start($callback, $config);
        }

        $session = $this->prepareSession($config, $resume);

        try {
            return $callback($session);
        } finally {
            $session->destroy();
        }
    }

    /**
     * Start a session and stream events via a callback. Returns a generator.
     *
     * @param  callable(CopilotSession): iterable  $callback
     * @param  ?string  $resume  Session ID to resume
     * @return iterable<SessionEvent>
     */
    public function stream(callable $callback, SessionConfig|ResumeSessionConfig|array $config = [], ?string $resume = null): iterable
    {
        if ($this->isFake()) {
            yield from $this->fake->stream($callback, $config);

            return;
        }

        $session = $this->prepareSession($config, $resume);

        try {
            yield from $callback($session);
        } finally {
            $session->destroy();
        }
    }

    /**
     * Prepare a session for start/stream (create or resume).
     */
    protected function prepareSession(SessionConfig|ResumeSessionConfig|array $config = [], ?string $resume = null): CopilotSession
    {
        $client = $this->client();

        $config = is_array($config) ? $config : $config->toArray();

        $config = $this->ensurePermissionHandler($config);

        if (empty($resume)) {
            if (is_array($config)) {
                $config = SessionConfig::fromArray(array_merge(
                    ['model' => $this->config['model'] ?? null],
                    $config,
                ));
            }

            return $client->createSession($config);
        }

        if (is_array($config)) {
            $config = ResumeSessionConfig::fromArray(array_merge(
                ['model' => $this->config['model'] ?? null],
                $config,
            ));
        }

        return $client->resumeSession($resume, $config);
    }

    /**
     * Create a new session (caller is responsible for destroying it).
     */
    public function createSession(SessionConfig|array $config = []): CopilotSession
    {
        if ($this->isFake()) {
            return $this->fake->createSession($config);
        }

        $config = is_array($config) ? $config : $config->toArray();

        $config = $this->ensurePermissionHandler($config);

        if (is_array($config)) {
            $config = SessionConfig::fromArray(array_merge(
                ['model' => $this->config['model'] ?? null],
                $config,
            ));
        }

        return $this->client()->createSession($config);
    }

    /**
     * Ensure the config has an onPermissionRequest handler.
     *
     * If no handler is provided and the `permission_approve` config is true,
     * automatically injects PermissionHandler::approveAll().
     */
    protected function ensurePermissionHandler(array $config): array
    {
        if (! isset($config['onPermissionRequest'])) {
            if ($this->config['permission_approve'] ?? config('copilot.permission_approve', true)) {
                $config['onPermissionRequest'] = PermissionHandler::approveSafety();
            }
        }

        return $config;
    }

    /**
     * Get or create the CopilotClient instance.
     *
     * @param  ?array  $config  Override configuration options.
     */
    public function client(?array $config = null): CopilotClient
    {
        if ($this->client === null && $config === null) {
            if (filled(data_get($this->config, 'url'))) {
                // TCP mode: connect to existing server
                $options['cli_url'] = $this->config['url'];
            } else {
                // Stdio mode: start new process
                $options = [
                    'cli_path' => $this->config['cli_path'] ?? null,
                    'cli_args' => $this->config['cli_args'] ?? [],
                    'cwd' => $this->config['cwd'] ?? base_path(),
                    'log_level' => $this->config['log_level'] ?? 'info',
                    'env' => $this->config['env'] ?? null,
                    'github_token' => $this->config['github_token'] ?? null,
                    'use_logged_in_user' => $this->config['use_logged_in_user'] ?? null,
                ];
            }

            $this->client = app(Client::class, [
                'options' => $options,
            ]);

            $this->client->start();
        }

        /**
         * Reconfigure the client if a new config is provided.
         * This allows switching stdio <-> tcp modes or changing options at runtime.
         */
        if ($config !== null) {
            rescue(fn () => $this->client?->stop());

            $this->client = app(Client::class, [
                'options' => $config,
            ]);

            $this->client->start();
        }

        return $this->client;
    }

    /**
     * Configure the client to use stdio transport with given options.
     *
     * @param  ?array{cli_path: string, cli_args?: array, cwd?: string, log_level?: string, env?: array, github_token?: string, use_logged_in_user?: bool}  $config  Configuration options for stdio transport.
     */
    public function useStdio(?array $config = null): static
    {
        if ($config === null) {
            $config = config('copilot');
        }

        $this->client(Arr::only($config, [
            'cli_path',
            'cli_args',
            'cwd',
            'log_level',
            'env',
            'github_token',
            'use_logged_in_user',
        ]));

        return $this;
    }

    /**
     * Configure the client to use TCP transport with given URL.
     *
     * @param  ?string  $url  e.g., "tcp://127.0.0.1:12345"
     */
    public function useTcp(?string $url = null): static
    {
        if (empty($url)) {
            $url = config('copilot.url');
        }

        if (empty($url)) {
            throw new RuntimeException('No TCP URL provided for Copilot client.');
        }

        $this->client(['cli_url' => $url]);

        return $this;
    }

    /**
     * Stop the client and release resources.
     */
    public function stop(): void
    {
        if ($this->client !== null) {
            $this->client->stop();
            $this->client = null;
        }
    }

    /**
     * Destructor - ensure client is stopped.
     *
     * Only cleanup if we're in the same process that created this instance.
     * This prevents forked child processes (e.g., from pcntl_fork() in Laravel Prompts spin())
     * from destroying sessions that belong to the parent process.
     */
    public function __destruct()
    {
        if (getmypid() === $this->ownerPid) {
            $this->stop();
        }
    }
}
