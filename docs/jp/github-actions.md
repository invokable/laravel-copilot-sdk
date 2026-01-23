# GitHub Actionsでの使い方

GitHub ActionsでArtisanコマンドを実行してその中でCopilot CLIを使う例。

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
