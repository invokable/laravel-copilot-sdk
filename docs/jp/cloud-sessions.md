# Cloud Sessions

Cloud Sessionsは、ローカルのCopilot CLIプロセスではなくGitHub側のホスト環境でセッションを実行する機能です。Mission Control上でタスクが予約され、クラウド側の`copilot-agent`が接続して処理を進めます。

通常のRemote Sessionsは「ローカルで動くセッションをGitHub Web/モバイルから見えるようにする」機能です。実行場所自体をGitHubホスト環境にしたい場合はCloud Sessionsを使います。

## 前提条件

- ユーザーがCloud Agentを利用できるCopilot権限を持っている
- GitHubトークン、またはログイン済みCopilot CLIユーザーで認証できる
- 可能ならGitHubリポジトリ情報を関連付ける
- 組織ポリシーでクラウド実行とリモート閲覧が許可されている

## 基本的な使い方

`SessionConfig`の`cloud`に`CloudSessionOptions`を指定します。リポジトリ情報はSDK型としては任意ですが、Mission Controlとクラウドエージェントに文脈を渡すため指定することを推奨します。

```php
use Revolution\Copilot\Contracts\CopilotSession;
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\Support\PermissionHandler;
use Revolution\Copilot\Types\CloudSessionOptions;
use Revolution\Copilot\Types\CloudSessionRepository;
use Revolution\Copilot\Types\SessionConfig;

Copilot::start(function (CopilotSession $session): void {
    $session->sendAndWait(prompt: 'READMEを要約して');
}, config: new SessionConfig(
    onPermissionRequest: PermissionHandler::approveSafety(),
    cloud: new CloudSessionOptions(
        repository: new CloudSessionRepository(
            owner: 'myorg',
            name: 'myrepo',
            branch: 'main',
        ),
    ),
));
```

配列でも指定できます。

```php
Copilot::run(
    prompt: 'このリポジトリのテスト方針を確認して',
    config: [
        'cloud' => [
            'repository' => [
                'owner' => 'myorg',
                'name' => 'myrepo',
                'branch' => 'main',
            ],
        ],
    ],
);
```

## 最初のプロンプトを送るタイミング

Cloud Sessionは2段階で初期化されます。`session.create`はMission Controlでタスクが予約された時点で戻りますが、クラウド側の`copilot-agent`が接続して`session.start`を発行するまで少し時間があります。

最初のプロンプトを確実に届けるには、先にイベントを購読し、`producer`が`copilot-agent`の`session.start`を確認してから送信してください。

```php
use Revolution\Copilot\Contracts\CopilotSession;
use Revolution\Copilot\Enums\SessionEventType;
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\Support\PermissionHandler;
use Revolution\Copilot\Types\CloudSessionOptions;
use Revolution\Copilot\Types\CloudSessionRepository;
use Revolution\Copilot\Types\SessionConfig;

$config = new SessionConfig(
    streaming: true,
    onPermissionRequest: PermissionHandler::approveSafety(),
    cloud: new CloudSessionOptions(
        repository: new CloudSessionRepository(owner: 'myorg', name: 'myrepo'),
    ),
);

Copilot::start(function (CopilotSession $session): void {
    $cloudAgentStarted = false;

    foreach ($session->stream(timeout: 30.0) as $event) {
        if (
            $event->is(SessionEventType::SESSION_START)
            && $event->data('producer') === 'copilot-agent'
        ) {
            $cloudAgentStarted = true;
            break;
        }
    }

    if (! $cloudAgentStarted) {
        throw new RuntimeException('Cloud session did not become ready.');
    }

    $session->sendAndWait(prompt: 'READMEを要約して');
}, config: $config);
```

実アプリでは、キューや非同期処理側で`session.start`確認後にプロンプトを送る構成にすると扱いやすいです。

## Remote Sessionsとの違い

| 機能 | 実行場所 | 主な用途 |
|---|---|---|
| Remote Sessions | ローカルまたは自前サーバー | Web/モバイルから既存セッションを閲覧・操作する |
| Cloud Sessions | GitHubホスト環境 | ユーザー端末や自前サーバーでCopilot CLIを動かさずに処理する |

## 注意点

- Cloud Sessionsは権限と組織ポリシーの影響を受けます
- `streaming: true`にすると`assistant.message_delta`などのリアルタイムイベントを受け取れます
- 権限リクエストの扱いは通常セッションと同じです。Laravel版のFacade経由ではデフォルトでdeny-allなので、必要に応じて`PermissionHandler::approveSafety()`などを指定してください

## 関連ドキュメント

- [Remote Sessions](./remote-sessions.md)
- [SessionConfig](./session-config.md)
- [Streaming Events](./streaming-events.md)
