# Git・GitHub

対象: ServerRpc（Client に紐づくサーバー全体の操作）

RPC 全体の概要とページ一覧は [RPC](../rpc.md) を参照してください。

## gitHubRepository

`gitHubRepository()->getById()`はGitHubリポジトリIDからowner/nameを解決し、`lookupBranchPullRequest()`は指定したhead ownerとブランチを、優先順に指定したリポジトリで検索します。どちらも`authInfo`をそのまま渡します。`getById()`の結果は`repository`にowner/nameを含み、見つからない場合は`null`です。Pull Request検索の`status`は`found`、`no-token`、`no-pr`、`lookup-failed`のいずれかで、未知の値は文字列のまま保持されるため例外になりません。`pullRequest`は見つかった場合の連想配列で、それ以外は`null`です。

```php
use Revolution\Copilot\Facades\Copilot;

$repository = Copilot::client()->rpc()->gitHubRepository();

$resolved = $repository->getById([
    'authInfo' => $authInfo,
    'repositoryId' => 12345,
]);

$lookup = $repository->lookupBranchPullRequest([
    'authInfo' => $authInfo,
    'headOwner' => 'octo',
    'baseRepositories' => [
        ['owner' => 'octo', 'name' => 'demo'],
        ['owner' => 'upstream', 'name' => 'demo'],
    ],
    'branch' => 'feature',
    'includeClosed' => false,
]);
```
