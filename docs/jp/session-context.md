# セッションコンテキストとフィルタリング

GitHub Copilot SDK v0.1.24以降では、セッションに作業ディレクトリとGit情報を含むコンテキストが追加され、セッション一覧でフィルタリングできるようになりました。

## SessionContext

`SessionContext`は、セッションが作成された時の作業ディレクトリとGitリポジトリの情報を保持します。

### プロパティ

- `cwd` (string): 作業ディレクトリの絶対パス
- `gitRoot` (?string): Gitリポジトリのルートディレクトリ（Gitリポジトリ内でない場合はnull）
- `repository` (?string): GitHubリポジトリ（`owner/repo`形式、例: `invokable/laravel-copilot-sdk`）
- `branch` (?string): 現在のGitブランチ名

### 使用例

```php
use Revolution\Copilot\Facades\Copilot;

// セッション一覧を取得
$sessions = Copilot::client()->listSessions();

foreach ($sessions as $metadata) {
    echo "Session: {$metadata->sessionId}\n";
    
    if ($metadata->context !== null) {
        echo "  作業ディレクトリ: {$metadata->context->cwd}\n";
        
        if ($metadata->context->repository !== null) {
            echo "  リポジトリ: {$metadata->context->repository}\n";
            echo "  ブランチ: {$metadata->context->branch}\n";
        }
    }
}
```

## SessionListFilter

`SessionListFilter`を使用して、特定の作業ディレクトリやリポジトリのセッションのみを取得できます。

### プロパティ

- `cwd` (?string): 作業ディレクトリで完全一致フィルタ
- `gitRoot` (?string): Gitルートディレクトリでフィルタ
- `repository` (?string): リポジトリ（`owner/repo`形式）でフィルタ
- `branch` (?string): ブランチ名でフィルタ

### 使用例

#### 配列でフィルタ指定

```php
use Revolution\Copilot\Facades\Copilot;

// 特定のリポジトリのセッションのみを取得
$sessions = Copilot::client()->listSessions([
    'repository' => 'invokable/laravel-copilot-sdk',
]);

// 特定のブランチで作業中のセッションを取得
$sessions = Copilot::client()->listSessions([
    'repository' => 'owner/repo',
    'branch' => 'feature/new-feature',
]);

// 特定の作業ディレクトリのセッションを取得
$sessions = Copilot::client()->listSessions([
    'cwd' => '/home/user/projects/my-app',
]);
```

#### SessionListFilterクラスを使用

```php
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\Types\SessionListFilter;

$filter = new SessionListFilter(
    repository: 'owner/repo',
    branch: 'main',
);

$sessions = Copilot::client()->listSessions($filter);
```

## session.context_changed イベント

セッション中に作業ディレクトリが変更されると、`session.context_changed`イベントが発火します。

### イベントタイプ

```php
use Revolution\Copilot\Enums\SessionEventType;

SessionEventType::SESSION_CONTEXT_CHANGED; // 'session.context_changed'
```

### イベントデータ

イベントデータには更新されたコンテキスト情報が含まれます：

```php
use Revolution\Copilot\Facades\Copilot;

Copilot::start(function ($session) {
    $session->on(function ($event) {
        if ($event->type === 'session.context_changed') {
            $data = $event->data;
            
            echo "作業ディレクトリが変更されました\n";
            echo "  cwd: {$data['cwd']}\n";
            echo "  repository: {$data['repository']}\n";
            echo "  branch: {$data['branch']}\n";
        }
    });
    
    $response = $session->sendAndWait(
        prompt: 'Change to a different directory and list files',
    );
});
```

## SessionMetadata

`SessionMetadata`に`context`プロパティが追加されました。

### 新しいプロパティ

- `context` (?SessionContext): セッションの作業ディレクトリとGit情報

### 使用例

```php
use Revolution\Copilot\Facades\Copilot;

$sessions = Copilot::client()->listSessions();

foreach ($sessions as $metadata) {
    echo "Session ID: {$metadata->sessionId}\n";
    echo "開始時刻: {$metadata->startTime}\n";
    
    // contextはオプショナルなので、nullチェックが必要
    if ($metadata->context !== null) {
        echo "コンテキスト:\n";
        echo "  作業ディレクトリ: {$metadata->context->cwd}\n";
        
        if ($metadata->context->repository !== null) {
            echo "  リポジトリ: {$metadata->context->repository}\n";
        }
        
        if ($metadata->context->branch !== null) {
            echo "  ブランチ: {$metadata->context->branch}\n";
        }
    }
    
    echo "\n";
}
```

## 実用例

### 特定のプロジェクトのセッションを取得

```php
use Revolution\Copilot\Facades\Copilot;

// Laravel Copilot SDKプロジェクトのセッションのみを取得
$sessions = Copilot::client()->listSessions([
    'repository' => 'invokable/laravel-copilot-sdk',
]);

// 作業中のセッションを表示
foreach ($sessions as $metadata) {
    echo "{$metadata->sessionId}: {$metadata->summary}\n";
    echo "  ブランチ: {$metadata->context->branch}\n";
}
```

### 機能ブランチのセッションを管理

```php
use Revolution\Copilot\Facades\Copilot;

// feature/* ブランチで作業中のセッションを取得（簡易的な例）
$sessions = Copilot::client()->listSessions();

$featureSessions = array_filter($sessions, function ($metadata) {
    return $metadata->context !== null 
        && str_starts_with($metadata->context->branch ?? '', 'feature/');
});

// 機能ブランチごとにセッションをグループ化
$byBranch = [];
foreach ($featureSessions as $metadata) {
    $branch = $metadata->context->branch;
    $byBranch[$branch][] = $metadata;
}

foreach ($byBranch as $branch => $sessions) {
    echo "{$branch}: " . count($sessions) . " セッション\n";
}
```

### 作業ディレクトリごとにセッションを整理

```php
use Revolution\Copilot\Facades\Copilot;

$sessions = Copilot::client()->listSessions();

// 作業ディレクトリでグループ化
$byCwd = [];
foreach ($sessions as $metadata) {
    if ($metadata->context !== null) {
        $cwd = $metadata->context->cwd;
        $byCwd[$cwd][] = $metadata;
    }
}

// ディレクトリごとのセッション数を表示
foreach ($byCwd as $cwd => $sessions) {
    echo "{$cwd}: " . count($sessions) . " セッション\n";
}
```

## 注意事項

- `context`は、セッションがGitリポジトリ内で作成された場合にのみGit関連情報（`gitRoot`, `repository`, `branch`）を含みます。
- `context`フィールドは、Copilot CLI v0.0.409以降で利用可能です。古いバージョンでは`null`が返されます。
- フィルタリングは完全一致で動作します。部分一致やワイルドカードはサポートされていません。
- `SessionListFilter`のすべてのフィールドはオプショナルです。フィルタを指定しない場合は、すべてのセッションが返されます。

## 公式SDKとの互換性

この機能は、公式GitHub Copilot SDK PR #427で追加されたものと同等です：
- Node.js SDK
- Python SDK
- Go SDK
- .NET SDK

すべてのSDKで同じAPIと動作を提供しています。
