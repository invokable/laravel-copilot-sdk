---
name: laravel-copilot-sdk-development
description: Develops features using the Laravel Copilot SDK (revolution/laravel-copilot-sdk) to interact with GitHub Copilot CLI programmatically. Use when building, editing, debugging, or testing any Copilot CLI integration in Laravel — including sending prompts, managing sessions, defining custom tools, streaming responses, testing with fakes, using RPC methods, configuring MCP servers, or working with TCP/stdio transport modes.
---

# Laravel Copilot SDK (`revolution/laravel-copilot-sdk`)

Laravel package for the [GitHub Copilot CLI SDK](https://github.com/github/copilot-sdk). Interact with GitHub Copilot CLI programmatically from Laravel applications.

- Namespace: `Revolution\Copilot`
- Facade: `Revolution\Copilot\Facades\Copilot`

## Architecture

The SDK communicates with `copilot --headless` (the CLI running as a server) via JSON-RPC 2.0 over stdio or TCP.

```
Your Application → SDK (Copilot Facade) → JSON-RPC → Copilot CLI (server mode)
```

## Installation

```bash
composer require revolution/laravel-copilot-sdk
```

Optional `.env` configuration:
```dotenv
COPILOT_CLI_PATH=copilot
COPILOT_MODEL=claude-sonnet-4.6
COPILOT_TIMEOUT=60
COPILOT_URL=127.0.0.1:12345       # Set this to enable TCP mode
COPILOT_PERMISSION_APPROVE=true    # Auto-approve permission requests (default)
```

---

## Basic Usage

### Run a single prompt

```php
use Revolution\Copilot\Facades\Copilot;

$response = Copilot::run(prompt: 'Tell me something about Laravel.');
$response->content(); // string
```

### Multiple prompts in one session

```php
use Revolution\Copilot\Contracts\CopilotSession;
use Revolution\Copilot\Facades\Copilot;

$content = Copilot::start(function (CopilotSession $session) {
    $response = $session->sendAndWait(prompt: 'Tell me something about PHP.');
    dump($response->content());

    // Follow-up in the same session — context is maintained
    $response = $session->sendAndWait(prompt: 'Now tell me about Laravel.');
    return $response->content();
});
```

### `copilot()` helper function

```php
use Revolution\Copilot\Contracts\CopilotSession;
use function Revolution\Copilot\copilot;

// Same as Copilot::run()
$response = copilot('Tell me something about Laravel.');

// Same as Copilot::start()
copilot(function (CopilotSession $session) {
    $session->sendAndWait(prompt: 'Hello');
});

// Same as Facade
copilot()->client()->ping();
```

---

## SessionConfig

Pass configuration via `SessionConfig` class or array to `Copilot::run()` or `Copilot::start()`.

```php
use Revolution\Copilot\Types\SessionConfig;
use Revolution\Copilot\Types\SystemMessageConfig;
use Revolution\Copilot\Types\InfiniteSessionConfig;
use Revolution\Copilot\Enums\ReasoningEffort;

$config = new SessionConfig(
    model: 'claude-opus-4.6',
    reasoningEffort: ReasoningEffort::HIGH,
    systemMessage: new SystemMessageConfig(
        content: 'You are a helpful assistant for Laravel developers.',
    ),
    tools: [...],
    streaming: true,
    availableTools: ['read_file', 'write_file'],
    excludedTools: ['shell'],
    mcpServers: [...],
    customAgents: [...],
    skillDirectories: [],
    disabledSkills: [],
    infiniteSessions: new InfiniteSessionConfig(enabled: true),
);

$response = Copilot::run('Hello', config: $config);
```

For simple cases, use an array:
```php
Copilot::run('Hello', config: ['model' => 'gpt-5.2']);
```

---

## Event Handling with `on()`

`sendAndWait()` returns only the final assistant message. Use `on()` to receive intermediate events.

```php
use Revolution\Copilot\Enums\SessionEventType;
use Revolution\Copilot\Types\SessionEvent;

Copilot::start(function (CopilotSession $session) {
    // Subscribe to all events
    $session->on(function (SessionEvent $event): void {
        if ($event->isAssistantMessage()) {
            dump($event->content());
        } elseif ($event->failed()) {
            dump($event->errorMessage());
        }
    });

    // Or subscribe to a specific event type
    $session->on(SessionEventType::ASSISTANT_MESSAGE, function (SessionEvent $event): void {
        dump($event->content());
    });

    $response = $session->sendAndWait(prompt: 'Hello');
});
```

---

## Streaming

Enable streaming with `streaming: true` in SessionConfig to receive `ASSISTANT_MESSAGE_DELTA` events.

```php
use Revolution\Copilot\Types\SessionConfig;

$config = new SessionConfig(streaming: true);

Copilot::start(function (CopilotSession $session) {
    $session->on(function (SessionEvent $event): void {
        if ($event->isAssistantMessageDelta()) {
            echo $event->deltaContent(); // Incremental text chunks
        } elseif ($event->isAssistantMessage()) {
            // Full message after all deltas
        } elseif ($event->isAssistantReasoningDelta()) {
            echo $event->deltaContent(); // Reasoning chunks
        }
    });

    $session->sendAndWait(prompt: 'Tell me about Laravel.');
}, config: $config);
```

### Streaming with SSE (Server-Sent Events)

```php
Route::get('/copilot/sse', function () {
    return response()->eventStream(function () {
        yield from Copilot::stream(function (CopilotSession $session) {
            foreach ($session->sendAndStream('Tell me about Laravel.') as $event) {
                if ($event->isAssistantMessageDelta()) {
                    yield $event->deltaContent();
                }
            }
        }, config: new SessionConfig(streaming: true));
    });
});
```

### Streaming with Livewire `wire:stream`

```php
$session->on(SessionEventType::ASSISTANT_MESSAGE_DELTA, function (SessionEvent $event): void {
    $this->stream(content: $event->deltaContent(), to: 'answer');
});
```

---

## Custom Tools

Define tools that Copilot can call during a conversation.

```php
use Illuminate\JsonSchema\JsonSchema;
use Revolution\Copilot\Types\SessionConfig;
use Revolution\Copilot\Types\Tool;
use Revolution\Copilot\Types\ToolResultObject;

$parameters = JsonSchema::object([
    'topic' => JsonSchema::string()
        ->description('Topic to look up')
        ->required(),
])->toArray();

$config = new SessionConfig(
    tools: [
        Tool::define(
            name: 'lookup_fact',
            description: 'Returns a fun fact about a given topic.',
            parameters: $parameters,
            handler: function (array $params): ToolResultObject {
                $topic = $params['topic'] ?? '';

                return new ToolResultObject(
                    textResultForLlm: "Fact about {$topic}",
                    resultType: 'success',
                    sessionLog: "lookup_fact: {$topic}",
                    toolTelemetry: [],
                );
            },
        ),
    ],
);

Copilot::start(function (CopilotSession $session) {
    $response = $session->sendAndWait(
        prompt: 'Use lookup_fact to tell me about Laravel.'
    );
    dump($response->content());
}, config: $config);
```

---

## File Attachments

```php
use Revolution\Copilot\Support\Attachment;

$attachments = [
    Attachment::file(path: '/path/to/file.php', displayName: 'My File'),
    Attachment::directory(path: '/path/to/dir/', displayName: 'dir'),
];

$response = Copilot::run(prompt: 'Review this code', attachments: $attachments);
```

---

## MCP Servers

Configure MCP servers in SessionConfig:

```php
$config = new SessionConfig(
    mcpServers: [
        'my-mcp' => [
            'type' => 'local',       // 'local' is required for local MCP
            'command' => 'php',
            'args' => ['artisan', 'boost:mcp'],
            'tools' => ['*'],        // Required — won't be recognized without this
        ],
    ],
);
```

---

## Session Resume

### With fixed session ID

```php
$config = new SessionConfig(sessionId: 'user-123-conversation');

Copilot::start(function (CopilotSession $session) {
    $response = $session->sendAndWait(prompt: 'Continue our conversation.');
}, config: $config);
```

### Resume with `resume` parameter

```php
use Revolution\Copilot\Types\ResumeSessionConfig;

Copilot::start(function (CopilotSession $session) {
    $response = $session->sendAndWait(prompt: 'Hello again.');
}, config: new ResumeSessionConfig(), resume: 'user-123-conversation');
```

### List existing sessions

```php
$sessions = Copilot::client()->listSessions();
```

---

## RPC Methods

### ServerRpc (Client-scoped)

```php
Copilot::client()->rpc()->ping();
Copilot::client()->rpc()->models()->list();       // ModelsListResult
Copilot::client()->rpc()->tools()->list();
Copilot::client()->rpc()->account()->getQuota();
```

### SessionRpc (Session-scoped)

```php
use Revolution\Copilot\Types\Rpc\SessionModeSetParams;
use Revolution\Copilot\Types\Rpc\SessionModelSwitchToParams;
use Revolution\Copilot\Types\Rpc\SessionFleetStartParams;

Copilot::start(function (CopilotSession $session) {
    // Mode: plan, autopilot, etc.
    $session->rpc()->mode()->set(new SessionModeSetParams(mode: 'plan'));
    $session->sendAndWait(prompt: 'Create a plan for...');
    $plan = $session->rpc()->plan()->read();

    $session->rpc()->mode()->set(new SessionModeSetParams(mode: 'autopilot'));
    $session->sendAndWait(prompt: 'Execute the plan');

    // Model
    $session->rpc()->model()->getCurrent();
    $session->rpc()->model()->switchTo(new SessionModelSwitchToParams(modelId: 'gpt-5.2'));

    // Workspace
    $session->rpc()->workspace()->listFiles();

    // Fleet (sub-agents)
    $session->rpc()->fleet()->start(new SessionFleetStartParams(prompt: '...'));

    // Agent
    $session->rpc()->agent()->list();
    $session->rpc()->agent()->getCurrent();

    // Compaction
    $session->rpc()->compaction()->compact();
});
```

RPC params also accept arrays: `$session->rpc()->mode()->set(['mode' => 'plan']);`

---

## Permission Requests

By default (`config/copilot.php` → `permission_approve: true`), all permission requests are auto-approved.
In web-facing applications or when prompts can be influenced by end users, you should **not** rely on blanket auto-approval. Instead, inspect each request and gate high-risk operations behind your own authorization logic.

Custom handler example:
```php
use Revolution\Copilot\Support\PermissionRequestKind;

$config = new SessionConfig(
    onPermissionRequest: function (array $request, array $invocation) {
        // $request['kind']: "shell" | "write" | "mcp" | "read" | "url" | "custom-tool"
        switch ($request['kind'] ?? null) {
            case 'shell':
            case 'write':
                // High-risk operations: require explicit application-level authorization or deny by default.
                return PermissionRequestKind::deniedInteractivelyByUser();

            case 'read':
            case 'url':
            case 'mcp':
            case 'custom-tool':
            default:
                // Lower-risk operations: adjust this to your own policies (per-user confirmation, permissions, etc.).
                return PermissionRequestKind::approved();
        }
    },
);
```

---

## TCP Mode

Connect to an already-running Copilot CLI server instead of spawning a new process per request.

```bash
copilot --headless --port 12345
```

```dotenv
COPILOT_URL=127.0.0.1:12345
```

Runtime switching:
```php
$response = Copilot::useTcp(url: 'tcp://127.0.0.1:12345')->run(prompt: 'Hello');
$response = Copilot::useStdio()->run(prompt: 'Hello');
```

---

## GitHub Token Switching (stdio mode only)

Use per-user GitHub tokens at runtime:
```php
$config = array_merge(config('copilot'), [
    'github_token' => $user->github_token,
]);

$response = Copilot::useStdio($config)->run(prompt: '...');
Copilot::stop(); // Dispose client with user token
```

---

## Concurrency

```php
use Illuminate\Support\Facades\Concurrency;

[$gpt, $claude] = Concurrency::run([
    fn () => Copilot::run('Hello', config: ['model' => 'gpt-5.2'])->content(),
    fn () => Copilot::run('Hello', config: ['model' => 'claude-sonnet-4.6'])->content(),
]);
```

---

## Laravel AI SDK Integration (experimental)

```php
// config/ai.php
'providers' => [
    'copilot' => ['driver' => 'copilot', 'key' => ''],
],

// Usage
use function Laravel\Ai\agent;

$response = agent(instructions: 'You are an expert.')->prompt('Tell me about Laravel');
echo $response->text;
```

---

## Testing

### `Copilot::fake()`

```php
use Revolution\Copilot\Facades\Copilot;

// Simple fake — always returns '2'
Copilot::fake('2');
$response = Copilot::run(prompt: '1 + 1');
expect($response->content())->toBe('2');
```

### Sequence for multiple calls

```php
Copilot::fake([
    '*' => Copilot::sequence()
            ->push(Copilot::response('2'))
            ->push(Copilot::response('4')),
]);

Copilot::start(function (CopilotSession $session) use (&$r1, &$r2) {
    $r1 = $session->sendAndWait(prompt: '1 + 1');
    $r2 = $session->sendAndWait(prompt: '2 + 2');
});

expect($r1->content())->toBe('2');
expect($r2->content())->toBe('4');
```

### Assertions

```php
Copilot::assertPrompt('1 + *');      // Prompt was called (wildcard match)
Copilot::assertNotPrompt('1 + *');   // Prompt was NOT called
Copilot::assertPromptCount(3);       // Exactly 3 prompts sent
Copilot::assertNothingSent();        // No prompts sent
```

### Prevent stray requests

```php
Copilot::preventStrayRequests();              // Throw on any unfaked request
Copilot::preventStrayRequests(allow: ['ping']); // Allow only 'ping'
Copilot::preventStrayRequests(false);          // Disable prevention
```

---

## Laravel Events

The SDK dispatches Laravel events for logging and debugging:

- `Client\ClientStarted`, `Client\PingPong`, `Client\ToolCall`
- `Session\CreateSession`, `Session\MessageSend`, `Session\MessageSendAndWait`, `Session\ResumeSession`, `Session\SessionEventReceived`
- `JsonRpc\MessageSending`, `MessageReceived`, `ResponseReceived`
- `Process\ProcessStarted`

---

## Key Classes Reference

| Class / Interface | Purpose |
|---|---|
| `Copilot` (Facade) | Main entry point: `run()`, `start()`, `stream()`, `fake()` |
| `CopilotSession` (Contract) | Session interface: `send()`, `sendAndWait()`, `on()`, `rpc()`, `getMessages()`, `destroy()` |
| `CopilotClient` (Contract) | Client interface: `start()`, `stop()`, `createSession()`, `resumeSession()`, `ping()`, `listModels()`, `listSessions()`, `rpc()` |
| `SessionConfig` | Session creation options (model, tools, streaming, MCP, etc.) |
| `ResumeSessionConfig` | Options for resuming a session |
| `SessionEvent` | Event object: `content()`, `type()`, `deltaContent()`, `isAssistantMessage()`, `isAssistantMessageDelta()`, `failed()`, `errorMessage()` |
| `Tool` | `Tool::define(name, description, parameters, handler)` |
| `ToolResultObject` | Tool handler return type |
| `Attachment` | `Attachment::file()`, `Attachment::directory()`, `Attachment::selection()` |
| `ServerRpc` / `SessionRpc` | Typed RPC layer |
| `PermissionHandler` | `PermissionHandler::approveAll()` |
| `PermissionRequestKind` | `approved()`, `deniedInteractivelyByUser()`, `select()` |
