<?php

declare(strict_types=1);

use Symfony\Component\Process\Process;

require_once dirname(__DIR__, 3).'/.github/scripts/check-sdk-sync.php';

function withSdkSyncFixture(Closure $test): void
{
    $root = sys_get_temp_dir().'/sdk-sync-audit-'.bin2hex(random_bytes(8));
    $write = function (string $path, string $source) use ($root): void {
        if (! is_dir(dirname($root.'/'.$path))) {
            mkdir(dirname($root.'/'.$path), 0700, true);
        }

        file_put_contents($root.'/'.$path, $source);
    };

    $write('copilot-sdk/nodejs/src/types.ts', <<<'TS'
        export interface SessionConfigBase {
            model?: string;
            settings?: {
                nestedProperty: string;
            };
            handler?: (
                input: {
                    nestedInput: string;
                }
            ) => void;
        }
        export interface SessionConfig extends SessionConfigBase {
            sessionId?: string;
        }
        export interface ResumeSessionConfig extends SessionConfigBase {
            continuePendingWork?: boolean;
        }
        export interface CopilotClientOptions {
            workingDirectory?: string;
            gitHubToken?: string;
            enableRemoteSessions?: boolean;
            logLevel?: string;
        }
        export interface SessionHooks {
            onPreToolUse?: () => void;
        }
        TS);
    $write('copilot-sdk/nodejs/src/generated/rpc.ts', <<<'TS'
        export interface ExistingRequest { changedProperty: string; }
        export interface MissingRequest {}
        export interface TsOnlyRequest {}
        export interface Extension {}
        export type JsonValue = unknown;
        export function createServerRpc(connection: MessageConnection) {
            return {
                ping: async () => connection.sendRequest("ping", {}),
                models: {
                    list: async () => connection.sendRequest("models.list", {}),
                    update: async () => connection.sendRequest("models.update", {}),
                },
            };
        }
        export function createSessionRpc(connection: MessageConnection, sessionId: string) {
            return {
                suspend: async () => connection.sendRequest("session.suspend", { sessionId }),
                model: {
                    set: async () => connection.sendRequest("session.model.set", { sessionId }),
                },
            };
        }
        export function createInternalServerRpc(connection: MessageConnection) {
            return { hidden: async () => connection.sendRequest("private.hidden", {}) };
        }
        TS);
    $write('copilot-sdk/python/copilot/generated/rpc.py', <<<'PY'
        class ExistingRequest:
            pass
        class MissingRequest:
            pass
        class PythonOnlyResult:
            pass
        class ServerModelsApi:
            async def list(self):
                return await self._client.request("models.list", {})
        class ServerRpc:
            def __init__(self, client):
                self.models = ServerModelsApi(client)
            async def ping(self):
                return await self._client.request("ping", {})
        class SessionRpc:
            async def suspend(self):
                return await self._client.request("session.suspend", {})
            async def set_model(self):
                return await self._client.request("session.model.set", {})
        class _InternalServerRpc:
            async def hidden(self):
                return await self._client.request("private.hidden", {})
        PY);
    $java = 'copilot-sdk/java/sdk/src/generated/java/com/github/copilot/generated/rpc';

    foreach (['ExistingParams', 'MissingParams', 'JavaOnly', 'Extension'] as $name) {
        $write("$java/$name.java", "public class $name {}");
    }

    $write('src/Types/Rpc/ExistingRequest.php', '<?php readonly class ExistingRequest {}');
    $write('src/Types/Rpc/ExtensionInfo.php', '<?php readonly class ExtensionInfo {}');
    $write('src/Rpc/ServerRpc.php', <<<'PHP'
        <?php
        class ServerRpc {
            public function ping() { return $this->client->request('ping', []); }
            public function models() { return new PendingServerModels($this->client); }
            // $this->client->request('models.update', []);
        }
        PHP);
    $write('src/Rpc/PendingServerModels.php', <<<'PHP'
        <?php
        class PendingServerModels {
            public function list() { return $this->client->request('models.list', []); }
        }
        PHP);
    $write('src/Rpc/SessionRpc.php', <<<'PHP'
        <?php
        class SessionRpc {
            public function suspend() { return $this->client->request('session.suspend', []); }
            public function model() { return new PendingModel($this->client); }
        }
        PHP);
    $write('src/Rpc/PendingModel.php', <<<'PHP'
        <?php
        class PendingModel {
            public function set() { return $this->client->request('session.model.set', []); }
        }
        PHP);
    $write('src/Types/SessionConfig.php', <<<'PHP'
        <?php
        readonly class SessionConfig {
            public function __construct(
                public ?string $model = null,
                public ?array $settings = null,
                public ?Closure $handler = null,
                public ?string $sessionId = null,
            ) {}
        }
        PHP);
    $write('src/Types/ResumeSessionConfig.php', <<<'PHP'
        <?php
        readonly class ResumeSessionConfig {
            public function __construct(
                public ?string $model = null,
                public ?array $settings = null,
                public ?Closure $handler = null,
                public ?bool $continuePendingWork = null,
            ) {}
        }
        PHP);
    $write('src/Types/SessionHooks.php', '<?php readonly class SessionHooks { public ?Closure $onPreToolUse; }');
    $write('src/Concerns/Session/HasHooks.php', <<<'PHP'
        <?php
        trait HasHooks {
            public function invoke() {
                return ['preToolUse' => $this->hooks->onPreToolUse];
            }
        }
        PHP);
    $write('src/Client.php', <<<'PHP'
        <?php
        class Client {
            public function __construct(array $options) {
                data_get($options, 'cwd');
                data_get($options, 'github_token');
                data_get($options, 'remote');
            }
        }
        PHP);
    $write('src/Concerns/Client/Options.php', <<<'PHP'
        <?php
        trait Options {
            public function logLevel() { return $this->options['log_level']; }
        }
        PHP);
    $write('src/CopilotManager.php', <<<'PHP'
        <?php
        class CopilotManager {
            public function client() {
                return ['cwd' => null, 'github_token' => null, 'remote' => false, 'log_level' => 'info'];
            }
            public function useStdio() {
                return Arr::only($config, ['cwd', 'github_token', 'remote', 'log_level']);
            }
        }
        PHP);

    try {
        $test(new SdkSyncAudit($root), $write, $root);
    } finally {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST,
        );

        foreach ($files as $file) {
            if ($file->isDir()) {
                rmdir($file->getPathname());
            } else {
                unlink($file->getPathname());
            }
        }

        rmdir($root);
    }
}

describe('SDK sync structural audit', function () {
    it('checks filenames across three languages without inspecting RPC properties', function () {
        withSdkSyncFixture(function (SdkSyncAudit $audit): void {
            $missing = $audit->report()['missing_rpc_types'];

            expect(array_column($missing, 'name'))->toBe(['JavaOnly', 'MissingRequest', 'PythonOnlyResult', 'TsOnlyRequest']);
            expect($missing[1]['sources'])->toHaveKeys(['java', 'javascript', 'python']);
            expect($missing[1]['expected_file'])->toBe('src/Types/Rpc/MissingRequest.php');
        });
    });

    it('includes inherited configuration properties but ignores nested fields and callback arguments', function () {
        withSdkSyncFixture(function (SdkSyncAudit $audit, Closure $write): void {
            $write('src/Types/ResumeSessionConfig.php', '<?php class ResumeSessionConfig { public ?string $model; public ?array $settings; public ?Closure $handler; public ?bool $legacy; }');

            $report = $audit->report();

            expect($report['session_config']['missing'])->toBe([]);
            expect($report['session_config']['local_only'])->toBe([]);
            expect($report['resume_session_config']['missing'])->toBe(['continuePendingWork']);
            expect($report['resume_session_config']['local_only'])->toBe(['legacy']);
        });
    });

    it('checks reachable nested RPC methods and retains language disagreement for verification', function () {
        withSdkSyncFixture(function (SdkSyncAudit $audit, Closure $write): void {
            $write('src/Rpc/PendingModel.php', '<?php class PendingModel { public function old() { return $this->client->request("session.model.old", []); } }');

            $methods = $audit->report()['rpc_methods'];

            expect($methods['ServerRpc']['missing'])->toBe(['models.update']);
            expect($methods['ServerRpc']['language_differences']['missing'])->toBe(['models.update']);
            expect($methods['ServerRpc']['local_only'])->toBe([]);
            expect($methods['SessionRpc']['missing'])->toBe(['session.model.set']);
            expect($methods['SessionRpc']['local_only'])->toBe(['session.model.old']);
        });
    });

    it('checks hook declarations and valid dispatch keys independently', function () {
        withSdkSyncFixture(function (SdkSyncAudit $audit, Closure $write): void {
            $write('src/Concerns/Session/HasHooks.php', '<?php trait HasHooks { public function invoke() { return ["wrongName" => $this->hooks->onPreToolUse]; } }');

            $hooks = $audit->report()['hooks'];

            expect($hooks['SessionHooks']['missing'])->toBe([]);
            expect($hooks['HasHooks']['missing'])->toBe(['onPreToolUse']);
        });
    });

    it('checks both hook surfaces when upstream adds a handler', function () {
        withSdkSyncFixture(function (SdkSyncAudit $audit, Closure $write, string $root): void {
            $path = 'copilot-sdk/nodejs/src/types.ts';
            $write($path, str_replace('onPreToolUse?:', "onNewHook?: () => void;\n    onPreToolUse?:", file_get_contents($root.'/'.$path)));

            $hooks = $audit->report()['hooks'];

            expect($hooks['SessionHooks']['missing'])->toBe(['onNewHook']);
            expect($hooks['HasHooks']['missing'])->toBe(['onNewHook']);
        });
    });

    it('recognizes Laravel aliases and reports incomplete manager forwarding independently', function () {
        withSdkSyncFixture(function (SdkSyncAudit $audit, Closure $write): void {
            expect($audit->report()['client_options']['missing'])->toBe([]);
            expect($audit->report()['client_options']['local_only'])->toBe([
                'Client' => [], 'CopilotManager::client' => [], 'CopilotManager::useStdio' => [],
            ]);

            $write('src/CopilotManager.php', <<<'PHP'
                <?php
                class CopilotManager {
                    public function client() {
                        return ['cwd' => null, 'github_token' => null, 'remote' => false];
                    }
                    public function useStdio() {
                        return Arr::only($config, ['cwd', 'github_token', 'remote', 'log_level']);
                    }
                }
                PHP);

            expect($audit->report()['client_options']['missing'])->toBe([
                ['name' => 'logLevel', 'accepted_names' => ['logLevel', 'log_level'], 'missing_in' => ['CopilotManager::client']],
            ]);
        });
    });

    it('detects new client options without counting examples in comments as support', function () {
        withSdkSyncFixture(function (SdkSyncAudit $audit, Closure $write, string $root): void {
            $path = 'copilot-sdk/nodejs/src/types.ts';
            $write($path, str_replace('workingDirectory?:', "newOption?: string;\n    workingDirectory?:", file_get_contents($root.'/'.$path)));
            $write('src/Client.php', file_get_contents($root.'/src/Client.php')."\n// data_get(\$options, 'new_option');");

            expect($audit->report()['client_options']['missing'])->toBe([
                [
                    'name' => 'newOption',
                    'accepted_names' => ['newOption', 'new_option'],
                    'missing_in' => ['Client', 'CopilotManager::client', 'CopilotManager::useStdio'],
                ],
            ]);
        });
    });

    it('detects options filtered out by useStdio even when Client and the default manager path support them', function () {
        withSdkSyncFixture(function (SdkSyncAudit $audit, Closure $write, string $root): void {
            $path = 'src/CopilotManager.php';
            $write($path, str_replace("'remote', 'log_level'", "'remote'", file_get_contents($root.'/'.$path)));

            expect($audit->report()['client_options']['missing'])->toBe([
                ['name' => 'logLevel', 'accepted_names' => ['logLevel', 'log_level'], 'missing_in' => ['CopilotManager::useStdio']],
            ]);
        });
    });

    it('fails explicitly when a required source is missing', function () {
        withSdkSyncFixture(function (SdkSyncAudit $audit, Closure $write, string $root): void {
            unlink($root.'/copilot-sdk/python/copilot/generated/rpc.py');

            expect(fn () => $audit->report())->toThrow(RuntimeException::class, 'Required source is unreadable');
        });
    });

    it('fails explicitly when required declarations cannot be parsed', function () {
        withSdkSyncFixture(function (SdkSyncAudit $audit, Closure $write): void {
            $write('copilot-sdk/nodejs/src/types.ts', 'export interface RenamedConfig {}');

            expect(fn () => $audit->report())->toThrow(RuntimeException::class, 'Required declaration not found');
        });
    });

    it('emits JSON on success and a nonzero exit with no report on failure', function () {
        withSdkSyncFixture(function (SdkSyncAudit $audit, Closure $write, string $root): void {
            $script = dirname(__DIR__, 3).'/.github/scripts/check-sdk-sync.php';
            $success = new Process([PHP_BINARY, $script, $root]);
            $success->run();

            expect($success->getExitCode())->toBe(0);
            expect(json_decode($success->getOutput(), true, flags: JSON_THROW_ON_ERROR)['schema_version'])->toBe(1);
            expect($success->getErrorOutput())->toBe('');

            $write('copilot-sdk/python/copilot/generated/rpc.py', '');
            $failure = new Process([PHP_BINARY, $script, $root]);
            $failure->run();

            expect($failure->getExitCode())->toBe(1);
            expect($failure->getOutput())->toBe('');
            expect($failure->getErrorOutput())->toContain('SDK sync audit failed: Required source is empty or unreadable');
        });
    });
});
