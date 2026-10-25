# セッションのAIクレジット制限

`sessionLimits`を使うと、Copilotセッションの現在の会計ウィンドウに対してAIクレジットのソフト上限を設定できます。セッションの作成時と再開時のどちらにも指定できます。

## 制限を設定する

`SessionLimitsConfig`の`maxAiCredits`に上限を指定します。使用量はモデル呼び出しの完了後に確認されるため、1回の応答で設定値を超過することがあります。上限を超えた場合、ランタイムは次のモデル呼び出しをブロックします。

```php
use Revolution\Copilot\Contracts\CopilotSession;
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\Types\ResumeSessionConfig;
use Revolution\Copilot\Types\Rpc\SessionLimitsConfig;
use Revolution\Copilot\Types\SessionConfig;

Copilot::start(function (CopilotSession $session) {
    $session->sendAndWait(prompt: 'Tell me something about Laravel.');
}, config: new SessionConfig(
    sessionLimits: new SessionLimitsConfig(maxAiCredits: 30.0),
));

Copilot::client()->resumeSession(
    'existing-session-id',
    new ResumeSessionConfig(
        sessionLimits: new SessionLimitsConfig(maxAiCredits: 30.0),
    ),
);
```

`SessionConfig`と`ResumeSessionConfig`のどちらも、`SessionLimitsConfig`または配列で`sessionLimits`を指定できます。

## 制限に関するイベント

セッションイベントを監視すると、制限の変更や上限到達時のユーザー確認を処理できます。

| イベント | 発生タイミング | 主なデータ |
|---|---|---|
| `session.session_limits_changed` (`SessionEventType::SESSION_SESSION_LIMITS_CHANGED`) | 有効なセッション制限が変更された時。`sessionLimits`が`null`なら制限はありません。 | `sessionLimits.maxAiCredits`（任意） |
| `session.usage_checkpoint` (`SessionEventType::SESSION_USAGE_CHECKPOINT`) | 再開や会計のため、ランタイムが集計使用量を記録した時。 | `totalNanoAiu`、`totalPremiumRequests`（任意） |
| `session_limits_exhausted.requested` (`SessionEventType::SESSION_LIMITS_EXHAUSTED_REQUESTED`) | 上限に到達し、続行前にユーザーの判断が必要になった時。 | `requestId`、`maxAiCredits`、`usedAiCredits` |
| `session_limits_exhausted.completed` (`SessionEventType::SESSION_LIMITS_EXHAUSTED_COMPLETED`) | 上限到達時の確認が解決された時。 | `requestId`、`response.action`、`response.additionalAiCredits`（任意）、`response.maxAiCredits`（任意） |

たとえば、`SESSION_LIMITS_EXHAUSTED_REQUESTED`を受け取ったら、イベントのデータを使って確認UIを表示できます。

```php
use Revolution\Copilot\Enums\SessionEventType;
use Revolution\Copilot\Types\SessionEvent;

$session->on(function (SessionEvent $event): void {
    if ($event->is(SessionEventType::SESSION_LIMITS_EXHAUSTED_REQUESTED)) {
        $requestId = $event->data['requestId'];
        $maxAiCredits = $event->data['maxAiCredits'];
        $usedAiCredits = $event->data['usedAiCredits'];

        // $requestIdを使って確認UIを表示する
    }
});
```

## 上限到達時の確認に応答する

ユーザーの選択は`handlePendingSessionLimitsExhausted()`でランタイムに返します。`action`には`SessionLimitsExhaustedResponseAction`を指定します。

```php
use Revolution\Copilot\Enums\SessionLimitsExhaustedResponseAction;
use Revolution\Copilot\Types\Rpc\UIHandlePendingSessionLimitsExhaustedRequest;
use Revolution\Copilot\Types\Rpc\UISessionLimitsExhaustedResponse;

$session->rpc()->ui()->handlePendingSessionLimitsExhausted(
    new UIHandlePendingSessionLimitsExhaustedRequest(
        requestId: $requestId,
        response: new UISessionLimitsExhaustedResponse(
            action: SessionLimitsExhaustedResponseAction::Add,
            additionalAiCredits: 10.0,
        ),
    ),
);
```

選択できるアクションは次のとおりです。

- `Add`: 現在の上限に指定したAIクレジットを追加します。`additionalAiCredits`を指定します。
- `Set`: 上限を新しい絶対値に変更します。`maxAiCredits`を指定します。
- `Unset`: セッションの上限を解除します。
- `Cancel`: 上限を変更せず、ブロックされたモデルリクエストをキャンセルします。

`session_limits_exhausted.requested`と`session_limits_exhausted.completed`を使った上限到達時の確認フロー、および関連する応答APIは実験的機能です。将来変更または削除される可能性があります。
