# ローカルモデル（Azure ML）

対象: ServerRpc（Client に紐づくサーバー全体の操作）

RPC 全体の概要とページ一覧は [RPC](../rpc.md) を参照してください。

```php
use Revolution\Copilot\Enums\AzureMlRegistry;
use Revolution\Copilot\Types\Rpc\AzureMlAcquireFamilyRequest;
use Revolution\Copilot\Types\Rpc\AzureMlCancelRequest;
use Revolution\Copilot\Types\Rpc\AzureMlCatalogRequest;
use Revolution\Copilot\Types\Rpc\AzureMlDiscardRequest;
use Revolution\Copilot\Types\Rpc\AzureMlDownloadRequest;
use Revolution\Copilot\Types\Rpc\AzureMlLatestRequest;
use Revolution\Copilot\Types\Rpc\AzureMlReleaseFamilyRequest;
use Revolution\Copilot\Types\Rpc\AzureMlResolveRequest;
use Revolution\Copilot\Types\Rpc\AzureMlStatusRequest;

// Azure ML ローカルモデル管理（experimental）
$azureMl = Copilot::client()->rpc()->azureMl();
$hardware = $azureMl->hardware();
// gpuMemory は計測できない環境では null
$gpuMemory = $hardware->gpuMemory;
$catalog = $azureMl->catalog(new AzureMlCatalogRequest(includeDev: false));
$latest = $azureMl->latest(new AzureMlLatestRequest(
    registry: AzureMlRegistry::AzureMl,
    name: 'phi',
));
$resolved = $azureMl->resolve(new AzureMlResolveRequest(
    registry: AzureMlRegistry::AzureMl,
    name: $latest->name,
    version: $latest->version,
));

// requestId と leaseId はこの SDK 接続に紐づくため、別の Client では再利用しない
$lease = $azureMl->acquireFamily(new AzureMlAcquireFamilyRequest(
    registry: AzureMlRegistry::AzureMl,
    name: $latest->name,
));

try {
    $download = $azureMl->download(new AzureMlDownloadRequest(
        requestId: 'download-1',
        registry: AzureMlRegistry::AzureMl,
        name: $latest->name,
        version: $latest->version,
        destination: 'models/phi/'.$latest->version,
        leaseId: $lease->leaseId,
    ));
} finally {
    $azureMl->releaseFamily(new AzureMlReleaseFamilyRequest(
        leaseId: $lease->leaseId,
        registry: AzureMlRegistry::AzureMl,
        name: $latest->name,
    ));
}

// download() は完了まで待機する。処理中の進捗確認・キャンセルは、
// 同じ接続の $azureMl を使う別 Fiber などから実行する
// $status = $azureMl->status(new AzureMlStatusRequest(requestId: 'download-1'));
// $cancelled = $azureMl->cancel(new AzureMlCancelRequest(requestId: 'download-1'));
// cancel() の bool はキャンセル要求の受理を示し、クリーンアップ完了は download() で確認する

// 非アクティブなバージョンを削除する
$azureMl->discard(new AzureMlDiscardRequest(destination: 'models/phi/previous-version'));

// resolve() と download() の型には署名付き URL の専用フィールドはない
// download() は既定でタイムアウトなし。秒数を指定する場合は第2引数に渡す
// registration() は同じファミリーの leaseId を使い、upsert/remove/restore を実行する
// 結果の kind/path/detail と、補償用の before を確認する
```
