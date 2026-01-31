# GitHub Actionsでの使い方

GitHub ActionsでArtisanコマンドを実行してその中でCopilot CLIを使う例。

## `copilot`コマンドを直接インストールする場合

```yaml
      - name: Install Copilot CLI
        run: |
          curl -fsSL https://gh.io/copilot-install | bash
          copilot version

      - name: Run command
        run: php artisan copilot:ping
        env:
          COPILOT_GITHUB_TOKEN: ${{ secrets.COPILOT_GITHUB_TOKEN }}
```

- `copilot-install`を使ってインストール。pathは`copilot`のまま追加の設定不要で使える。
- `COPILOT_GITHUB_TOKEN`で認証。`GH_TOKEN`や`GITHUB_TOKEN`などCopilot CLIがサポートしている認証方法ならなんでもいい。
- トークンは`Copilot Requests`の権限が必要なのでデフォルトの`GITHUB_TOKEN`ではおそらく使えない。

## `gh`コマンドを使う場合

GitHub CLI 2.86.0 でcopilotのインストール機能が追加。普通のGitHub Actions環境では`gh`コマンドがプリインストールされているので追加のインストール不要で使える。おそらく最初に`gh copilot ...`コマンドが実行された時にcopilotが自動でインストールされるので`gh copilot version`だけ実行。

```yaml
      - name: Install Copilot CLI
        run: |
          gh --version
          gh copilot version

      - name: Run command
        run: php artisan copilot:ping
        env:
          COPILOT_GITHUB_TOKEN: ${{ secrets.COPILOT_GITHUB_TOKEN }}
          COPILOT_CLI_PATH: "gh copilot"
```

`COPILOT_CLI_PATH`は`gh copilot`の時のみ特別な対応してるので`gh copilot`以外の設定では動かない。
