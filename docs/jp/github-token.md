# 認証するGitHubトークンの実行時切り替え

Socialiteなどを使って認証したユーザーごとのGitHubトークンを使ってCopilot CLIを実行する方法。

> [!NOTE]
> Personal Access Tokenでは`Copilot Requests`の権限が必要なのでSocialiteでも同じはず。
> Socialiteからトークンを取得できない場合はPersonal Access Tokenを直接入力してもらう方式にする。

これが可能なのはstdioモードのみ。TCPモードはCopilot CLIが起動したままなので変更できない。
TCPモード（cli_url）で`github_token`や`use_logged_in_user`を指定するとエラーになる。

## github_token オプション

SDKの公式オプション。トークンは環境変数`COPILOT_SDK_AUTH_TOKEN`経由でCLIに渡される。

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

`github_token`を指定すると自動的に`use_logged_in_user`は`false`になる。
これにより、CLIは保存されたOAuthトークンやgh CLI認証を使わず、明示的に渡されたトークンのみを使用する。

明示的に`use_logged_in_user`を`true`にすることもできる。

```php
$config = [
    'github_token' => $user->github_token,
    'use_logged_in_user' => true, // 明示的に有効化
];
```

`github_token`なしで`use_logged_in_user`を`false`にすると、`--no-auto-login`フラグが追加され、CLIは自動ログインを行わない。
