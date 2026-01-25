# TCPモードへの対応

最初の計画でstdioのみ対応にしてTCPは非対応の予定だったけどstdio部分をStdioTransportクラス`src/Transport/StdioTransport.php`に分離したのでTCP用もTcpTransportかHttpTransportクラスを作って切り替えられるようにすれば対応できそう。

Laravel ForgeやLaravel Cloudはバックグランドプロセスの管理がしやすいのでartisanコマンドで`copilot --server --port xxxxx`を起動したままにしておき、SDKから接続する使い方ができる。

LaravelはServer-Sent Eventsにも対応してるのでストリーミングも可能。

TCPの場合のClientは
- ProcessManagerは起動しない。
- TcpTransportを指定してJsonRpcClientを作成。
- あとはJsonRpcClient側での処理。

JsonRpcClientは元々stdioだけのつもりだったのでStdioTransportを分離したとはいえまだ修正が必要なはず。

Laravel MCPのJsonRpcサーバー機能がStdioTransportとHttpTransportで切り替えているので参考にする。
`vendor/laravel/mcp/src/Server/Transport/StdioTransport.php`
`vendor/laravel/mcp/src/Server/Transport/HttpTransport.php`

`vendor/laravel/mcp/src/Server.php`
`vendor/laravel/mcp/src/Server/Registrar.php`
