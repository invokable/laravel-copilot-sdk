# 公式 GitHub Copilot SDK v1.0.0 GA 直前同期計画

## 目的

公式 SDK v1.0.0-beta.8 のリリースノートと破壊的変更ガイドを基準に、Laravel 版が v1.0.0 GA 前に同期すべき項目を整理する。既存の Laravel らしい使い方、特に `Copilot::run()`、`Copilot::start()`、`Copilot::useStdio()`、`Copilot::useTcp()` は壊さず、内部構造とワイヤー形状を公式 SDK の GA 形に寄せる。

参照:

- https://github.com/github/copilot-sdk/releases/tag/v1.0.0-beta.8
- https://gist.github.com/SteveSandersonMS/2f979b5aff967abc776137c5fefd5e70
- `docs/develop/official-sdk-v1-strategy.md`

## 前提

- `preMcpToolCall` は修正済み。GA 前には regression test と docs の確認だけ行う。
- Canvas は昨日の同期エージェントで Laravel 版にも追加済み。ただし公式 beta.8 時点でも「experimental, GitHub Copilot App only」なので、Laravel 版 v1.0 の主要 public API としては推さない。
- `send()` / `sendAndWait()` の文字列オーバーロードは Laravel 版では最初から名前付き引数中心なので、追加対応は不要。
- PermissionRequest 周りは Laravel 版独自の安全側設計を維持する。Facade 経由のデフォルトは引き続き全拒否。
- `ElicitationRequest` は非推奨後の互換クラスとして残っているだけなので、v1.0 前に削除する。

## beta.8 破壊的変更の Laravel 版判断

| 公式変更 | Laravel 版の現状 | GA 前の判断 |
| --- | --- | --- |
| Client connection config -> `RuntimeConnection` | `CopilotManager` は `url` の有無で TCP 接続/stdio 起動を分岐し、`Client` は `cli_url` と `ProcessManager` 用の flat options を受ける。`useStdio()` / `useTcp()` は既にある。 | ユーザー向け API は変えず、内部だけ `RuntimeConnection` 相当に寄せる。現在の `useTcp()` は「既存 runtime への URI 接続」として扱い、公式の `forTcp()` 相当の「SDK が TCP runtime を起動する」機能は v1.0 GA ブロッカーにしない。 |
| `copilotHome` -> `baseDirectory` | config は `copilot_home`、内部は `copilotHome`、実際には `COPILOT_HOME` env を設定。 | wire/内部名は `baseDirectory` に寄せるが、Laravel config の `copilot_home` は残す。必要なら `base_directory` を alias として受け、docs は既存の `copilot_home` を維持する。 |
| `getMessages()` -> `getEvents()` | `CopilotSession::getMessages()` が `session.getMessages` RPC を呼び、docs にも掲載。 | `getEvents()` を追加して公式名に揃える。`getMessages()` は deprecated alias にするか、v1.0 前に削除するかを実装時に決める。公式 RPC 名はまだ `session.getMessages` なので、内部 RPC はそのまま。 |
| Permission results -> generated `PermissionDecision` | `PermissionRequestResultKind` helper と配列 result、`PermissionDecisionRequest` RPC type、`PermissionDecisionKind` enum が混在。Facade は default deny-all。Direct `Client` は handler 必須。 | 公式に近い `PermissionDecision` factory/helper を追加し、`PermissionRequestResultKind` は deprecated alias にする。default deny-all と explicit handler 方針は Laravel 独自として維持する。 |
| Handler rename: `onExitPlanMode` -> `onExitPlanModeRequest`, `onAutoModeSwitch` -> `onAutoModeSwitchRequest` | `SessionConfig` / `ResumeSessionConfig` は旧名。wire flags は handler presence から生成。 | 新名を追加し、旧名は alias/deprecated として読む。wire flags と register は新名優先、旧名は互換 fallback。docs は新名に更新。 |
| `maxInputTokens` -> `maxPromptTokens` | `ProviderConfig` の public property は `maxInputTokens`、`fromArray()` は両方読み、`toArray()` は `maxPromptTokens` を出す。 | `maxPromptTokens` を primary にし、`maxInputTokens` は互換 alias にする。docs は `maxPromptTokens` へ更新。 |
| `streaming` tri-state | `?bool` で実装済み。 | 現状維持。null/false/true の round-trip test を確認する。 |
| Handler-derived wire flags | Permission は handler 必須のため `requestPermission => true` 固定。他の handler flags と hooks は presence 由来。 | Permission default deny-all 注入後の handler presence から `requestPermission` を決める形に整理する。ただし Facade 経由では常に deny-all handler が入るため安全側の挙動は変えない。 |
| Protocol v2 dropped / min protocol 3 | `Protocol::version()` は 3。 | 現状維持。v2 fallback が残っていないことだけ確認。 |
| `ConnectionState` / `getState()` removed | `Client::getState()` と `ConnectionState` enum は public method/class として存在。ただし `CopilotClient` interface にはない。 | public docs に出していなければ v1.0 前に削除候補。互換を優先するなら deprecated internal API と明記。 |
| `destroy()` -> `disconnect()` | `disconnect()` が主、`destroy()` は deprecated trait と fake に残る。 | v1.0 前に削除するか、少なくとも docs から完全に消す。公式 GA 形に寄せるなら削除が自然。 |
| MCP server `tools` tri-state | `McpServerValue::$tools` は nullable、`array_filter(fn => value !== null)` なので `null` は省略、空配列は送れる。 | 公式 semantics に合っているか test を追加する。docs に「null/未指定=全 tools、空配列=tools なし」を明記。 |
| `DisableResume` -> `SuppressResumeEvent` | `ResumeSessionConfig::$disableResume` と wire key `disableResume` が残る。 | `suppressResumeEvent` に rename。`disableResume` は alias/deprecated として入力だけ受け、wire key は `suppressResumeEvent` に変更。 |
| `InputOptions` -> `UiInputOptions` | `Types\InputOptions` が `input()` helper の options として使われている。 | `UiInputOptions` を追加し、`InputOptions` は alias/deprecated。docs と signatures は新名へ移行。 |
| Generated RPC type renames | `Types/Rpc` に古い名前と新しい名前が混在する可能性がある。 | beta.8 の generated `rpc.ts` / Python generated RPC と照合し、public helper と衝突する型名だけ優先して揃える。全 RPC 型の機械的 rename は GA tag 後に再実行する。 |

## 実装フェーズ

### Phase 1: GA 互換の名前・wire 形状を先に揃える

1. `RuntimeConnection` 内部モデルを追加する。
   - 候補: `src/Types/RuntimeConnection.php` と `src/Enums/RuntimeConnectionKind.php`。
   - `stdio`, `uri`, 将来用 `tcp` の 3 kind を表現する。
   - `CopilotManager::useStdio()` は `stdio`、現在の `useTcp()` は `uri` に正規化する。
   - `Client` constructor は旧 flat options と新 `connection` の両方を受け、最初に正規化する。
   - `ProcessManager` には normalized stdio options だけを渡す。

2. `baseDirectory` へ内部 rename する。
   - `copilot_home` config は残す。
   - `base_directory` / `baseDirectory` も入力 alias として受ける。
   - `ProcessManager` の property は `baseDirectory` に変更し、引き続き `COPILOT_HOME` env を設定する。

3. Resume config の rename を行う。
   - `ResumeSessionConfig::$suppressResumeEvent` を追加。
   - `fromArray()` は `suppressResumeEvent` 優先、なければ `disableResume` を読む。
   - `toArray()` と `Client::resumeSession()` は `suppressResumeEvent` を送る。
   - `disableResume` は deprecated alias にするか削除する。

4. Handler rename を追加する。
   - `SessionConfig` / `ResumeSessionConfig` に `onExitPlanModeRequest` と `onAutoModeSwitchRequest` を追加。
   - `fromArray()` は新名優先、旧名 fallback。
   - `Client` は新名優先で handler registration と `requestExitPlanMode` / `requestAutoModeSwitch` flags を生成する。
   - docs は新名に変更する。

5. `ProviderConfig` を `maxPromptTokens` primary にする。
   - constructor に `maxPromptTokens` を追加するか、breaking を許容して `maxInputTokens` を置き換える。
   - v1.0 前なので置き換えが自然だが、Laravel 利用者の混乱を避けるなら `fromArray()` alias は残す。
   - `toArray()` は引き続き `maxPromptTokens` のみ。

### Phase 2: PermissionRequest / PermissionDecision を整理する

1. 公式に近い `PermissionDecision` factory/helper を作る。
   - 例: `PermissionDecision::approveOnce()`, `reject(?string $feedback = null)`, `userNotAvailable()`, `noResult()`, `approveForSession()`, `approveForLocation()`, `approvePermanently(string $domain)`。
   - 戻り値は既存 handler と同じ array shape にして、Laravel の Closure API を壊さない。

2. 既存 helper を整理する。
   - `PermissionRequestResultKind` は deprecated alias とする。
   - `PermissionHandler::approveAll()`, `approveSafety()`, `denyAll()` は `PermissionDecision` を使う。
   - `PermissionDecisionKind` enum と factory の責務を明確化する。enum は generated/wire discriminator、factory は user-facing helper。

3. RPC type 名の衝突を直す。
   - 現在の `Types\Rpc\PermissionRequestResult` は `handlePendingPermissionRequest` RPC の result (`success`) を表しており、公式の handler result 名と紛らわしい。
   - generated beta.8 の名前に合わせて `PermissionsHandlePendingPermissionRequestResult` などへ rename するか、GA tag 後の generated type sync でまとめて直す。

4. デフォルト挙動は変えない。
   - `CopilotManager::ensurePermissionHandler()` は default `deny-all` のまま。
   - Direct `Client` で handler 必須を続けるかは再判断対象。ただし「handler なしで pending のまま」は Laravel Facade の default にはしない。
   - `requestPermission` は「登録された handler があるか」で決める。Facade 経由では deny-all handler が入るので引き続き拒否応答される。

### Phase 3: deprecated / 互換クラスを v1.0 前に整理する

1. `ElicitationRequest` を削除する。
   - 削除対象: `src/Types/ElicitationRequest.php`, `tests/Unit/Types/ElicitationRequestTest.php`。
   - `ElicitationContext`、`UIElicitationRequest`、`UIHandlePendingElicitationRequest` は残す。
   - docs に `ElicitationRequest` が出ていないことを確認する。

2. `getEvents()` を追加し、`getMessages()` を整理する。
   - `CopilotSession::getEvents()` と `Session::getEvents()` を追加。
   - `FakeSession` も合わせる。
   - `getMessages()` は deprecated alias にするか、v1.0 前 breaking として削除する。
   - `docs/jp/resume.md` の例は `getEvents()` に更新する。

3. `destroy()` を削除または最終 deprecation にする。
   - `disconnect()` は既に主 API。
   - `HasDeprecated` trait、`FakeSession::destroy()`、interface の `destroy()` を削除するなら v1.0 前が最後のタイミング。

4. `ConnectionState` / `getState()` を public API から外す。
   - `CopilotClient` interface にはないため削除しやすい。
   - tests/docs で使っていなければ削除候補。
   - 内部 state は string/private enum でもよいが、Laravel 版では今の enum を internal として残す選択も可能。

5. `InputOptions` を `UiInputOptions` へ移す。
   - `CopilotSession::input()` signature と docs を新名にする。
   - `InputOptions` は deprecated alias にするか削除する。

### Phase 4: beta.8 新機能の扱いを明文化する

1. Canvas は experimental のまま据え置く。
   - `SessionConfig::$canvases`, `requestCanvasRenderer`, `requestExtensions`, `extensionInfo` は維持。
   - GitHub Copilot App 専用の実験的機能として、README や主要 docs では推さない。
   - 実装が存在することを developer doc に留める。

2. `preMcpToolCall` は regression test のみ。
   - hooks dispatch map、type round-trip、実際の `hooks.invoke` handling を確認する。
   - 既に修正済みなので同期計画上は完了扱い。

3. Remote sessions / cloud sessions は現状維持しつつ naming を確認する。
   - client-level `remote` は公式の `enableRemoteSessions` に相当。
   - user-facing `remote` config は `useStdio(['remote' => true])` のまま維持する。
   - 内部名は `enableRemoteSessions` へ寄せる余地がある。

4. SessionFs provider adapter は GA ブロッカーにしない。
   - 公式は provider method names を `ReadDirectory`, `MakeDirectory`, `Remove` 方向へ整理。
   - Laravel 版で adapter を実装するなら、最初から beta.8 名に合わせる。
   - v1.0 では RPC type が存在するだけなら「provider adapter 未対応」と明記する。

## 検証方針

- `composer run test`
- `composer run lint`
- 変更範囲に応じて個別に `vendor/bin/pest --filter=...`
- 実 runtime 確認が必要な変更では以下も実行する。
  - `vendor/bin/testbench copilot:ping`
  - `vendor/bin/testbench copilot:version`

## GA 前の優先順位

| Priority | 項目 | 理由 |
| --- | --- | --- |
| High | `RuntimeConnection` 内部導入 | 公式 beta.8 最大の構造変更。Laravel 版の外部 API を守りながら内部を整理できる。 |
| High | `PermissionDecision` 整理 | 公式が Laravel 独自設計に近づいたため、今の安全 default を保ったまま名前と helper を揃える価値が高い。 |
| High | `ElicitationRequest` 削除 | v1.0 前に deprecated 互換クラスを消す最後の機会。 |
| High | `suppressResumeEvent` rename | wire key が変わるため runtime 互換に直結する。 |
| Medium | Handler rename | public config 名の公式追従。旧名 fallback を残せる。 |
| Medium | `getEvents()` 追加 | 公式名とのずれを減らす。RPC method 名はまだ `session.getMessages`。 |
| Medium | `maxPromptTokens` rename | BYOK provider docs/API の公式追従。 |
| Low | Canvas docs | experimental なので実装維持と注記だけで十分。 |
| Low | SessionFs adapter | 公式と揃えるなら大きめの実装になるため GA 後でもよい。 |

## 完了条件

- 公式 beta.8 の all-language breaking changes に対して、Laravel 版で「実装する」「互換 alias にする」「Laravel 独自として残す」「v1.0 外に送る」の判断がコードと docs に反映されている。
- ユーザー向けの `Copilot::useStdio()` / `Copilot::useTcp()`、`Copilot::run()` / `Copilot::start()` の使い方は変わらない。
- Facade 経由の permission default は引き続き deny-all。
- `ElicitationRequest` の class/test/import が残っていない。
- Canvas は実験的かつ GitHub Copilot App 専用として扱い、Laravel 版 v1.0 の必須機能にしない。
