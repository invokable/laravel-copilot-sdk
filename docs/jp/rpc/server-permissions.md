# Server Permissions

対象: ServerRpc（Client に紐づくサーバー全体の操作）

RPC 全体の概要とページ一覧は [RPC](../rpc.md) を参照してください。

## Extension approvals（experimental）

`permissions()->extensionApprovals()`は、インストール済みプラグインの拡張機能に対するユーザー単位の承認を保存・一覧・取り消しします。このAPIは実験的なため、将来変更・削除される可能性があります。保存された承認は、後続の要求のcapabilitiesが承認済みcapabilitiesの部分集合である場合に適用されます。

### 保留中の要求を承認

`grant()`には、保留中の拡張機能の権限要求を所有するセッションのIDと、その要求のIDを指定します。ホストが要求を通常どおり処理する**前に**呼び出してください。`granted`は要求が対象となり承認が保存されたかを示し、成功時の`approval`には保存された承認が入ります。

`grant()`は承認を保存するだけで、保留中の要求への応答は行いません。ホストはその後も通常の方法で要求に応答する必要があります。応答方法は [Session Tools・Permissions・Commands・UI](session-tools.md) を参照してください。

```php
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\Types\Rpc\PermissionsExtensionApprovalsGrantParams;

// permissions.extensionApprovals.grant
// $sessionId と $requestId は保留中の拡張機能の権限要求から取得する
$approvals = Copilot::client()->rpc()->permissions()->extensionApprovals();
$result = $approvals->grant(new PermissionsExtensionApprovalsGrantParams(
    sessionId: $sessionId,
    requestId: $requestId,
));

// $result->granted, $result->approval
```

### 一覧と取り消し

`list()`と`revoke()`の`sessionId`を省略または`null`にするとサーバーの設定プロファイルを対象にし、接続済みセッションのIDを指定するとそのセッションの設定プロファイルを対象にします。これは承認を特定のセッションだけに限定する指定ではありません。

```php
use Revolution\Copilot\Types\Rpc\PermissionsExtensionApprovalsListParams;
use Revolution\Copilot\Types\Rpc\PermissionsExtensionApprovalsRevokeParams;

// permissions.extensionApprovals.list
$serverApprovals = $approvals->list();
$sessionApprovals = $approvals->list(new PermissionsExtensionApprovalsListParams(
    sessionId: $sessionId,
));

foreach ($sessionApprovals->approvals as $approval) {
    // $approval->extensionName, $approval->pluginName, $approval->capabilities
    // $approval->grantedAt, $approval->installationKey, $approval->installationLabel
}
```

取り消す承認をユーザーが明示的に選択した場合、その一覧または`grant()`結果の`approval`を使い、同じ設定プロファイルを指定して取り消します。

```php
// permissions.extensionApprovals.revoke
// $selectedApproval はユーザーが明示的に選択した承認
$result = $approvals->revoke(new PermissionsExtensionApprovalsRevokeParams(
    extensionName: $selectedApproval->extensionName,
    installationKey: $selectedApproval->installationKey,
    sessionId: $sessionId,
));

// $result->revoked は一致する承認が存在し、削除されたかを示す
```

`installationKey`は不透明なインストール識別子です。取り消す際は、一覧または承認結果から取得した値を変更せずに渡してください。引数は配列でも指定できます。
