# Copilot CLI SDK for Laravel Project Guidelines

## Overview

GitHub Copilot CLIへプログラムからアクセスするSDK。公式にはnode.js、Python、Go、.NETがサポートされているが、Laravel（PHP）からも利用できるようにするコミュニティパッケージ。

## 公式 SDK
https://github.com/github/copilot-sdk

`./copilot-sdk/`内はgit submoduleで追加した公式SDKのコード。
公式SDKのコードを確認する時は以下のコマンドで最新に更新してください。
```shell
git submodule update --remote --merge
```

## Technology Stack

- **Language**: PHP 8.4+
- **Framework**: Laravel 12.x+
- **Testing**: Pest PHP 4.x
- **Code Quality**: Laravel Pint (PSR-12)

## Commands
```shell
composer run test        # Run tests with Pest
composer run lint        # Run Laravel Pint for code style checks
```

実際にCopilot CLIを起動する動作確認コマンド。
```shell
vendor/bin/testbench copilot:ping
```

## Debugging
- パッケージプロジェクトなので`info()`などのログはtestbenchの`vendor/orchestra/testbench-core/laravel/storage/logs/laravel.log`に出力される。

## 実装予定なしの機能
- TCP接続。stdioの方がいいのでTCPは非対応。

## Custom instructions

Copilot CLIのバグでinstructionsが`＜`で途切れて読み込まれてないのでこれ以降に`＜laravel-boost-guidelines＞`がないなら追加で読み込んでください。
`.github/instructions/laravel-boost.instructions.md`
