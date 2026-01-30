# User Input Requests

`onUserInputRequest` ハンドラーを設定することで、エージェントが `ask_user` ツールを使用してユーザーに質問できるようになります。

```php
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\Contracts\CopilotSession;
use Revolution\Copilot\Types\UserInputRequest;
use Revolution\Copilot\Types\UserInputResponse;

Copilot::start(function (CopilotSession $session) {
    $response = $session->sendAndWait(prompt: 'ユーザーに好きな言語を聞いて');
    dump($response->content());
}, config: [
    'model' => 'gpt-5',
    'onUserInputRequest' => function (UserInputRequest $request): UserInputResponse {
        // $request->question - 質問内容
        // $request->choices - 複数選択肢の配列（オプション）
        // $request->allowFreeform - 自由入力が許可されているか（デフォルト: true）

        dump("エージェントからの質問: {$request->question}");
        if ($request->choices) {
            dump('選択肢: '.implode(', ', $request->choices));
        }

        // ユーザーの回答を返す
        return new UserInputResponse(
            answer: 'PHP',
            wasFreeform: true, // 選択肢からではなく自由入力だったか
        );
    },
]);
```

## UserInputRequest

エージェントからのユーザー入力リクエスト。

| プロパティ           | 型        | 説明                         |
|-----------------|----------|----------------------------|
| `question`      | `string` | ユーザーに尋ねる質問                 |
| `choices`       | `?array` | 複数選択の場合の選択肢（オプション）         |
| `allowFreeform` | `?bool`  | 自由入力が許可されているか（デフォルト: true） |

## UserInputResponse

ユーザー入力リクエストへの応答。

| プロパティ         | 型        | 説明                            |
|---------------|----------|-------------------------------|
| `answer`      | `string` | ユーザーの回答                       |
| `wasFreeform` | `bool`   | 回答が自由入力だったか（選択肢からではない場合 true） |

## 実際の使用例

インタラクティブなコマンドで使う場合：

```php
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\Contracts\CopilotSession;
use Revolution\Copilot\Types\UserInputRequest;
use Revolution\Copilot\Types\UserInputResponse;

// Artisan コマンド内で
Copilot::start(function (CopilotSession $session) use ($command) {
    $response = $session->sendAndWait(prompt: 'プロジェクトの設定を行います');
    $command->info($response->content());
}, config: [
    'onUserInputRequest' => function (UserInputRequest $request) use ($command): UserInputResponse {
        if ($request->choices) {
            // 選択肢がある場合は choice メソッドを使用
            $answer = $command->choice(
                $request->question,
                $request->choices,
                $request->choices[0] ?? null
            );
            return new UserInputResponse(answer: $answer, wasFreeform: false);
        }

        // 自由入力
        $answer = $command->ask($request->question);
        return new UserInputResponse(answer: $answer, wasFreeform: true);
    },
]);
```
