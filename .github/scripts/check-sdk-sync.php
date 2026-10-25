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

const SDK_SYNC_RPC_METHOD_INCLUSIONS = [
    // 'account.getWebUrl' => 'Explicitly tracked from the upstream internal server RPC factory.',
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
        $enums = $this->enumTypes($typescript, $python);
        $javaRpc = $this->javaRpcTypeReferences();
        $rpcMethodAnalysis = $this->rpcMethods($typescript, $python);
        $rpcApis = $this->missingRpcApis(
            $javaRpc['methods_by_api'],
            $rpcMethodAnalysis['method_locations'],
            $rpcMethodAnalysis['method_scopes'],
        );
        $rpcTypes = $this->rpcTypes(
            $typescript,
            $python,
            $enums,
            $this->rpcTypeReferences($typescript, $javaRpc['types_by_method']),
            $rpcMethodAnalysis['covered_methods'],
            $rpcMethodAnalysis['method_locations'],
        );

        return [
            'schema_version' => 3,
            'scope' => 'Current upstream snapshot vs Laravel; type names and files are compared structurally, while RPC type candidates account for implemented wire methods and internal-only APIs.',
            'rpc_type_exclusions' => SDK_SYNC_RPC_TYPE_EXCLUSIONS,
            'rpc_type_coverage' => $rpcTypes['coverage'],
            'rpc_method_exclusions' => SDK_SYNC_RPC_METHOD_EXCLUSIONS,
            'rpc_method_inclusions' => SDK_SYNC_RPC_METHOD_INCLUSIONS,
            'client_option_exclusions' => SDK_SYNC_CLIENT_OPTION_EXCLUSIONS,
            'missing_rpc_types' => $rpcTypes['missing'],
            'rpc_api_coverage' => $rpcApis['coverage'],
            'missing_rpc_apis' => $rpcApis['missing'],
            'missing_enums' => $this->missingEnums($enums),
            'rpc_methods' => $rpcMethodAnalysis['methods'],
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
        if ($start === false) {
            throw new RuntimeException("Required block not found: $pattern");
        }

        return $this->blockAt($source, $start, $pattern);
    }

    private function blockAt(string $source, int $start, string $pattern): string
    {
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

    private function rpcTypes(
        string $typescript,
        string $python,
        array $enums,
        array $typeReferences,
        array $coveredMethods,
        array $methodLocations,
    ): array
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
        $coverage = [
            'covered_by_rpc_methods' => [],
            'internal_only' => [],
        ];

        foreach ($sources as $key => $type) {
            if (in_array($key, $local, true) || in_array($key, $excluded, true) || isset($enums[$this->enumKey($type['name'])])) {
                continue;
            }

            $name = $type['name'];
            $trackedMethods = $typeReferences['tracked'][$name] ?? [];
            $internalMethods = $typeReferences['internal'][$name] ?? [];
            $uncoveredMethods = array_diff($trackedMethods, array_keys($coveredMethods));

            if ($trackedMethods !== [] && $uncoveredMethods === []) {
                $localFiles = [];

                foreach ($trackedMethods as $method) {
                    $localFiles = [...$localFiles, ...($coveredMethods[$method] ?? [])];
                }

                $localFiles = array_values(array_unique($localFiles));
                sort($localFiles);
                $coverage['covered_by_rpc_methods'][] = [
                    'name' => $name,
                    'upstream_methods' => $trackedMethods,
                    'local_files' => $localFiles,
                ];

                continue;
            }

            if ($trackedMethods === [] && $internalMethods !== []) {
                $localFiles = [];

                foreach ($internalMethods as $method) {
                    $localFiles = [...$localFiles, ...($methodLocations[$method] ?? [])];
                }

                $localFiles = array_values(array_unique($localFiles));
                sort($localFiles);
                $coverage['internal_only'][] = [
                    'name' => $name,
                    'upstream_methods' => $internalMethods,
                    'local_files' => $localFiles,
                ];

                continue;
            }

            $missing[] = [
                ...$type,
                'expected_file' => 'src/Types/Rpc/'.$name.'.php',
            ];
        }

        foreach ($coverage as $key => $entries) {
            usort($entries, fn (array $left, array $right): int => $left['name'] <=> $right['name']);
            $coverage[$key] = $entries;
        }

        return ['missing' => $missing, 'coverage' => $coverage];
    }

    private function missingRpcApis(array $apiMethodsByFile, array $methodLocations, array $methodScopes): array
    {
        $javaDirectory = 'copilot-sdk/java/sdk/src/generated/java/com/github/copilot/generated/rpc';
        $missing = [];
        $coverage = [];
        $trackedMethods = array_fill_keys($methodScopes['tracked'], true);
        $internalMethods = array_fill_keys($methodScopes['internal'], true);

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

            if (is_file($this->root.'/'.$expectedFile)) {
                continue;
            }

            $apiMethods = $apiMethodsByFile[$name] ?? [];
            $apiStem = substr($name, 0, -3);

            foreach ($apiMethodsByFile as $childApi => $childMethods) {
                $childStem = substr($childApi, 0, -3);

                if (str_starts_with($childStem, $apiStem) && $childStem !== $apiStem) {
                    $apiMethods = [...$apiMethods, ...$childMethods];
                }
            }

            $apiMethods = array_values(array_unique($apiMethods));
            $excludedApiMethods = array_values(array_intersect($apiMethods, array_keys(SDK_SYNC_RPC_METHOD_EXCLUSIONS)));
            $apiMethods = array_values(array_diff($apiMethods, $excludedApiMethods));
            $trackedApiMethods = array_values(array_filter($apiMethods, fn (string $method): bool => isset($trackedMethods[$method])));
            $internalApiMethods = array_values(array_filter($apiMethods, fn (string $method): bool => isset($internalMethods[$method])));
            $unclassifiedMethods = array_values(array_diff($apiMethods, $trackedApiMethods, $internalApiMethods));
            $uncoveredMethods = array_values(array_filter(
                $trackedApiMethods,
                fn (string $method): bool => ! isset($methodLocations[$method]),
            ));

            if ($trackedApiMethods !== [] && $uncoveredMethods === [] && $unclassifiedMethods === []) {
                $coverage[] = [
                    'name' => $name,
                    'status' => 'covered_by_rpc_methods',
                    'source' => $file,
                    'methods' => array_map(
                        fn (string $method): array => [
                            'name' => $method,
                            'local_files' => $methodLocations[$method],
                        ],
                        $trackedApiMethods,
                    ),
                ];

                continue;
            }

            if ($trackedApiMethods === [] && $internalApiMethods !== [] && $unclassifiedMethods === []) {
                $coverage[] = [
                    'name' => $name,
                    'status' => 'internal_only',
                    'source' => $file,
                    'methods' => array_map(
                        fn (string $method): array => [
                            'name' => $method,
                            'local_files' => $methodLocations[$method] ?? [],
                        ],
                        $internalApiMethods,
                    ),
                ];

                continue;
            }

            if ($apiMethods === [] && $excludedApiMethods !== []) {
                $coverage[] = [
                    'name' => $name,
                    'status' => 'excluded_methods',
                    'source' => $file,
                    'methods' => array_map(
                        fn (string $method): array => [
                            'name' => $method,
                            'reason' => SDK_SYNC_RPC_METHOD_EXCLUSIONS[$method],
                        ],
                        $excludedApiMethods,
                    ),
                ];

                continue;
            }

            $missing[] = [
                'name' => $name,
                'source' => $file,
                'expected_file' => $expectedFile,
                'upstream_methods' => [...$apiMethods, ...$excludedApiMethods],
                'uncovered_methods' => $uncoveredMethods,
                'unclassified_methods' => $unclassifiedMethods,
            ];
        }

        usort($coverage, fn (array $left, array $right): int => $left['name'] <=> $right['name']);

        return ['missing' => $missing, 'coverage' => $coverage];
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

    private function phpRpcMethods(string $root, array $upstreamMethods, ?array &$locations = null): array
    {
        $queue = [$root];
        $visited = [];
        $methods = [];
        $locations = [];

        while ($queue !== []) {
            $name = array_shift($queue);

            if (isset($visited[$name])) {
                continue;
            }

            $visited[$name] = true;
            $path = "src/Rpc/$name.php";
            $code = $this->phpCode($path);
            $fileMethods = $this->names($code, '/->(?:request|notify)\(\s*[\'"]([^\'"]+)[\'"]/');
            $methods = array_merge($methods, $fileMethods);

            foreach ($fileMethods as $method) {
                $locations[$method][] = $path;
            }

            $queue = array_merge($queue, $this->names($code, '/\bnew\s+(Pending\w+)\s*\(/'));
        }

        $entryPoints = [
            'src/Client.php',
            'src/Session.php',
            ...$this->files('src/Concerns/Client', 'php'),
            ...$this->files('src/Concerns/Session', 'php'),
        ];

        foreach ($entryPoints as $path) {
            $fileMethods = $this->names($this->phpCode($path), '/->(?:request|notify)\(\s*[\'"]([^\'"]+)[\'"]/');
            $methods = array_merge(
                $methods,
                array_values(array_intersect($fileMethods, $upstreamMethods)),
            );

            foreach (array_intersect($fileMethods, $upstreamMethods) as $method) {
                $locations[$method][] = $path;
            }
        }

        $methods = array_values(array_unique($methods));
        sort($methods);

        foreach ($locations as $method => $files) {
            $files = array_values(array_unique($files));
            sort($files);
            $locations[$method] = $files;
        }

        return $methods;
    }

    /**
     * @return array{tracked: array<string, array<int, string>>, internal: array<string, array<int, string>>}
     */
    private function rpcTypeReferences(string $typescript, array $javaTypeReferences): array
    {
        $factories = [
            'tracked' => ['createServerRpc', 'createSessionRpc'],
            'internal' => ['createInternalServerRpc', 'createInternalSessionRpc'],
        ];
        $references = ['tracked' => [], 'internal' => []];
        $methodScopes = ['tracked' => [], 'internal' => []];

        foreach ($factories as $scope => $functions) {
            foreach ($functions as $function) {
                foreach ($this->javascriptRpcTypes($typescript, $function) as $method => $types) {
                    $methodScope = $scope === 'internal' && isset(SDK_SYNC_RPC_METHOD_INCLUSIONS[$method])
                        ? 'tracked'
                        : $scope;

                    $methodScopes[$methodScope][$method] = true;

                    if (isset(SDK_SYNC_RPC_METHOD_EXCLUSIONS[$method])) {
                        continue;
                    }

                    foreach ($this->expandJavascriptTypes($typescript, $types) as $type) {
                        $references[$methodScope][$type][] = $method;
                    }
                }
            }
        }

        foreach ($javaTypeReferences as $method => $types) {
            $scope = isset($methodScopes['tracked'][$method])
                ? 'tracked'
                : (isset($methodScopes['internal'][$method]) ? 'internal' : null);

            if ($scope === null || isset(SDK_SYNC_RPC_METHOD_EXCLUSIONS[$method])) {
                continue;
            }

            foreach ($types as $type) {
                foreach ($this->expandJavascriptTypes($typescript, [$type]) as $expandedType) {
                    $references[$scope][$expandedType][] = $method;
                }
            }
        }

        foreach ($references as $scope => $types) {
            foreach ($types as $name => $methods) {
                $methods = array_values(array_unique($methods));
                sort($methods);
                $references[$scope][$name] = $methods;
            }
        }

        return $references;
    }

    private function expandJavascriptTypes(string $typescript, array $types): array
    {
        $source = $this->withoutComments($typescript);
        $knownTypes = array_fill_keys($this->names($source, '/^export\s+(?:interface|type)\s+(\w+)/m'), true);
        $queue = array_values(array_unique($types));
        $expanded = [];

        while ($queue !== []) {
            $name = array_shift($queue);

            if (isset($expanded[$name])) {
                continue;
            }

            $expanded[$name] = true;

            if (! isset($knownTypes[$name])) {
                continue;
            }

            $interfacePattern = '/^export\s+interface\s+'.preg_quote($name, '/').'\b[^{}]*\{/m';

            if (preg_match($interfacePattern, $source)) {
                $body = $this->block($source, $interfacePattern);
            } elseif (preg_match(
                '/^export\s+type\s+'.preg_quote($name, '/').'\s*=\s*(.*?);/ms',
                $source,
                $alias,
            )) {
                $body = $alias[1];
            } else {
                continue;
            }

            foreach ($this->names($body, '/\b([A-Z]\w*)\b/') as $dependency) {
                if (isset($knownTypes[$dependency]) && ! isset($expanded[$dependency])) {
                    $queue[] = $dependency;
                }
            }
        }

        return array_keys($expanded);
    }

    private function javaRpcTypeReferences(): array
    {
        $directory = 'copilot-sdk/java/sdk/src/generated/java/com/github/copilot/generated/rpc';
        $references = [];
        $apiMethods = [];
        $apiStems = [];
        $javaFiles = $this->files($directory, 'java');

        foreach ($javaFiles as $path) {
            $fileName = basename($path, '.java');

            if (! $this->isRpcApi($fileName)) {
                continue;
            }

            $apiStem = substr($fileName, 0, -3);
            $apiStems[] = $apiStem;
            $source = $this->withoutComments($this->read($path));
            preg_match_all(
                '/(?:public\s+|protected\s+|private\s+)?CompletableFuture<(.+?)>\s+(\w+)\s*\(([^)]*)\)\s*\{/',
                $source,
                $declarations,
                PREG_SET_ORDER | PREG_OFFSET_CAPTURE,
            );

            foreach ($declarations as $declaration) {
                $signature = $declaration[0][0];
                $offset = $declaration[0][1];
                $start = strpos($source, '{', $offset + strlen($signature) - 1);

                if ($start === false) {
                    continue;
                }

                $body = $this->blockAt($source, $start, "Java RPC method in $path");
                $methods = $this->names($body, '/caller\.invoke\(\s*["\']([^"\']+)["\']/');
                $types = $this->names($signature, '/\b([A-Z]\w*)\b/');
                $methodName = $declaration[2][0];

                foreach ($methods as $method) {
                    $references[$method] = [...($references[$method] ?? []), ...$types];
                    $apiMethods[$fileName][$methodName] = $method;
                }
            }
        }

        usort($apiStems, fn (string $left, string $right): int => strlen($right) <=> strlen($left));

        foreach ($javaFiles as $path) {
            $typeName = basename($path, '.java');

            if ($this->isRpcApi($typeName) || ! preg_match('/(?:Params|Request|Result|Response)$/', $typeName)) {
                continue;
            }

            $operationType = preg_replace('/(?:Params|Request|Result|Response)$/', '', $typeName);

            foreach ($apiStems as $apiName) {
                if (! str_starts_with($operationType, $apiName)) {
                    continue;
                }

                $methodName = lcfirst(substr($operationType, strlen($apiName)));
                $apiFile = $apiName.'Api';
                $method = $apiMethods[$apiFile][$methodName] ?? null;

                if ($method !== null) {
                    $references[$method][] = $typeName;
                }

                break;
            }
        }

        foreach ($references as $method => $types) {
            $references[$method] = array_values(array_unique($types));
        }

        $methodsByApi = [];

        foreach ($apiMethods as $apiName => $methods) {
            $methodsByApi[$apiName] = array_values(array_unique(array_values($methods)));
            sort($methodsByApi[$apiName]);
        }

        return [
            'types_by_method' => $references,
            'methods_by_api' => $methodsByApi,
        ];
    }

    private function javascriptRpcTypes(string $typescript, string $function): array
    {
        $source = $this->withoutComments($typescript);
        $body = $this->block($source, '/export function '.preg_quote($function, '/').'\([^)]*\)\s*\{/');
        preg_match_all(
            '/\w+\s*:\s*async\s*\(([^)]*)\)\s*(?::\s*Promise<([^>]+)>)?\s*=>\s*/s',
            $body,
            $matches,
            PREG_SET_ORDER | PREG_OFFSET_CAPTURE,
        );
        $methods = [];

        foreach ($matches as $index => $match) {
            $signature = $match[0][0];
            $expressionStart = $match[0][1] + strlen($signature);
            $nextDeclarationStart = $matches[$index + 1][0][1] ?? strlen($body);
            $expression = substr($body, $expressionStart, $nextDeclarationStart - $expressionStart);
            $openingBrace = strpos($expression, '{');

            if ($openingBrace !== false && trim(substr($expression, 0, $openingBrace)) === '') {
                $expression = $this->blockAt(
                    $body,
                    $expressionStart + $openingBrace,
                    "JavaScript RPC method in $function",
                );
            }

            if (preg_match('/connection\.sendRequest\(\s*["\']([^"\']+)["\']/', $expression, $wireMethod)) {
                $returnType = $match[2][0] ?? '';
                $types = $this->names($match[1][0].' '.$returnType, '/\b([A-Z]\w*)\b/');
                $methods[$wireMethod[1]] = $types;
            }
        }

        return $methods;
    }

    private function rpcMethods(string $typescript, string $python): array
    {
        $groups = [
            'ServerRpc' => [
                'javascript' => ['createServerRpc'],
                'python' => ['ServerRpc'],
                'internal_javascript' => ['createInternalServerRpc'],
                'internal_python' => ['_InternalServerRpc'],
            ],
            'SessionRpc' => [
                'javascript' => ['createSessionRpc'],
                'python' => ['SessionRpc'],
                'internal_javascript' => ['createInternalSessionRpc'],
                'internal_python' => ['_InternalSessionRpc'],
            ],
        ];
        $result = [];
        $coveredMethods = [];
        $methodLocations = [];
        $trackedScope = [];
        $internalScope = [];

        foreach ($groups as $root => $sources) {
            $javascriptMethods = [];
            foreach ($sources['javascript'] as $function) {
                $javascriptMethods = [...$javascriptMethods, ...array_keys($this->javascriptRpcTypes($typescript, $function))];
            }

            $pythonMethods = [];
            foreach ($sources['python'] as $class) {
                $pythonMethods = array_merge($pythonMethods, $this->pythonRpcMethods($python, $class));
            }

            $internalMethods = [];
            foreach ($sources['internal_javascript'] as $function) {
                $internalMethods = [...$internalMethods, ...array_keys($this->javascriptRpcTypes($typescript, $function))];
                $javascriptMethods = [
                    ...$javascriptMethods,
                    ...array_values(array_intersect(array_keys($this->javascriptRpcTypes($typescript, $function)), array_keys(SDK_SYNC_RPC_METHOD_INCLUSIONS))),
                ];
            }

            foreach ($sources['internal_python'] as $class) {
                $internalPythonMethods = $this->pythonRpcMethods($python, $class);
                $internalMethods = [...$internalMethods, ...$internalPythonMethods];
                $pythonMethods = [
                    ...$pythonMethods,
                    ...array_values(array_intersect($internalPythonMethods, array_keys(SDK_SYNC_RPC_METHOD_INCLUSIONS))),
                ];
            }

            $excludedMethods = array_keys(SDK_SYNC_RPC_METHOD_EXCLUSIONS);
            $javascriptMethods = array_values(array_diff(array_unique($javascriptMethods), $excludedMethods));
            $pythonMethods = array_values(array_diff(array_unique($pythonMethods), $excludedMethods));

            if ($javascriptMethods === [] || $pythonMethods === []) {
                throw new RuntimeException("No upstream RPC methods found for $root");
            }

            $upstream = array_values(array_unique([...$javascriptMethods, ...$pythonMethods]));
            sort($upstream);
            $localMethods = $this->phpRpcMethods($root, $upstream, $localMethodLocations);
            $local = array_values(array_intersect($localMethods, $upstream));
            $internalMethods = array_values(array_unique($internalMethods));
            $trackedScope = [...$trackedScope, ...array_fill_keys($upstream, true)];
            $internalScope = [...$internalScope, ...array_fill_keys($internalMethods, true)];
            $localOnly = array_values(array_diff($localMethods, $upstream, $internalMethods, $excludedMethods));
            $result[$root] = [
                'local_root' => "src/Rpc/$root.php",
                ...$this->difference($upstream, $local),
                'local_only' => $localOnly,
                'language_differences' => $this->difference($javascriptMethods, $pythonMethods),
            ];

            foreach ($localMethodLocations as $method => $files) {
                $methodLocations[$method] = [...($methodLocations[$method] ?? []), ...$files];
            }

            foreach (array_intersect($upstream, $localMethods) as $method) {
                $coveredMethods[$method] = $localMethodLocations[$method] ?? ["src/Rpc/$root.php"];
            }
        }

        foreach ($methodLocations as $method => $files) {
            $files = array_values(array_unique($files));
            sort($files);
            $methodLocations[$method] = $files;
        }

        $internalScope = array_diff_key($internalScope, $trackedScope);
        $trackedScope = array_keys($trackedScope);
        $internalScope = array_keys($internalScope);
        sort($trackedScope);
        sort($internalScope);

        return [
            'methods' => $result,
            'covered_methods' => $coveredMethods,
            'method_locations' => $methodLocations,
            'method_scopes' => [
                'tracked' => $trackedScope,
                'internal' => $internalScope,
            ],
        ];
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
