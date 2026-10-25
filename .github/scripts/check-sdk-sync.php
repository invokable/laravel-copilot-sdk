<?php

declare(strict_types=1);

// Known naming differences / representations that do not need a new RPC file.
const SDK_SYNC_RPC_TYPE_EXCLUSIONS = [
    'Extension' => 'src/Types/Rpc/ExtensionInfo.php',
    'JsonValue' => 'PHP mixed / arrays',
    'OpaqueInProcessValue' => 'In-process values are not transferable over JSON-RPC',
];

const SDK_SYNC_RPC_METHOD_EXCLUSIONS = [
    'agentRegistry.spawn' => 'Experimental controller-local API with no identified Laravel use case (discussion #455)',
];

const SDK_SYNC_CLIENT_OPTION_ALIASES = [
    'workingDirectory' => ['cwd'],
    'gitHubToken' => ['github_token'],
    'enableRemoteSessions' => ['remote'],
    'baseDirectory' => ['baseDirectory', 'base_directory', 'copilot_home'],
];

const SDK_SYNC_CLIENT_OPTION_EXCLUSIONS = [
    'clientInfo' => 'Forwarded in the client handshake payload',
    'onGetTraceContext' => 'Provided by the TraceContext provider',
    'onListModels' => 'Provided by Client::listModelsUsing()',
];

/**
 * Structural candidates only: the workflow must verify semantics before filing tasks.
 * Runs without Composer, Laravel bootstrapping, or loading the inspected PHP classes.
 */
final class SdkSyncAudit
{
    public function __construct(private string $root) {}

    public function report(): array
    {
        $typescript = $this->read('copilot-sdk/nodejs/src/generated/rpc.ts');
        $python = $this->read('copilot-sdk/python/copilot/generated/rpc.py');
        $types = $this->read('copilot-sdk/nodejs/src/types.ts');

        return [
            'schema_version' => 2,
            'scope' => 'Current upstream snapshot vs Laravel; names and file existence only, not RPC type contents or signatures.',
            'rpc_type_exclusions' => SDK_SYNC_RPC_TYPE_EXCLUSIONS,
            'rpc_method_exclusions' => SDK_SYNC_RPC_METHOD_EXCLUSIONS,
            'client_option_exclusions' => SDK_SYNC_CLIENT_OPTION_EXCLUSIONS,
            'missing_rpc_types' => $this->rpcTypes($typescript, $python, $this->enumTypes($typescript, $python)),
            'missing_rpc_apis' => $this->missingRpcApis(),
            'missing_enums' => $this->missingEnums($this->enumTypes($typescript, $python)),
            'rpc_methods' => $this->rpcMethods($typescript, $python),
            'session_config' => $this->config($types, 'SessionConfig'),
            'resume_session_config' => $this->config($types, 'ResumeSessionConfig'),
            'client_options' => $this->clientOptions($types),
            'hooks' => $this->hooks($types),
        ];
    }

    private function read(string $path): string
    {
        if (! is_readable($this->root.'/'.$path)) {
            throw new RuntimeException("Required source is unreadable: $path");
        }

        $content = file_get_contents($this->root.'/'.$path);

        if ($content === false || trim($content) === '') {
            throw new RuntimeException("Required source is empty or unreadable: $path");
        }

        return $content;
    }

    private function files(string $path, string $extension): array
    {
        $files = glob($this->root.'/'.$path.'/*.'.$extension);

        if ($files === false || $files === []) {
            throw new RuntimeException("No .$extension sources found in $path");
        }

        sort($files);

        return array_map(fn (string $file): string => substr($file, strlen($this->root) + 1), $files);
    }

    private function phpCode(string $path): string
    {
        return implode('', array_map(
            fn (array|string $token): string => is_array($token)
                ? (in_array($token[0], [T_COMMENT, T_DOC_COMMENT], true) ? '' : $token[1])
                : $token,
            token_get_all($this->read($path)),
        ));
    }

    private function withoutComments(string $source): string
    {
        return preg_replace_callback(
            '~("(?:\\\\.|[^"\\\\])*"|\'(?:\\\\.|[^\'\\\\])*\'|`(?:\\\\.|[^`\\\\])*`)|/\*.*?\*/|//[^\n]*~s',
            fn (array $match): string => isset($match[1]) && $match[1] !== ''
                ? $match[1]
                : str_repeat("\n", substr_count($match[0], "\n")),
            $source,
        );
    }

    private function block(string $source, string $pattern): string
    {
        if (! preg_match($pattern, $source, $match, PREG_OFFSET_CAPTURE)) {
            throw new RuntimeException("Required declaration not found: $pattern");
        }

        $start = strpos($source, '{', $match[0][1] + strlen($match[0][0]) - 1);
        $depth = 0;
        $quote = null;

        for ($index = $start; $index < strlen($source); $index++) {
            $char = $source[$index];

            if ($quote !== null) {
                if ($char === '\\') {
                    $index++;
                } elseif ($char === $quote) {
                    $quote = null;
                }

                continue;
            }

            if (in_array($char, ['"', "'", '`'], true)) {
                $quote = $char;
            } elseif ($char === '{') {
                $depth++;
            } elseif ($char === '}' && --$depth === 0) {
                return substr($source, $start + 1, $index - $start - 1);
            }
        }

        throw new RuntimeException("Unterminated declaration: $pattern");
    }

    private function names(string $source, string $pattern): array
    {
        preg_match_all($pattern, $source, $matches);
        $names = array_values(array_unique($matches[1]));
        sort($names);

        return $names;
    }

    private function difference(array $upstream, array $local): array
    {
        return [
            'missing' => array_values(array_diff($upstream, $local)),
            'local_only' => array_values(array_diff($local, $upstream)),
        ];
    }

    private function enumKey(string $name): string
    {
        return strtolower(ltrim($name, '_'));
    }

    private function interfaceProperties(string $source, string $name, array $ancestors = []): array
    {
        if (in_array($name, $ancestors, true)) {
            throw new RuntimeException("Cyclic interface inheritance: $name");
        }

        $source = $this->withoutComments($source);
        $pattern = '/\binterface\s+'.preg_quote($name, '/').'\b\s*(?:extends\s+([\w,\s]+))?\s*\{/';
        $body = $this->block($source, $pattern);
        preg_match($pattern, $source, $declaration);
        $properties = [];
        $depth = 0;

        foreach (explode("\n", $body) as $line) {
            if ($depth === 0 && preg_match('/^\s*(?:readonly\s+)?(\w+)\??\s*:/', $line, $match)) {
                $properties[] = $match[1];
            }

            $structure = preg_replace('/"(?:\\\\.|[^"\\\\])*"|\'(?:\\\\.|[^\'\\\\])*\'/', '', $line);
            $depth += substr_count($structure, '{') + substr_count($structure, '(') + substr_count($structure, '[')
                - substr_count($structure, '}') - substr_count($structure, ')') - substr_count($structure, ']');
        }

        foreach (preg_split('/[\s,]+/', trim($declaration[1] ?? ''), flags: PREG_SPLIT_NO_EMPTY) as $parent) {
            $properties = array_merge($properties, $this->interfaceProperties($source, $parent, [...$ancestors, $name]));
        }

        if ($properties === []) {
            throw new RuntimeException("No properties found in $name");
        }

        $properties = array_values(array_unique($properties));
        sort($properties);

        return $properties;
    }

    private function phpProperties(string $path): array
    {
        return $this->names($this->phpCode($path), '/\bpublic\s+(?:readonly\s+)?[\w\\\\|?]+\s+\$(\w+)/');
    }

    private function config(string $types, string $name): array
    {
        $path = "src/Types/$name.php";

        return [
            'upstream' => 'copilot-sdk/nodejs/src/types.ts#'.$name,
            'local' => $path,
            ...$this->difference($this->interfaceProperties($types, $name), $this->phpProperties($path)),
        ];
    }

    private function enumTypes(string $typescript, string $python): array
    {
        $javaDirectory = 'copilot-sdk/java/sdk/src/generated/java/com/github/copilot/generated/rpc';
        $enums = [];
        $typescript = $this->withoutComments($typescript);

        foreach ($this->names($typescript, '/^export\s+enum\s+(\w+)/m') as $name) {
            $key = $this->enumKey($name);
            $enums[$key]['name'] = $name;
            $enums[$key]['sources']['javascript'] = 'copilot-sdk/nodejs/src/generated/rpc.ts#'.$name;
        }

        preg_match_all('/^export\s+type\s+(\w+)\s*=\s*(.*?);/ms', $typescript, $aliases, PREG_SET_ORDER);

        foreach ($aliases as $alias) {
            if (preg_match('/^(?:\s*\|\s*)?"(?:\\\\.|[^"\\\\])*"(?:\s*\|\s*"(?:\\\\.|[^"\\\\])*")*$/s', trim($alias[2]))) {
                $name = $alias[1];
                $key = $this->enumKey($name);
                $enums[$key]['name'] = $name;
                $enums[$key]['sources']['javascript'] = 'copilot-sdk/nodejs/src/generated/rpc.ts#'.$name;
            }
        }

        foreach ($this->files($javaDirectory, 'java') as $file) {
            $name = basename($file, '.java');

            if (preg_match('/\benum\s+'.preg_quote($name, '/').'\b/', $this->read($file))) {
                $key = $this->enumKey($name);
                $enums[$key]['name'] ??= $name;
                $enums[$key]['sources']['java'] = $file;
            }
        }

        preg_match_all('/^class\s+(\w+)\s*\(\s*(?:(?:str)\s*,\s*)?Enum\s*\)\s*:/m', $python, $pythonEnums);

        foreach ($pythonEnums[1] as $name) {
            $key = $this->enumKey($name);
            $enums[$key]['name'] ??= $name;
            $enums[$key]['sources']['python'] = 'copilot-sdk/python/copilot/generated/rpc.py#'.$name;
        }

        foreach (['javascript', 'java'] as $language) {
            if (array_filter($enums, fn (array $enum): bool => isset($enum['sources'][$language])) === []) {
                throw new RuntimeException("No $language RPC enum declarations found");
            }
        }

        ksort($enums);

        return $enums;
    }

    private function rpcTypes(string $typescript, string $python, array $enums): array
    {
        $javaDirectory = 'copilot-sdk/java/sdk/src/generated/java/com/github/copilot/generated/rpc';
        $declarations = [
            'javascript' => $this->names($this->withoutComments($typescript), '/^export\s+(?:interface|type)\s+(\w+)/m'),
            'python' => array_values(array_filter(
                $this->names($python, '/^class\s+(\w+)/m'),
                fn (string $name): bool => ! str_starts_with($name, '_') && ! str_ends_with($name, 'Api') && ! str_ends_with($name, 'Rpc'),
            )),
        ];

        if ($declarations['javascript'] === [] || $declarations['python'] === []) {
            throw new RuntimeException('No JS/Python RPC type declarations found');
        }

        $knownNames = array_merge(...array_values($declarations));
        $sources = [];

        foreach ($this->files($javaDirectory, 'java') as $file) {
            $name = basename($file, '.java');

            if ($this->isRpcApi($name)) {
                continue;
            }

            $requestName = preg_replace('/Params$/', 'Request', $name);
            $canonical = in_array($requestName, $knownNames, true) ? $requestName : $name;
            $sources[$this->enumKey($canonical)]['name'] = $canonical;
            $sources[$this->enumKey($canonical)]['sources']['java'] = $file;
        }

        foreach ($declarations as $language => $names) {
            foreach ($names as $name) {
                $key = $this->enumKey($name);
                $sources[$key]['name'] ??= $name;
                $sources[$key]['sources'][$language] = $language === 'javascript'
                    ? 'copilot-sdk/nodejs/src/generated/rpc.ts#'.$name
                    : 'copilot-sdk/python/copilot/generated/rpc.py#'.$name;
            }
        }

        $local = array_map(fn (string $file): string => strtolower(basename($file, '.php')), $this->files('src/Types/Rpc', 'php'));
        $excluded = array_map('strtolower', array_keys(SDK_SYNC_RPC_TYPE_EXCLUSIONS));
        ksort($sources);
        $missing = [];

        foreach ($sources as $key => $type) {
            if (! in_array($key, $local, true) && ! in_array($key, $excluded, true) && ! isset($enums[$this->enumKey($type['name'])])) {
                $missing[] = [
                    ...$type,
                    'expected_file' => 'src/Types/Rpc/'.$type['name'].'.php',
                ];
            }
        }

        return $missing;
    }

    private function missingRpcApis(): array
    {
        $javaDirectory = 'copilot-sdk/java/sdk/src/generated/java/com/github/copilot/generated/rpc';
        $missing = [];

        foreach ($this->files($javaDirectory, 'java') as $file) {
            $name = basename($file, '.java');

            if (! $this->isRpcApi($name)) {
                continue;
            }

            $apiName = substr($name, 0, -3);
            $pendingName = str_starts_with($apiName, 'Session')
                ? 'Pending'.substr($apiName, strlen('Session'))
                : 'Pending'.$apiName;
            $expectedFile = 'src/Rpc/'.$pendingName.'.php';

            if (! is_file($this->root.'/'.$expectedFile)) {
                $missing[] = [
                    'name' => $name,
                    'source' => $file,
                    'expected_file' => $expectedFile,
                ];
            }
        }

        return $missing;
    }

    private function isRpcApi(string $name): bool
    {
        return str_ends_with($name, 'Api')
            && (str_starts_with($name, 'Server') || str_starts_with($name, 'Session'));
    }

    private function missingEnums(array $enums): array
    {
        $local = array_map(
            fn (string $file): string => strtolower(basename($file, '.php')),
            $this->files('src/Enums', 'php'),
        );
        $missing = [];

        foreach ($enums as $key => $enum) {
            if (isset($enum['sources']['javascript']) || isset($enum['sources']['java'])) {
                $enumKey = $this->enumKey($enum['name']);
            } else {
                continue;
            }

            if (! in_array($enumKey, $local, true)) {
                $missing[] = [
                    ...$enum,
                    'expected_file' => 'src/Enums/'.$enum['name'].'.php',
                ];
            }
        }

        return $missing;
    }

    private function pythonRpcMethods(string $source, string $root): array
    {
        preg_match_all('/^class\s+(\w+)[^\n]*:\n(.*?)(?=^class\s|\z)/ms', $source, $matches, PREG_SET_ORDER);
        $classes = array_column($matches, 2, 1);
        $queue = [$root];
        $visited = [];
        $methods = [];

        while ($queue !== []) {
            $name = array_shift($queue);

            if (isset($visited[$name])) {
                continue;
            }

            if (! isset($classes[$name])) {
                throw new RuntimeException("Python RPC class not found: $name");
            }

            $visited[$name] = true;
            $methods = array_merge($methods, $this->names($classes[$name], '/self\._client\.request\(\s*["\']([^"\']+)["\']/'));
            preg_match_all('/self\.(?!_)\w+\s*=\s*(\w+Api)\(/', $classes[$name], $children);
            $queue = array_merge($queue, $children[1]);
        }

        $methods = array_values(array_unique($methods));
        sort($methods);

        return $methods;
    }

    private function phpRpcMethods(string $root, array $upstreamMethods): array
    {
        $queue = [$root];
        $visited = [];
        $methods = [];

        while ($queue !== []) {
            $name = array_shift($queue);

            if (isset($visited[$name])) {
                continue;
            }

            $visited[$name] = true;
            $code = $this->phpCode("src/Rpc/$name.php");
            $methods = array_merge($methods, $this->names($code, '/->(?:request|notify)\(\s*[\'"]([^\'"]+)[\'"]/'));
            $queue = array_merge($queue, $this->names($code, '/\bnew\s+(Pending\w+)\s*\(/'));
        }

        $entryPoints = [
            'src/Client.php',
            'src/Session.php',
            ...$this->files('src/Concerns/Client', 'php'),
            ...$this->files('src/Concerns/Session', 'php'),
        ];

        foreach ($entryPoints as $path) {
            $methods = array_merge(
                $methods,
                array_values(array_intersect(
                    $this->names($this->phpCode($path), '/->(?:request|notify)\(\s*[\'"]([^\'"]+)[\'"]/'),
                    $upstreamMethods,
                )),
            );
        }

        $methods = array_values(array_unique($methods));
        sort($methods);

        return $methods;
    }

    private function rpcMethods(string $typescript, string $python): array
    {
        $groups = [
            'ServerRpc' => [
                'javascript' => ['createServerRpc', 'createInternalServerRpc'],
                'python' => ['ServerRpc', '_InternalServerRpc'],
            ],
            'SessionRpc' => [
                'javascript' => ['createSessionRpc', 'createInternalSessionRpc'],
                'python' => ['SessionRpc', '_InternalSessionRpc'],
            ],
        ];
        $result = [];

        foreach ($groups as $root => $sources) {
            $javascriptMethods = [];
            foreach ($sources['javascript'] as $function) {
                $body = $this->block($this->withoutComments($typescript), '/export function '.$function.'\([^)]*\)\s*\{/');
                $javascriptMethods = array_merge(
                    $javascriptMethods,
                    $this->names($body, '/connection\.sendRequest\(\s*["\']([^"\']+)["\']/'),
                );
            }

            $pythonMethods = [];
            foreach ($sources['python'] as $class) {
                $pythonMethods = array_merge($pythonMethods, $this->pythonRpcMethods($python, $class));
            }

            $excludedMethods = array_keys(SDK_SYNC_RPC_METHOD_EXCLUSIONS);
            $javascriptMethods = array_values(array_diff(array_unique($javascriptMethods), $excludedMethods));
            $pythonMethods = array_values(array_diff(array_unique($pythonMethods), $excludedMethods));

            if ($javascriptMethods === [] || $pythonMethods === []) {
                throw new RuntimeException("No upstream RPC methods found for $root");
            }

            $upstream = array_values(array_unique([...$javascriptMethods, ...$pythonMethods]));
            sort($upstream);
            $local = array_values(array_diff($this->phpRpcMethods($root, $upstream), $excludedMethods));
            $result[$root] = [
                'local_root' => "src/Rpc/$root.php",
                ...$this->difference($upstream, $local),
                'language_differences' => $this->difference($javascriptMethods, $pythonMethods),
            ];
        }

        return $result;
    }

    private function clientOptions(string $types): array
    {
        $client = $this->phpCode('src/Client.php');

        foreach ($this->files('src/Concerns/Client', 'php') as $path) {
            $client .= "\n".$this->phpCode($path);
        }

        $clientKeys = array_values(array_unique([
            ...$this->names($client, '/data_get\(\s*\$(?:this->)?options\s*,\s*[\'"]([^\'"]+)[\'"]/'),
            ...$this->names($client, '/\$(?:this->)?options\[\s*[\'"]([^\'"]+)[\'"]\s*\]/'),
        ]));
        $manager = $this->phpCode('src/CopilotManager.php');
        $defaultClient = $this->block($manager, '/public function client\([^)]*\)[^{]*\{/');
        $stdio = $this->block($manager, '/public function useStdio\([^)]*\)[^{]*\{/');
        $surfaces = [
            'Client' => $clientKeys,
            'CopilotManager::client' => $this->names($defaultClient, '/[\'"]([^\'"]+)[\'"]\s*=>/'),
            'CopilotManager::useStdio' => $this->names($stdio, '/[\'"](\w+)[\'"]/'),
        ];
        $result = [];
        $knownKeys = [];

        foreach ($this->interfaceProperties($types, 'CopilotClientOptions') as $name) {
            if (isset(SDK_SYNC_CLIENT_OPTION_EXCLUSIONS[$name])) {
                continue;
            }

            $snake = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $name));
            $aliases = SDK_SYNC_CLIENT_OPTION_ALIASES[$name] ?? array_values(array_unique([$name, $snake]));
            $knownKeys = [...$knownKeys, ...$aliases];
            $missing = array_keys(array_filter($surfaces, fn (array $keys): bool => array_intersect($keys, $aliases) === []));

            if ($missing !== []) {
                $result[] = ['name' => $name, 'accepted_names' => $aliases, 'missing_in' => $missing];
            }
        }

        return [
            'missing' => $result,
            'local_only' => array_map(fn (array $keys): array => array_values(array_diff($keys, $knownKeys)), $surfaces),
        ];
    }

    private function hooks(string $types): array
    {
        $names = $this->interfaceProperties($types, 'SessionHooks');
        $code = $this->phpCode('src/Concerns/Session/HasHooks.php');
        preg_match_all('/[\'"](\w+)[\'"]\s*=>\s*\$this->hooks->(\w+)/', $code, $matches, PREG_SET_ORDER);
        $dispatch = [];

        foreach ($matches as $match) {
            if ($match[1] === lcfirst(substr($match[2], 2))) {
                $dispatch[] = $match[2];
            }
        }

        return [
            'SessionHooks' => $this->difference($names, $this->phpProperties('src/Types/SessionHooks.php')),
            'HasHooks' => $this->difference($names, $dispatch),
        ];
    }
}

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    try {
        $root = $argv[1] ?? dirname(__DIR__, 2);
        echo json_encode((new SdkSyncAudit(rtrim($root, '/')))->report(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
    } catch (Throwable $exception) {
        fwrite(STDERR, 'SDK sync audit failed: '.$exception->getMessage()."\n");
        exit(1);
    }
}
