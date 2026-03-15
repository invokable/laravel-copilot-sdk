# GitHub Agentic Workflows

## awがバージョンアップした時の作業

PhpStormなら「>>」で直接実行。

```shell
# プロジェクトルートで実行
cd ../
# awを更新
gh extension upgrade github/gh-aw
# workflowを更新
gh aw upgrade
# upgradeで更新されないファイルを更新
gh aw compile
```

```shell
cd ../ && gh extension upgrade github/gh-aw && gh aw upgrade && gh aw compile
```

`actions-lock.json` `sdk-sync.lock.yml` `copilot-setup-steps.yml` `agentic-workflows.agent.md`などが適切に更新される。

upgradeコマンドだけで解決できない時は`agentic-workflows`カスタムエージェントを使ってCopilotで修正。
