# 認証するGitHubトークンの実行時切り替え

Socialiteなどを使って認証したユーザーごとのGitHubトークンを使ってCopilot CLIを実行する方法。

> [!NOTE]
> 実際に試せてない段階での情報。
> Personal Access Tokenでは`Copilot Requests`の権限が必要なのでSocialiteでも同じはず。
> Socialiteからトークンを取得できない場合はPersonal Access Tokenを直接入力してもらう方式にする。

これが可能なのはstdioモードのみ。TCPモードはCopilot CLIが起動したままなので変更できない。

```php
use Revolution\Copilot\Facades\Copilot;

$config = array_merge(
    config('copilot'),
    [
        'env' => [
            'COPILOT_GITHUB_TOKEN' => $user->github_token,
        ],
    ]
);

$response = Copilot::useStdio($config)->run(prompt: '...');
// 念の為ユーザートークンを持ったクライアントを破棄
Copilot::stop();
```

`COPILOT_GITHUB_TOKEN`は`GH_TOKEN`や`GITHUB_TOKEN`などすでに使っている環境変数を上書きする形で指定する。
