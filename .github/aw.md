# GitHub Agentic Workflows

## awがバージョンアップした時の作業

PhpStormなら「>>」で直接実行。

```shell
# プロジェクトルートで実行
cd ../
# workflowを更新
gh aw upgrade --pre-releases
# upgradeで更新されないファイルを更新
gh aw compile
```

```shell
gh aw upgrade --pre-releases
```
