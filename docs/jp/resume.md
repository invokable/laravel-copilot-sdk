# Resume Session

以前のセッションを再開する方法。

## Artisanコマンドで対話形式のチャットで使用している場合

```php
// listSessionsでこれまでのセッション一覧を取得
$sessions = Copilot::client()->listSessions();

// Laravel\Prompts\selectなどで再開するセッションIDを選択
$sessions = collect(Copilot::client()->listSessions())
    ->mapWithKeys(function (SessionMetadata $session) {
        return [$session->sessionId => $session->summary ?? ''];
    })
    ->toArray();

$session_id = select(
    label: 'What session do you want to resume?',
    options: $sessions,
);

// 選択されたIDでセッションを再開
$session = Copilot::client()->resumeSession($session_id);

// これまでのメッセージを取得
$messages = $session->getMessages();
```

## 固定のセッションIDを使う場合

対話形式でない場合は選択できないので最初から固定のIDを指定します。ユーザーIDやなんらかのコンテキストに応じたIDを用意します。  
例えばウェブサービス上でマルチユーザーで使うなら事前にIDを用意するこちらの方法を使います。

```php
use Revolution\Copilot\Types\SessionConfig;
use Revolution\Copilot\Contracts\CopilotSession;
use Revolution\Copilot\Facades\Copilot;

$config = new SessionConfig(
    sessionId: 'user-123-conversation',
);

Copilot::start(function (CopilotSession $session) {
    dump('Starting Copilot session: '.$session->id());

    $response = $session->sendAndWait(prompt: 'Tell me something about Laravel.');
}, config: $config);
```

再開時の方法は`Copilot::start`なら`resume`引数で指定することもできます。

```php
use Revolution\Copilot\Contracts\CopilotSession;
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\Types\ResumeSessionConfig;

// ResumeSessionConfigではsessionIdを指定できない
$config = new ResumeSessionConfig();

Copilot::start(function (CopilotSession $session) {
    dump('Starting Copilot session: '.$session->id());

    $response = $session->sendAndWait(prompt: 'Tell me something about Laravel.');
}, config: $config, resume: 'user-123-conversation');
```

`Copilot::start`のクロージャ内で今のセッションを破棄して新しく再開する方法もあります。

```php
use Revolution\Copilot\Contracts\CopilotSession;
use Revolution\Copilot\Facades\Copilot;

Copilot::start(function (CopilotSession $session) {
    dump('Starting Copilot session: '.$session->id());

    $session->disconnect();

    $session = Copilot::client()->resumeSession(sessionId: 'user-123-conversation');

    $response = $session->sendAndWait(prompt: 'Tell me something about Laravel.');
});
```

## 最後のセッションを再開

セッションが存在しない場合は`getLastSessionId()`は`null`を返すので自動的に新規セッションとして開始されます。SessionConfigの切り替えもできます。

```php
use Revolution\Copilot\Types\SessionConfig;
use Revolution\Copilot\Types\ResumeSessionConfig;
use Revolution\Copilot\Contracts\CopilotSession;
use Revolution\Copilot\Facades\Copilot;

$session_id = Copilot::client()->getLastSessionId();

if (empty($session_id)) {
    $config = new SessionConfig();
} else {
    $config = new ResumeSessionConfig();
}

Copilot::start(function (CopilotSession $session) {
    dump('Starting Copilot session: '.$session->id());

    $response = $session->sendAndWait(prompt: 'Tell me something about Laravel.');
}, config: $config, resume: $session_id);
```

## SessionMetadataを使う

`getSessionMetadata()`もセッションが存在しなければ`null`を返すので同様に新規セッションとして開始されます。セッションの作成日時や更新日時なども取得できます。

```php
use Revolution\Copilot\Types\SessionMetadata;
use Revolution\Copilot\Facades\Copilot;

$meta = Copilot::client()->getSessionMetadata('user-123-conversation');
```

## セッションのアイドルタイムアウト

デフォルトではセッションは無期限に保持されます。`session_idle_timeout_seconds`（または環境変数`COPILOT_SESSION_IDLE_TIMEOUT`）を設定すると、指定秒数間アクティビティがないセッションは自動的にクリーンアップされます。

```php
// config/copilot.php
'session_idle_timeout_seconds' => (int) env('COPILOT_SESSION_IDLE_TIMEOUT', 0),
```

```php
// useStdio()で直接指定することもできる
use Revolution\Copilot\Facades\Copilot;

Copilot::useStdio(['session_idle_timeout_seconds' => 3600]); // 1時間
```

> **Note:** このオプションはSDKがCLIプロセスを起動する場合のみ有効です。`url`でTCPサーバーに接続している場合（外部サーバー）は無視されます。
