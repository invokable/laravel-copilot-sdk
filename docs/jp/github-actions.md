# GitHub Actionsでの使い方

GitHub ActionsでArtisanコマンドを実行してその中でCopilot CLIを使う例。

## `copilot`コマンドを直接インストールする場合

```yaml
      - name: Install Copilot CLI
        run: |
          curl -fsSL https://gh.io/copilot-install | bash
          copilot --version

      - name: Run command
        run: php artisan copilot:ping
        env:
          COPILOT_GITHUB_TOKEN: ${{ secrets.COPILOT_GITHUB_TOKEN }}
```

- `copilot-install`を使ってインストール。pathは`copilot`のまま追加の設定不要で使える。
- `COPILOT_GITHUB_TOKEN`で認証。`GH_TOKEN`や`GITHUB_TOKEN`などCopilot CLIがサポートしている認証方法ならなんでもいい。

## `gh`コマンドを使う場合

GitHub CLI 2.86.0 でcopilotのインストール機能が追加。普通のGitHub Actions環境では`gh`コマンドがプリインストールされているので`gh copilot`でインストールできる。

```yaml
      - name: Install Copilot CLI
        run: |
          gh --version
          gh copilot
          gh copilot -- --version

      - name: Run command
        run: php artisan copilot:ping
        env:
          COPILOT_GITHUB_TOKEN: ${{ secrets.COPILOT_GITHUB_TOKEN }}
```

現時点ではGitHub Actions環境が更新されていなくて2.86.0ではないので後で試して修正。
