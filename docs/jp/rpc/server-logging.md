# Server file logging

対象: ServerRpc（Client に紐づくサーバー全体の操作）

RPC 全体の概要とページ一覧は [RPC](../rpc.md) を参照してください。

`logging()->file()`は、ネイティブホストが許可した出力先へランタイムロガーを通してログを書き込みます。リクエストからファイルパスを指定することはできません。このAPIはexperimentalです。

## ロガーを開いて書き込む

`open()`は不透明な`logId`を返します。`secretValues`に登録した値と各メッセージはランタイムによって秘匿化されます。書き込みは順番に完了させてから`flush()`または`close()`を呼び出してください。

```php
use Revolution\Copilot\Enums\FileLogLevel;
use Revolution\Copilot\Enums\FileLogOperation;
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\Types\Rpc\LoggingFileOpenRequest;
use Revolution\Copilot\Types\Rpc\LoggingFileWriteRequest;

$fileLogger = Copilot::client()->rpc()->logging()->file();

$opened = $fileLogger->open(new LoggingFileOpenRequest(
    level: FileLogLevel::Info,
    debugEnvironmentVariables: ['APP_DEBUG'],
    secretValues: [$secret],
));

$fileLogger->write(new LoggingFileWriteRequest(
    logId: $opened->logId,
    operation: FileLogOperation::Info,
    message: 'Background task started',
));

$fileLogger->write(new LoggingFileWriteRequest(
    logId: $opened->logId,
    operation: FileLogOperation::StartGroup,
    message: 'Processing items',
    groupLevel: FileLogLevel::Debug,
));
```

`FileLogOperation`は`log`、`debug`、`info`、`notice`、`warning`、`error`、`startGroup`、`endGroup`を指定できます。`endGroup`では`message`に空文字列を指定します。

## フラッシュとクローズ

```php
use Revolution\Copilot\Types\Rpc\LoggingFileCloseRequest;
use Revolution\Copilot\Types\Rpc\LoggingFileFlushRequest;

$fileLogger->flush(new LoggingFileFlushRequest($opened->logId));
$fileLogger->close(new LoggingFileCloseRequest($opened->logId));
```

`flush()`はそれまでの書き込みがファイルへ到達するのを待ち、失敗を報告します。`close()`もフラッシュしてからロガーを閉じます。クローズ後は同じ`logId`を使用できません。
