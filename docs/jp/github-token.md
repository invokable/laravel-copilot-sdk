# 認証するGitHubトークンの実行時切り替え

Socialiteなどを使って認証したユーザーごとのGitHubトークンを使ってCopilot CLIを実行する方法。

> [!NOTE]
> Personal Access Tokenでは`Copilot Requests`の権限が必要なのでSocialiteでも同じはずです。
> Socialiteからトークンを取得できない場合はPersonal Access Tokenを直接入力してもらう方式にします。

これが可能なのはstdioモードのみです。TCPモードはCopilot CLIが起動したままなので変更できません。
TCPモード（cli_url）で`github_token`や`use_logged_in_user`を指定するとエラーになります。

> [!NOTE]
> 下記の「セッションごとのGitHubトークン（v0.3.0+）」はTCPモードでも利用可能です。

## github_token オプション

SDKの公式オプションです。トークンは環境変数`COPILOT_SDK_AUTH_TOKEN`経由でCLIに渡されます。

```php
use Revolution\Copilot\Facades\Copilot;

$config = array_merge(
    config('copilot'),
    [
        'github_token' => $user->github_token,
    ]
);

$response = Copilot::useStdio($config)->run(prompt: '...');
// 念の為ユーザートークンを持ったクライアントを破棄
Copilot::stop();
```

## use_logged_in_user オプション

`github_token`を指定すると自動的に`use_logged_in_user`は`false`になります。
これにより、CLIは保存されたOAuthトークンやgh CLI認証を使わず、明示的に渡されたトークンのみを使用します。

明示的に`use_logged_in_user`を`true`にすることもできます。

```php
$config = [
    'github_token' => $user->github_token,
    'use_logged_in_user' => true, // 明示的に有効化
];
```

`github_token`なしで`use_logged_in_user`を`false`にすると、`--no-auto-login`フラグが追加され、CLIは自動ログインを行いません。

## セッションごとのGitHubトークン（v0.3.0+）

`SessionConfig`の`gitHubToken`フィールドを使うと、同一CLIプロセス内でセッションごとに異なるGitHubトークンを指定できます（マルチテナント対応）。

このトークンは`session.create` JSON-RPCのパラメーターとして渡されるため、**stdioモードとTCPモードの両方で利用可能**です。CLIがすでに起動しているTCPモードでも、セッション単位でトークンを切り替えられます。

```php
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\Types\SessionConfig;

$response = Copilot::run(
    prompt: '...',
    config: new SessionConfig(gitHubToken: $user->github_token),
);
```

TCPモードでの使用例：

```php
// CLIサーバーは起動済み（TCPモード）
// セッションごとに異なるユーザートークンを渡せる
$response = Copilot::useTcp(url: 'tcp://127.0.0.1:12345')->run(
    prompt: '...',
    config: new SessionConfig(gitHubToken: $user->github_token),
);
```

クライアントレベルの`github_token`（CLIプロセス起動時のトークン）とは別に機能します。  
セッション単位でユーザートークンを切り替えたい場合に使用してください。
