# Laravel Cloudでの使い方

Settings > Deployments > Build commandsに以下を追加してデプロイ。

```shell
export PATH="$PATH:/var/www/.local/bin"
curl -fsSL https://gh.io/copilot-install | bash
/var/www/.local/bin/copilot --version
```

pathは`/var/www/.local/bin/copilot`なのでCustom environment variablesで以下を追加。

```dotenv
COPILOT_CLI_PATH=/var/www/.local/bin/copilot
COPILOT_GITHUB_TOKEN=
```

`COPILOT_GITHUB_TOKEN`は`GH_TOKEN`や`GITHUB_TOKEN`などCopilot CLIがサポートしている認証方法ならなんでもいい。

### 注意点
- 安いサーバーではメモリ不足になりやすい。
- デフォルトのHTTPタイムアウトは20秒なのでよく失敗する場合は最大の60秒まで伸ばす。
