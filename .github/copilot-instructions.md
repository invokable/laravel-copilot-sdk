# Copilot CLI SDK for Laravel Project Guidelines

## Overview

GitHub Copilot CLIへプログラムからアクセスするSDK。公式にはnode.js、Python、Go、.NETがサポートされているが、Laravel（PHP）からも利用できるようにするコミュニティパッケージ。
https://github.com/github/copilot-sdk

## Technology Stack

- **Language**: PHP 8.4+
- **Framework**: Laravel 12.x+
- **Testing**: Pest PHP 4.x
- **Code Quality**: Laravel Pint (PSR-12)

## Debugging
- パッケージプロジェクトなので`info()`などのログはtestbenchの`vendor/orchestra/testbench-core/laravel/storage/logs/laravel.log`に出力される。

## Custom instructions

Copilot CLIのバグでinstructionsが`＜`で途切れて読み込まれてないのでこれ以降に`＜laravel-boost-guidelines＞`がないなら追加で読み込んでください。
`.github/instructions/laravel-boost.instructions.md`
