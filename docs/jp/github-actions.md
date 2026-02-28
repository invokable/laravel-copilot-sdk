# GitHub Actionsでの使い方

GitHub ActionsでArtisanコマンドを実行してその中でCopilot CLIを使う例です。

GitHub Actionsではstdioモードで使うのが一般的です。

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

- `copilot-install`を使ってインストールします。pathは`copilot`のまま追加の設定不要で使えます。
- `COPILOT_GITHUB_TOKEN`で認証します。`GH_TOKEN`や`GITHUB_TOKEN`などCopilot CLIがサポートしている認証方法ならなんでもよいです。
- トークンは`Copilot Requests`の権限が必要なのでデフォルトの`GITHUB_TOKEN`ではおそらく使えません。

## `gh`コマンドを使う場合

GitHub CLI 2.86.0 でcopilotのインストール機能が追加されました。普通のGitHub Actions環境では`gh`コマンドがプリインストールされているので追加のインストール不要で使えます。おそらく最初に`gh copilot ...`コマンドが実行された時にcopilotが自動でインストールされるので`gh copilot version`だけ実行します。

> gh version 2.86.0 (2026-01-21)  
> https://github.com/cli/cli/releases/tag/v2.86.0  
> ✓ Copilot CLI installed successfully  

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

`COPILOT_CLI_PATH`は`gh copilot`の時のみ特別な対応しているので`gh copilot`以外の設定では動きません。
