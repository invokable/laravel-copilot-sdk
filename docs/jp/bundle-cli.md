# プロジェクトにCopilot CLIを含める

Copilot CLIのバージョンを厳密に管理したい場合、npmパッケージとしてプロジェクトに含めることができます。

一般的にはデプロイ時に別途Copilot CLIをインストールする方法で十分です。  
→ [Laravel Cloudでの使い方](laravel-cloud.md) / [GitHub Actionsでの使い方](github-actions.md)

## 前提条件

Laravelプロジェクトには`package.json`があり、本番サーバーにもNode.js実行環境があるのでnpmパッケージのインストールが可能です。

## セットアップ

### 1. npmパッケージのインストール

`dependencies`に`@github/copilot`をインストールします。

```shell
npm install @github/copilot
```

インストール後、以下のパスでCopilot CLIを直接実行できます。

```shell
node node_modules/@github/copilot/index.js --version
```

### 2. .envの設定

`COPILOT_CLI_PATH`に`index.js`までのフルパスを指定します。

```dotenv
COPILOT_CLI_PATH=/path/to/project/node_modules/@github/copilot/index.js
```

`base_path()`を使って動的にパスを設定したい場合は`config/copilot.php`を直接編集します。

```php
'cli_path' => base_path('node_modules/@github/copilot/index.js'),
```

## デプロイ

本番サーバーへのデプロイ時はアセットビルド後に`devDependencies`を除外して再インストールします。`@github/copilot`は`dependencies`にあるのでそのまま残ります。

```shell
npm install
npm run build
npm install --omit=dev
```

`npm install --omit=dev`により`devDependencies`のパッケージが削除され、`@github/copilot`を含む`dependencies`のパッケージのみが`node_modules`に残ります。

## メリットと注意点

| メリット | 注意点 |
|---------|--------|
| CLIのバージョンを厳密に管理できる | `node_modules`のサイズが増加する |
| `package.json`でバージョンを固定できる | CLIの更新は手動で行う必要がある |
| チーム全員が同じバージョンを使える | Node.js実行環境が必要 |

## 認証

認証方法はインストール方法に関わらず同じです。`.env`で`COPILOT_GITHUB_TOKEN`を設定します。

```dotenv
COPILOT_GITHUB_TOKEN=your-token
```

詳しくは[認証方法](auth.md)を参照してください。
