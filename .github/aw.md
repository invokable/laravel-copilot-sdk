# GitHub Agentic Workflows

## awがバージョンアップした時の作業

PhpStormなら「>>」で直接実行。

```shell
# プロジェクトルートで実行
cd ../
# awを更新
gh extension upgrade gh-aw
# workflowを更新
gh aw upgrade --pre-releases
# upgradeで更新されないファイルを更新
gh aw compile
```

```shell
cd ../ && gh extension upgrade gh-aw && gh aw upgrade --pre-releases && gh aw compile
```
