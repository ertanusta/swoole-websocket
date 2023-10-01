<?php
declare(strict_types=1);

use OpenSwoole\WebSocket\Server;
use OpenSwoole\Http\Request;
use OpenSwoole\WebSocket\Frame;
use OpenSwoole\Table;
use OpenSwoole\Coroutine as Co;

require __DIR__ . '/../vendor/autoload.php';

$server = new Server("0.0.0.0", 9502);

$redis = new Redis(['host' => '127.0.0.1', 'port' => 6379, 'readTimeout' => 0, 'connectTimeout' => 0, 'persistent' => true]);
$redis->setOption(Redis::OPT_READ_TIMEOUT,-1);
$server->on('Message', function (Server $server, Frame $frame) use ($redis) {
    echo "received message: {$frame->data}\n";
    $channel = $frame->data;
    $redis->subscribe([$channel], function ($redis, $channel, $message) use ($server, $frame) {
        $server->push($frame->fd, $message);
    });
});

$server->start();
