# Skills

Skills は再利用可能なプロンプトモジュールです。`SKILL.md` を読み込んで、特定ドメイン向けの指示をセッションへ追加できます。

## 概要

- ドメイン知識をスキルとして再利用できる
- プロジェクト間で同じ振る舞いを共有できる
- セッション単位で有効/無効を切り替えられる

Laravel版では `SessionConfig` の `skillDirectories` と `disabledSkills` を使って設定します。

## 基本的な使い方

`skillDirectories` に親ディレクトリを指定すると、配下の各サブディレクトリにある `SKILL.md` が読み込まれます。

```php
use Revolution\Copilot\Contracts\CopilotSession;
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\Types\SessionConfig;

Copilot::start(function (CopilotSession $session) {
    $response = $session->sendAndWait(prompt: 'この差分をセキュリティ観点でレビューして');

    dump($response->content());
}, config: new SessionConfig(
    skillDirectories: [
        base_path('skills'),
    ],
));
```

配列形式でも指定できます。

```php
Copilot::run(
    prompt: 'READMEの改善案を出して',
    config: [
        'skillDirectories' => [base_path('skills')],
    ],
);
```

## スキルを無効化する

`disabledSkills` を使うと、ロード済みスキルの一部だけ無効化できます。

```php
$config = new SessionConfig(
    skillDirectories: [base_path('skills')],
    disabledSkills: ['experimental-feature', 'deprecated-tool'],
);
```

## ディレクトリ構成

```text
skills/
├── code-review/
│   └── SKILL.md
└── documentation/
    └── SKILL.md
```

`skillDirectories` には `skills/` のような親ディレクトリを指定します。

## SKILL.md の形式

`SKILL.md` は Markdown で、YAML frontmatter を任意で付けられます。

```markdown
---
name: code-review
description: Specialized code review capabilities
---

# Code Review Guidelines

When reviewing code, always check for:

1. Security vulnerabilities
2. Performance issues
3. Code style
4. Test coverage
```

frontmatter の主な項目:

- `name`: スキル識別子（`disabledSkills` で指定する名前）
- `description`: スキルの短い説明

`name` を省略した場合はディレクトリ名が使われます。

## Custom Agents と組み合わせる

`customAgents[].skills` にスキル名を指定すると、対象エージェント起動時にそのスキル内容が事前に読み込まれます。

```php
use Revolution\Copilot\Types\SessionConfig;

$config = new SessionConfig(
    skillDirectories: [base_path('skills/security')],
    customAgents: [
        [
            'name' => 'security-auditor',
            'description' => 'Security-focused code reviewer',
            'prompt' => 'Focus on OWASP Top 10 vulnerabilities',
            'skills' => ['security-scan', 'dependency-check'],
        ],
    ],
);
```

> `skills` を省略した場合、エージェントにスキル内容は注入されません。

## RPCでの確認・操作

スキルの検出や有効化/無効化は RPC からも操作できます。

```php
// サーバーレベルでスキルを検出
$result = Copilot::client()->rpc()->skills()->discover();

// セッション内スキル一覧
$session->rpc()->skills()->list();

// セッション内で個別に有効/無効を切り替え
$session->rpc()->skills()->enable(['name' => 'security-scan']);
$session->rpc()->skills()->disable(['name' => 'security-scan']);
```

## ベストプラクティス

- スキルはドメインごとに分割する（`skills/security`, `skills/testing` など）
- frontmatter に `name` と `description` を付ける
- 競合する指示がある場合は `disabledSkills` で明示的に除外する
- まず単体で動作確認してから複数スキルを組み合わせる

## 参考

- [SessionConfig](./session-config.md)
- [Custom Agents](./custom-agents.md)
- [RPC](./rpc.md)
