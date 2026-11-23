# セッションメモリ

対象: SessionRpc（Session に紐づくセッション単位の操作）

RPC 全体の概要とページ一覧は [RPC](../rpc.md) を参照してください。

## memory.importRecord（experimental）

`memory()->importRecord()`は、ユーザーが明示的に指定した1件のメモリを、セッションのクラウドメモリサービスに取り込みます。入力内容からの推論は行いません。`scope`は`MemoryImportScope::User`（現在のユーザー）または`MemoryImportScope::Repository`（セッションで解決されたリポジトリ）を指定します。

```php
use Revolution\Copilot\Contracts\CopilotSession;
use Revolution\Copilot\Enums\MemoryImportScope;
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\Types\Rpc\SessionMemoryImportRecordRequest;

Copilot::start(function (CopilotSession $session): void {
    // memory.importRecord
    $result = $session->rpc()->memory()->importRecord(new SessionMemoryImportRecordRequest(
        scope: MemoryImportScope::Repository,
        subject: 'テストコマンド',
        fact: 'このプロジェクトのテストは composer test で実行する。',
        reason: '今後の変更を検証する際に、プロジェクトのテストコマンドを使うため。',
        citations: 'composer.json:scripts.test',
    ));

    // $result->ok: メモリが保存されたか
    // $result->status: 取り込み結果
    // $result->code: 機械可読の結果コード
    // $result->message: サービスまたはランタイムからの説明
});
```

`subject`は短い件名、`fact`は保存する事実、`reason`は保存する理由、`citations`は事実を裏付ける出典です。すべて必須です。返り値は`SessionMemoryImportRecordResult`で、`status`は`MemoryImportStatus|string`です。既知の値はenumになり、未知の値は文字列のまま保持されます。

| status | 意味 |
|---|---|
| `imported` | 保存された |
| `conflict` | 競合によりサービスが拒否した |
| `disabled` | ポリシーまたは設定で保存が無効 |
| `unavailable` | クラウドメモリストレージを利用できない |
| `failed` | サービスが保存できなかった |

セッションの認証情報、リポジトリ、メモリポリシー、権限チェック、シークレットフィルタリング、テレメトリが適用されます。競合やサービス障害は結果として返され、ローカルストアやホストストアへのフォールバックはありません。機密情報を含む内容を送らないよう、入力を確認してください。

複数レコードのJSONL取り込みはサーバー単位の [memories.import](server-memories.md) を参照してください。
