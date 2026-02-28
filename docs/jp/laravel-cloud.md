# Laravel Cloudでの使い方

## インストール
Settings > Deployments > Build commandsに以下を追加してデプロイします。

```shell
export PATH="$PATH:/var/www/.local/bin"
curl -fsSL https://gh.io/copilot-install | bash
/var/www/.local/bin/copilot --version
```

pathは`/var/www/.local/bin/copilot`なのでCustom environment variablesで以下を追加します。

```dotenv
COPILOT_CLI_PATH=/var/www/.local/bin/copilot
COPILOT_GITHUB_TOKEN=
```

`COPILOT_GITHUB_TOKEN`は`GH_TOKEN`や`GITHUB_TOKEN`などCopilot CLIがサポートしている認証方法ならなんでもよいです。

デフォルトのstdioモードで使う場合はこのインストールのみで完了です。

## TCPモードでの運用

App cluster > Background processes > Custom worker で`/var/www/.local/bin/copilot --headless --port 12345`を設定します。バックグラウンドプロセスとして常に稼働し続けます。デプロイ時には自動で再起動します。

portの設定は自由。

Custom environment variablesでCOPILOT_URLを設定します。
```dotenv
COPILOT_URL=tcp://127.0.0.1:12345
#COPILOT_CLI_PATH=TCPモードでは不要。両方設定している場合はTCPモードが優先される。
```

「常に稼働し続ける」ということはメモリ使用量も多いので2GiB RAM以上のサーバーが必要かもしれません。  
Httpリクエスト内で使うと60秒以内に終わらずほとんど失敗するのでキューで使います。
Laravel CloudでのTCPモードは実用的ではないのでstdioモードで使うほうが推奨です。

上位プランで使えるWorker clusterでも使えるはずです。App clusterがメモリ不足で落ちるのを防ぎたいならWorker clusterを使うのも有効です。

## 注意点
- 安いサーバーではメモリ不足になりやすいです。
- デフォルトのHTTPタイムアウトは20秒なのでよく失敗する場合は最大の60秒まで伸ばしてください。
