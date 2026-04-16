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
cd ../ && gh extension upgrade github/gh-aw && gh aw upgrade --approve-updates && gh aw compile --approve-updates
```
