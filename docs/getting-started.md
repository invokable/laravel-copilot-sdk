# Build Your First Copilot-Powered Laravel App

In this tutorial, you'll use the Laravel Copilot SDK to build a command-line assistant. You'll start with the basics, add event handling, then add custom tools - giving Copilot the ability to call your code.

**What you'll build:**

```
You: What's a fun fact about Laravel?
Copilot: Let me look that up for you...
         Laravel is a web application framework with expressive, elegant syntax.
         It's one of the most popular PHP frameworks!
```

## Prerequisites

Before you begin, make sure you have:

- **GitHub Copilot CLI** installed and authenticated ([Installation guide](https://docs.github.com/en/copilot/how-tos/set-up/install-copilot-cli))
- **PHP** 8.4+
- **Laravel** 12.x+

Verify the CLI is working:

```bash
copilot --version
```

## Step 1: Install the SDK

Install the package via Composer:

```bash
composer require revolution/laravel-copilot-sdk
```

### Optional Configuration

```bash
# Publish config file (optional)
php artisan vendor:publish --tag=copilot-config
```

Add to your `.env` if needed:

```dotenv
COPILOT_CLI_PATH=copilot
```

## Step 2: Send Your First Message

The simplest way to use the SDK is with `Copilot::run()` - about 3 lines of code.

Create an Artisan command `app/Console/Commands/CopilotDemo.php`:

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Revolution\Copilot\Facades\Copilot;

class CopilotDemo extends Command
{
    protected $signature = 'copilot:demo';
    protected $description = 'Demo Copilot SDK';

    public function handle()
    {
        $response = Copilot::run(prompt: 'What is 2 + 2?');
        
        $this->info($response->content());
    }
}
```

Run it:

```bash
php artisan copilot:demo
```

**You should see:**

```
4
```

Congratulations! You just built your first Copilot-powered Laravel app.

## Step 3: Multiple Messages in a Session

For conversations with multiple prompts, use `Copilot::start()` to maintain context across messages:

```php
use Revolution\Copilot\Contracts\CopilotSession;
use Revolution\Copilot\Facades\Copilot;

public function handle()
{
    Copilot::start(function (CopilotSession $session) {
        $this->info('Session ID: ' . $session->id());

        $response = $session->sendAndWait(prompt: 'What is 2 + 2?');
        $this->info('Answer: ' . $response->content());

        // Follow-up question in the same session
        $response = $session->sendAndWait(prompt: 'Now multiply that by 3');
        $this->info('Answer: ' . $response->content());
    });
}
```

The session maintains conversation history, so Copilot understands "that" refers to the previous answer.

## Step 4: Handle Events

`sendAndWait()` returns only the final assistant message. To receive all events during the conversation, use the `on()` method to register an event handler:

```php
use Revolution\Copilot\Contracts\CopilotSession;
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\Types\SessionEvent;

public function handle()
{
    Copilot::start(function (CopilotSession $session) {
        // Register event handler
        $session->on(function (SessionEvent $event): void {
            if ($event->isAssistantMessage()) {
                $this->info($event->content());
            } elseif ($event->failed()) {
                $this->error($event->errorMessage() ?? 'Unknown error');
            } else {
                $this->line('Event: ' . $event->type());
            }
        });

        // Events will be received by the handler above
        $response = $session->sendAndWait(prompt: 'Tell me a short joke');
        
        // The final response is also available from sendAndWait()
        // but the on() handler already displayed all messages
    });
}
```

This is useful for:
- Displaying intermediate messages
- Handling errors
- Debugging event flow

## Step 5: Add a Custom Tool

Now for the powerful part. Let's give Copilot the ability to call your code by defining a custom tool.

```php
use Illuminate\JsonSchema\JsonSchema;
use Revolution\Copilot\Contracts\CopilotSession;
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\Types\SessionConfig;
use Revolution\Copilot\Types\Tool;

public function handle()
{
    $facts = [
        'PHP' => 'A popular general-purpose scripting language that is especially suited to web development.',
        'Laravel' => 'A web application framework with expressive, elegant syntax.',
    ];

    // Define parameters using Laravel's JsonSchema
    $parameters = JsonSchema::object([
        'topic' => JsonSchema::string()
            ->description('Topic to look up (e.g., "PHP", "Laravel")')
            ->required(),
    ])->toArray();

    // Create session config with tools
    $config = new SessionConfig(
        tools: [
            Tool::define(
                name: 'lookup_fact',
                description: 'Returns a fun fact about a given topic.',
                parameters: $parameters,
                handler: function (array $params) use ($facts): array {
                    $topic = $params['topic'] ?? '';
                    $fact = $facts[$topic] ?? "Sorry, I don't have a fact about {$topic}.";

                    return [
                        'textResultForLlm' => $fact,
                        'resultType' => 'success',
                        'sessionLog' => "lookup_fact: served {$topic}",
                        'toolTelemetry' => [],
                    ];
                },
            ),
        ],
    );

    Copilot::start(function (CopilotSession $session) {
        $this->info('Session ID: ' . $session->id());

        $response = $session->sendAndWait(
            prompt: 'Use lookup_fact to tell me something about Laravel.'
        );

        $this->info($response->content());
    }, config: $config);
}
```

Run it and you'll see Copilot call your tool to get the fact, then respond with the results!

## Step 6: Build an Interactive Assistant

Let's put it all together into a useful interactive assistant using Laravel Prompts:

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\JsonSchema\JsonSchema;
use Revolution\Copilot\Contracts\CopilotSession;
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\Types\SessionConfig;
use Revolution\Copilot\Types\SessionEvent;
use Revolution\Copilot\Types\Tool;

use function Laravel\Prompts\info;
use function Laravel\Prompts\note;
use function Laravel\Prompts\spin;
use function Laravel\Prompts\text;
use function Laravel\Prompts\error;

class CopilotAssistant extends Command
{
    protected $signature = 'copilot:assistant';
    protected $description = 'Interactive Copilot assistant';

    public function handle()
    {
        $facts = [
            'PHP' => 'A popular general-purpose scripting language for web development.',
            'Laravel' => 'A web application framework with expressive, elegant syntax.',
            'Composer' => 'Dependency manager for PHP.',
        ];

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
                    handler: function (array $params) use ($facts): array {
                        $topic = $params['topic'] ?? '';
                        $fact = $facts[$topic] ?? "No fact available for {$topic}.";

                        return [
                            'textResultForLlm' => $fact,
                            'resultType' => 'success',
                            'sessionLog' => "lookup_fact: {$topic}",
                            'toolTelemetry' => [],
                        ];
                    },
                ),
            ],
        );

        Copilot::start(function (CopilotSession $session) {
            info('🤖 Copilot Assistant (Ctrl+C to exit)');
            info("   Session: {$session->id()}");
            info("   Try: 'Use lookup_fact to tell me about Laravel'\n");

            // Handle intermediate events
            $session->on(function (SessionEvent $event): void {
                if ($event->isAssistantMessage()) {
                    note($event->content());
                } elseif ($event->failed()) {
                    error($event->errorMessage() ?? 'Unknown error');
                }
            });

            while (true) {
                $prompt = text(
                    label: 'You',
                    placeholder: 'Ask me anything...',
                    required: true,
                    hint: 'Ctrl+C to exit',
                );

                spin(
                    callback: fn () => $session->sendAndWait($prompt),
                    message: 'Thinking...',
                );

                echo "\n";
            }
        }, config: $config);
    }
}
```

Run it:

```bash
php artisan copilot:assistant
```

**Example session:**

```
🤖 Copilot Assistant (Ctrl+C to exit)
   Session: abc-123-def
   Try: 'Use lookup_fact to tell me about Laravel'

You: What can you tell me about Laravel?
Thinking...
Laravel is a web application framework with expressive, elegant syntax.
It's a great choice for building modern PHP applications!

You: How about PHP?
Thinking...
PHP is a popular general-purpose scripting language for web development.
It powers a significant portion of the web!
```

---

## How Tools Work

When you define a tool, you're telling Copilot:
1. **What the tool does** (description)
2. **What parameters it needs** (JSON schema)
3. **What code to run** (handler)

Copilot decides when to call your tool based on the user's question. When it does:
1. Copilot sends a tool call request with the parameters
2. The SDK runs your handler function
3. The result is sent back to Copilot
4. Copilot incorporates the result into its response

---

## Session Configuration Options

The `SessionConfig` class supports many options:

```php
use Revolution\Copilot\Types\SessionConfig;
use Revolution\Copilot\Types\SystemMessageConfig;

$config = new SessionConfig(
    // Specify a model
    model: 'gpt-4.1',
    
    // Custom tools
    tools: [...],
    
    // System message configuration
    systemMessage: new SystemMessageConfig(
        content: 'You are a helpful assistant for Laravel developers.',
    ),
    
    // Enable streaming
    streaming: true,
    
    // Limit available built-in tools
    availableTools: ['read_file', 'write_file'],
    
    // Or exclude specific tools
    excludedTools: ['shell'],
    
    // MCP server configurations
    mcpServers: [
        'github' => [
            'type' => 'http',
            'url' => 'https://api.githubcopilot.com/mcp/',
        ],
    ],
    
    // Custom agents
    customAgents: [
        [
            'name' => 'reviewer',
            'displayName' => 'Code Reviewer',
            'description' => 'Reviews code for best practices',
            'prompt' => 'You are an expert code reviewer.',
        ],
    ],
);
```

---

## What's Next?

Now that you've got the basics, explore more features:

- **[Tools Documentation](./jp/tools.md)** - Advanced tool definitions
- **[Event Handling](./jp/send-on.md)** - Deep dive into `send()` and `on()`
- **[Permission Requests](./jp/permission-request.md)** - Handle permission callbacks
- **[Official SDK Documentation](https://github.com/github/copilot-sdk)** - Full reference

---

**You did it!** You've learned the core concepts of the Laravel Copilot SDK:
- ✅ Running single prompts with `Copilot::run()`
- ✅ Managing sessions with `Copilot::start()`
- ✅ Handling events with `on()`
- ✅ Defining custom tools that Copilot can call

Now go build something amazing! 🚀
