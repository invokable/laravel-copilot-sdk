# Session Hooks

`hooks` 設定でハンドラーを提供することで、セッションのライフサイクルイベントにフックできます。

```php
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\Contracts\CopilotSession;
use Revolution\Copilot\Types\SessionHooks;
use Revolution\Copilot\Types\Hooks\PreToolUseHookInput;
use Revolution\Copilot\Types\Hooks\PreToolUseHookOutput;
use Revolution\Copilot\Types\Hooks\PostToolUseHookInput;
use Revolution\Copilot\Types\Hooks\PostToolUseHookOutput;
use Revolution\Copilot\Types\Hooks\UserPromptSubmittedHookInput;
use Revolution\Copilot\Types\Hooks\UserPromptSubmittedHookOutput;
use Revolution\Copilot\Types\Hooks\SessionStartHookInput;
use Revolution\Copilot\Types\Hooks\SessionStartHookOutput;
use Revolution\Copilot\Types\Hooks\SessionEndHookInput;
use Revolution\Copilot\Types\Hooks\SessionEndHookOutput;
use Revolution\Copilot\Types\Hooks\ErrorOccurredHookInput;
use Revolution\Copilot\Types\Hooks\ErrorOccurredHookOutput;

Copilot::start(function (CopilotSession $session) {
    $response = $session->sendAndWait(prompt: 'Hello');
    dump($response->content());
}, config: [
    'model' => 'gpt-5',
    'hooks' => new SessionHooks(
        // 各ツール実行前に呼ばれる
        onPreToolUse: function (PreToolUseHookInput $input): ?PreToolUseHookOutput {
            dump("ツール実行予定: {$input->toolName}");
            // 許可判定を返し、オプションで引数を変更
            return new PreToolUseHookOutput(
                permissionDecision: 'allow', // "allow", "deny", または "ask"
                modifiedArgs: $input->toolArgs, // オプションでツール引数を変更
                additionalContext: 'モデルへの追加コンテキスト',
            );
        },

        // 各ツール実行後に呼ばれる
        onPostToolUse: function (PostToolUseHookInput $input): ?PostToolUseHookOutput {
            dump("ツール {$input->toolName} 完了");
            // オプションで結果を変更またはコンテキストを追加
            return new PostToolUseHookOutput(
                additionalContext: '実行後のメモ',
            );
        },

        // ユーザーがプロンプトを送信したときに呼ばれる
        onUserPromptSubmitted: function (UserPromptSubmittedHookInput $input): ?UserPromptSubmittedHookOutput {
            dump("ユーザープロンプト: {$input->prompt}");
            return new UserPromptSubmittedHookOutput(
                modifiedPrompt: $input->prompt, // オプションでプロンプトを変更
            );
        },

        // セッション開始時に呼ばれる
        onSessionStart: function (SessionStartHookInput $input): ?SessionStartHookOutput {
            dump("セッション開始: {$input->source}"); // "startup", "resume", "new"
            return new SessionStartHookOutput(
                additionalContext: 'セッション初期化コンテキスト',
            );
        },

        // セッション終了時に呼ばれる
        onSessionEnd: function (SessionEndHookInput $input): ?SessionEndHookOutput {
            dump("セッション終了: {$input->reason}");
            return null;
        },

        // エラー発生時に呼ばれる
        onErrorOccurred: function (ErrorOccurredHookInput $input): ?ErrorOccurredHookOutput {
            dump("エラー発生 {$input->errorContext}: {$input->error}");
            return new ErrorOccurredHookOutput(
                errorHandling: 'retry', // "retry", "skip", または "abort"
            );
        },
    ),
]);
```

## 利用可能なフック

| フック                     | 説明                                |
|-------------------------|-----------------------------------|
| `onPreToolUse`          | ツール実行前にインターセプト。許可/拒否または引数の変更が可能。  |
| `onPostToolUse`         | ツール実行後に結果を処理。結果の変更やコンテキストの追加が可能。  |
| `onUserPromptSubmitted` | ユーザープロンプトをインターセプト。処理前にプロンプトを変更可能。 |
| `onSessionStart`        | セッション開始または再開時にロジックを実行。            |
| `onSessionEnd`          | セッション終了時のクリーンアップやログ記録。            |
| `onErrorOccurred`       | retry/skip/abort 戦略でエラーを処理。       |

## Hook Input/Output Types

### PreToolUseHookInput

| プロパティ       | 型         | 説明           |
|-------------|-----------|--------------|
| `toolName`  | `string`  | 実行するツールの名前   |
| `toolArgs`  | `?array`  | ツールに渡される引数   |
| `timestamp` | `?int`    | タイムスタンプ（ミリ秒） |
| `cwd`       | `?string` | 現在の作業ディレクトリ  |

### PreToolUseHookOutput

| プロパティ                | 型         | 説明                         |
|----------------------|-----------|----------------------------|
| `permissionDecision` | `?string` | "allow", "deny", または "ask" |
| `modifiedArgs`       | `?array`  | 変更されたツール引数                 |
| `additionalContext`  | `?string` | モデルへの追加コンテキスト              |

### PostToolUseHookInput

| プロパティ       | 型                   | 説明           |
|-------------|---------------------|--------------|
| `toolName`  | `string`            | 実行したツールの名前   |
| `toolArgs`  | `?array`            | ツールに渡された引数   |
| `result`    | `?ToolResultObject` | ツールの実行結果     |
| `timestamp` | `?int`              | タイムスタンプ（ミリ秒） |
| `cwd`       | `?string`           | 現在の作業ディレクトリ  |

### PostToolUseHookOutput

| プロパティ               | 型                   | 説明         |
|---------------------|---------------------|------------|
| `modifiedResult`    | `?ToolResultObject` | 変更されたツール結果 |
| `additionalContext` | `?string`           | 追加コンテキスト   |

### UserPromptSubmittedHookInput

| プロパティ       | 型         | 説明           |
|-------------|-----------|--------------|
| `prompt`    | `string`  | ユーザーのプロンプト   |
| `timestamp` | `?int`    | タイムスタンプ（ミリ秒） |
| `cwd`       | `?string` | 現在の作業ディレクトリ  |

### UserPromptSubmittedHookOutput

| プロパティ            | 型         | 説明         |
|------------------|-----------|------------|
| `modifiedPrompt` | `?string` | 変更されたプロンプト |

### SessionStartHookInput

| プロパティ       | 型         | 説明                              |
|-------------|-----------|---------------------------------|
| `source`    | `string`  | 開始元: "startup", "resume", "new" |
| `timestamp` | `?int`    | タイムスタンプ（ミリ秒）                    |
| `cwd`       | `?string` | 現在の作業ディレクトリ                     |

### SessionStartHookOutput

| プロパティ               | 型         | 説明             |
|---------------------|-----------|----------------|
| `additionalContext` | `?string` | セッション初期化コンテキスト |

### SessionEndHookInput

| プロパティ       | 型         | 説明           |
|-------------|-----------|--------------|
| `reason`    | `string`  | 終了理由         |
| `timestamp` | `?int`    | タイムスタンプ（ミリ秒） |
| `cwd`       | `?string` | 現在の作業ディレクトリ  |

### SessionEndHookOutput

出力なし（`null` を返す）。

### ErrorOccurredHookInput

| プロパティ          | 型         | 説明             |
|----------------|-----------|----------------|
| `error`        | `string`  | エラーメッセージ       |
| `errorContext` | `?string` | エラーが発生したコンテキスト |
| `timestamp`    | `?int`    | タイムスタンプ（ミリ秒）   |
| `cwd`          | `?string` | 現在の作業ディレクトリ    |

### ErrorOccurredHookOutput

| プロパティ           | 型         | 説明                           |
|-----------------|-----------|------------------------------|
| `errorHandling` | `?string` | "retry", "skip", または "abort" |

## ToolResultObject

ツールの実行結果を表すオブジェクト。

| プロパティ                | 型         | 説明                                         |
|----------------------|-----------|--------------------------------------------|
| `textResultForLlm`   | `?string` | LLM に渡すテキスト結果                              |
| `resultType`         | `?string` | "success", "failure", "rejected", "denied" |
| `resultForAssistant` | `?array`  | アシスタント用の結果データ                              |
