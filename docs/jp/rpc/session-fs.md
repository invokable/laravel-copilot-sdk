# SessionFS コールバック

対象: SessionRpc（Session に紐づくセッション単位の操作）

RPC 全体の概要とページ一覧は [RPC](../rpc.md) を参照してください。

## SessionFS コールバック型

セッションスコープのファイルシステム操作のためのコールバック型（Request/Result）が定義されています。これらはCopilot CLIがクライアントにコールバックする際のリクエスト/レスポンスの型です。

| 型クラス | 用途 |
|---|---|
| `SessionFsReadFileRequest` / `SessionFsReadFileResult` | ファイル読み取り |
| `SessionFsWriteFileRequest` | ファイル書き込み |
| `SessionFsAppendFileRequest` | ファイル追記 |
| `SessionFsExistsRequest` / `SessionFsExistsResult` | ファイル存在確認 |
| `SessionFsStatRequest` / `SessionFsStatResult` | ファイルメタデータ取得 |
| `SessionFsMkdirRequest` | ディレクトリ作成 |
| `SessionFsReaddirRequest` / `SessionFsReaddirResult` | ディレクトリ一覧 |
| `SessionFsReaddirWithTypesRequest` / `SessionFsReaddirWithTypesResult` | 型付きディレクトリ一覧 |
| `SessionFsRmRequest` | ファイル/ディレクトリ削除 |
| `SessionFsRenameRequest` | ファイル/ディレクトリ名前変更 |

`SessionFsStatResult` / `SessionFsReaddirResult` / `SessionFsReaddirWithTypesResult` は失敗時に使う任意の `error`（`SessionFsError`）を持ちます。

### SessionFsハンドラー

Clientは`sessionFs.readFile` / `readFileBytes` / `writeFile` / `writeFileBytes` に加えて、`appendFile`、`exists`、`stat`、`mkdir`、`readdir`、`readdirWithTypes`、`rm`、`rename`、`sqliteQuery`、`sqliteTransaction`、`sqliteExists` の各リクエストを`Session`のプロバイダーへ振り分けます。ファイル操作の失敗は `ENOENT`（`ENOENT`または errno 2）/ `UNKNOWN` の`error`として返します。`exists`は例外時に`false`、`stat`は失敗時にゼロ値＋`error`を返します。プロバイダーが対応していない操作は`UNKNOWN`エラー（SQLite系はJSON-RPCエラー）になります。`sqliteQuery`/`sqliteExists`の例外はJSON-RPCエラー、`sqliteTransaction`の失敗は結果の`error`（`errorClass`付き）として返します。

プロバイダー側のインターフェース（`SessionFsFilesystemProvider`、`SessionFsSqliteCapableProvider`、`SessionFsSqliteProvider`、`SessionFsSqliteTransactionProvider`）は、従来の`SessionFsProvider`（2メソッド）を壊さないよう別インターフェースとして追加されています。

### SQLite コールバック型

| 型クラス | 用途 |
|---|---|
| `SessionFsSetProviderCapabilities` | プロバイダーの対応機能宣言（SQLiteサポートなど） |
| `SessionFsSqliteExistsRequest` / `SessionFsSqliteExistsResult` | SQLiteデータベースの存在確認 |
| `SessionFsSqliteQueryRequest` / `SessionFsSqliteQueryResult` | SQLiteクエリ実行 |
| `SessionFsSqliteTransactionRequest` / `SessionFsSqliteTransactionStatement` / `SessionFsSqliteTransactionResult` | SQLiteトランザクション（複数ステートメントをアトミックに実行） |
| `SessionFsSqliteTransactionError` | トランザクション失敗の分類（`errorClass`: `busyOrLocked` / `fatal` / `postCommitAmbiguous`。enum `SessionFSSqliteTransactionErrorClass`） |

SQLiteサポートを有効にするには、`SessionFsSetProviderRequest` の `capabilities` に `SessionFsSetProviderCapabilities(sqlite: true)` を渡します。

```php
use Revolution\Copilot\Types\Rpc\SessionFsSetProviderCapabilities;
use Revolution\Copilot\Types\Rpc\SessionFsSetProviderRequest;

$client->rpc()->sessionFs()->setProvider(new SessionFsSetProviderRequest(
    initialCwd: '/app',
    sessionStatePath: '.copilot/sessions',
    capabilities: new SessionFsSetProviderCapabilities(sqlite: true),
));
```

通常はこのRPCを直接呼ばず、Clientオプション`sessionFs`（または`config/copilot.php`の`session_fs`）を指定すると`Client::start()`が`sessionFs.setProvider`を1回だけ送信します。セッションには`createSessionFsProvider`が必要です（[SessionConfig](./session-config.md)を参照）。

`SessionFsSqliteQueryType` enum（`src/Enums/`）はクエリ種別を表します:
- `Exec`: DDL/複数ステートメント（結果なし）
- `Query`: SELECT（行を返す）
- `Run`: INSERT/UPDATE/DELETE（rowsAffected を返す）

これらの型クラスは`src/Types/Rpc/`に配置されています。
