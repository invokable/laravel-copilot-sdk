# Memories

対象: ServerRpc（Client に紐づくサーバー全体の操作）

RPC 全体の概要とページ一覧は [RPC](../rpc.md) を参照してください。

## memories.import（experimental）

`memories()->import()`はセマンティックなメモリ JSONL を検証し、サーバーのアカウント・リポジトリポリシーに従ってレコードを保存します。`jsonl`には入力全体、`workingDirectory`にはメモリサービスが使用するディレクトリを指定します。`onConflict`には`skip`または`error`を指定できます。

`dryRun: true`では検証のみを行い、レコードを送信しません。実際に取り込む場合は`dryRun: false`を指定します。

```php
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\Types\Rpc\MemoriesImportParams;

// memories.import
$result = Copilot::client()->rpc()->memories()->import(new MemoriesImportParams(
    jsonl: $jsonl, // セマンティックなメモリ JSONL の全内容
    workingDirectory: '/path/to/project',
    dryRun: true,
    onConflict: 'skip',
));

// $result->ok, $result->code, $result->message, $result->dryRun
// $result->imported は total / imported / skipped / failed の件数
// $result->diagnostics は検証結果、$result->records は入力順の各レコードの結果
```

各診断には`line`、`code`、`message`、任意の`externalId`が含まれます。各レコードの結果にはこれらに加えて`status`が含まれます。引数は配列でも指定できます。
