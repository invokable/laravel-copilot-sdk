# Remote Sessions

Remote Sessionsを有効にすると、GitHubのMission Control経由でWebやモバイルから同じセッションにアクセスできます。  
このページは公式の`remote-sessions.md`をLaravel向けにまとめた日本語版です。

ローカルや自前サーバーではなくGitHubホスト環境でセッション自体を実行したい場合は [Cloud Sessions](./cloud-sessions.md) を使います。

## 前提条件

- ユーザーが認証済みであること（GitHubトークンまたはログイン済みユーザー）
- `workingDirectory` がGitHubリポジトリであること

## 公式SDK 1.0.103 で削除された設定

公式SDK 1.0.103 でクライアント設定の`remote`、セッション設定の`remoteSession`、CLI起動引数の`--remote`は削除されました。既存のリモートセッションRPCは引き続き利用できます。

## セッションごとに有効化（オンデマンド）

途中から共有を開始したい場合はRPCで切り替えます。  
CLIの`/remote on`と`/remote off`に相当します。

```php
use Revolution\Copilot\Contracts\CopilotSession;
use Revolution\Copilot\Facades\Copilot;

Copilot::start(function (CopilotSession $session): void {
    $result = $session->rpc()->remote()->enable();

    if ($result->url !== null) {
        echo 'Remote URL: '.$result->url.PHP_EOL;
    }

    // 共有停止
    $session->rpc()->remote()->disable();
});
```

## 既存のリモートセッションにサーバースコープで接続

CLIサーバーから既存のリモートセッションに接続する場合は、`$client->rpc()->sessions()->connect()`を使います。接続先のリモート`sessionId`を渡すと、SDK側の`sessionId`とリモートセッションのmetadataを含む結果が返ります。

```php
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\Types\Rpc\ConnectRemoteSessionParams;

$client = Copilot::useTcp(url: 'tcp://127.0.0.1:12345')->client();

$connected = $client->rpc()->sessions()->connect(
    new ConnectRemoteSessionParams(sessionId: 'remote-session-id'),
);

echo $connected->sessionId;
// $connected->metadata - ConnectedRemoteSessionMetadata オブジェクト
```

これはセッションスコープの`$session->rpc()->remote()->connectRemoteSession()`とは別のRPCです。後者は既存セッションに紐づく`session.remote.connectRemoteSession`を呼び出します。

## ランタイム全体の Remote Control（TCP モード）

`RemoteControlConfig`の`remote`はランタイム全体のRemote Control用設定です。公式SDK 1.0.103で削除されたクライアント起動設定の`remote`とは別です。

TCPモードでは`$client->rpc()->sessions()`からランタイム全体で1つのRemote Controlを管理できます。セッション単位の`remote()->enable()`とは異なり、操作対象セッションの開始・停止・移譲や、現在の状態の取得ができます。

```php
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\Types\Rpc\RemoteControlConfig;
use Revolution\Copilot\Types\Rpc\SessionsStartRemoteControlRequest;

$client = Copilot::useTcp(url: 'tcp://127.0.0.1:12345')->client();

$result = $client->rpc()->sessions()->startRemoteControl(
    new SessionsStartRemoteControlRequest(
        sessionId: $sessionId,
        config: new RemoteControlConfig(
            remote: true,
            steerable: true,
            explicit: true,
            silent: false,
        ),
    ),
);

if ($result->status->frontendUrl !== null) {
    echo 'Remote URL: '.$result->status->frontendUrl.PHP_EOL;
}
```

現在の状態は`off`、`connecting`、`active`、`error`のいずれかです。`active`では`frontendUrl`や`isSteerable`も参照できます。

```php
$status = $client->rpc()->sessions()->getRemoteControlStatus();

$client->rpc()->sessions()->setRemoteControlSteering(['enabled' => true]);
$client->rpc()->sessions()->transferRemoteControl([
    'toSessionId' => $nextSessionId,
    'expectedFromSessionId' => $sessionId,
]);
$client->rpc()->sessions()->stopRemoteControl([
    'expectedSessionId' => $nextSessionId,
]);
```

セッションのリモート操作可否が変わったことをランタイムに知らせるには、セッションスコープのRPCを使います。

```php
$session->rpc()->remote()->notifySteerableChanged(['remoteSteerable' => true]);
```

## QRコード生成

取得したURLはQRコードにしてモバイルへ渡せます。  
Laravel Fortifyでも使われている[BaconQrCode](https://github.com/Bacon/BaconQRCode)が利用できます。

```php
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

$writer = new Writer(
    new ImageRenderer(
        new RendererStyle(256),
        new SvgImageBackEnd(),
    ),
);

$svg = $writer->writeString($result->url ?? '');
```

## 注意点

- オンデマンド有効化（`remote()->enable()`）ではエラーになる場合があります
- 認証情報（`github_token`または`use_logged_in_user`）を適切に設定してください

## 参考

- 公式ドキュメント: https://github.com/github/copilot-sdk/blob/main/docs/features/remote-sessions.md
- [Cloud Sessions](./cloud-sessions.md)
- [RPC](./rpc.md)
- [TCPモード](./tcp-mode.md)
