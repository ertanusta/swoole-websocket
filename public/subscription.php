<?php
declare(strict_types=1);

use OpenSwoole\WebSocket\Server;
use OpenSwoole\Http\Request;
use OpenSwoole\WebSocket\Frame;
use OpenSwoole\Table;
use OpenSwoole\Coroutine as Co;

require __DIR__ . '/../vendor/autoload.php';
Co::set(['hook_flags' => OpenSwoole\Runtime::HOOK_ALL]);

$server = new Server("0.0.0.0", 9502);

$server->on("Start", function (Server $server) {
    echo "OpenSwoole WebSocket Server is started at http://127.0.0.1:9502\n";
});

$server->on('Open', function (Server $server, Request $request) {
    echo "connection open: {$request->fd}\n";
});
$redis = new Redis(['host' => '127.0.0.1', 'port' => 6379, 'readTimeout' => 0, 'connectTimeout' => 0, 'persistent' => true]);
$redis->setOption(Redis::OPT_READ_TIMEOUT,-1);
$server->on('Message', function (Server $server, Frame $frame) use ($redis) {
    echo "received message: {$frame->data}\n";
    $channel = $frame->data;
    $redis->subscribe([$channel], function ($redis, $channel, $message) use ($server, $frame) {
        $server->push($frame->fd, $message);
    });
});

$server->on('Close', function (Server $server, int $fd) {
    echo "connection close: {$fd}\n";

});

$server->on('Disconnect', function (Server $server, int $fd) {
    echo "connection disconnect: {$fd}\n";
});

$server->start();
