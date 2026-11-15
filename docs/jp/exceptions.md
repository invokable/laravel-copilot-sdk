# 例外・エラー処理

SDKがスローする例外は、すべて `Revolution\Copilot\Exceptions` 名前空間にあります。発生のしかたは2通りあり、セッションの実行結果に保持されて `throw()` でスローされるものと、RPC呼び出しなどでその場でスローされるものがあります。

## セッションのエラーとタイムアウト

`sendAndWait()`や`Copilot::run()`では、セッションエラーやタイムアウトが発生してもその場ではスローされず、返ってきた`SessionEvent`に例外が保持されます。LaravelのHttpやProcessと同様に`throw()`を呼ぶと保持された例外がスローされ、エラーでないときは何もしないので、そのままメソッドチェーンを続けられます。

```php
use Revolution\Copilot\Exceptions\SessionErrorException;
use Revolution\Copilot\Exceptions\SessionTimeoutException;
use Revolution\Copilot\Facades\Copilot;

try {
    $content = Copilot::run('1 + 1 を計算して')->throw()->content();
} catch (SessionTimeoutException $e) {
    // タイムアウト時の処理
} catch (SessionErrorException $e) {
    // セッションエラー時の処理
}
```

例外をスローせずに成否だけを確認したい場合は、`failed()`と`successful()`が使えます。

### SessionTimeoutException

`session.idle`（応答の完了）を待つ間にタイムアウトすると保持されます。タイムアウトは`sendAndWait()`の`timeout`引数で指定でき、省略した場合は`copilot.timeout`設定（デフォルトは60秒）が使われます。

### SessionErrorException

セッションが`SESSION_ERROR`イベントでエラーを返すと保持されます。メッセージにはランタイムから返されたエラーの内容が含まれます。

## JsonRpcException

RPC呼び出しに対してCLIがJSON-RPCエラーを返すと、その場でスローされます。JSON-RPCのエラーコードは`code`プロパティ、エラーに付随するデータは`data`プロパティで取得できます。

```php
use Revolution\Copilot\Exceptions\JsonRpcException;
use Revolution\Copilot\Facades\Copilot;

try {
    Copilot::client()->rpc()->ping();
} catch (JsonRpcException $e) {
    $e->code; // JSON-RPCのエラーコード
    $e->data; // エラーに付随するデータ
}
```

## SessionFsSqliteTransactionFailure

SessionFsのSQLiteプロバイダーを実装する場合に使う例外です。捕捉するものではなく、プロバイダーの`transaction()`からスローして失敗の分類をランタイムへ伝えます。分類は`errorClass`で指定します。

- `Fatal`（デフォルト）: 回復不能な失敗
- `BusyOrLocked`: コミット前にSQLiteがBUSY/LOCKEDを返し、トランザクションがロールバックされた場合のみ使います
- `PostCommitAmbiguous`: コミット後の結果が確定できない場合

これ以外の例外を`transaction()`からスローすると、`fatal`として報告されます。

```php
use Revolution\Copilot\Enums\SessionFSSqliteTransactionErrorClass;
use Revolution\Copilot\Exceptions\SessionFsSqliteTransactionFailure;

throw new SessionFsSqliteTransactionFailure(
    'database is locked',
    SessionFSSqliteTransactionErrorClass::BusyOrLocked,
);
```

## StrayRequestException

テスト用のFakeで、対応するFakeが登録されていないリクエストを送るとスローされます。意図せず発生した場合は、リクエストに対応するFakeの登録漏れです。Fakeの使い方は[テスト用機能](fake.md)を参照してください。
