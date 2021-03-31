<?php

use App\Opcode;
use App\Packet;
use App\BilibiliBarrage;
use Workerman\Connection\AsyncTcpConnection;
use Workerman\Connection\TcpConnection;
use Workerman\Timer;
use Workerman\Worker;

require_once __DIR__ . '/vendor/autoload.php';

$worker = new Worker();

$worker->onWorkerStart = function($worker) {

    $room_id = 22590309;
    $config = BilibiliBarrage::getChatConfig($room_id);

    $conn = new AsyncTcpConnection("tcp://{$config['host']}:{$config['port']}");

    $conn->onConnect = function(TcpConnection $conn) use ($room_id, $config) {
        $packet = BilibiliBarrage::getAuthenticatePacket($room_id, $config['token']);

        $result = $conn->send($packet, true);
        if (!$result) {
            Worker::safeEcho("发送认证包失败\n");
            return;
        }

        Timer::add(BilibiliBarrage::HEART_BEAT_INTERVAL, function (TcpConnection $conn) {
            $conn->send(BilibiliBarrage::getHeartBeatPacket(), true);
        }, [$conn]);
    };

    $conn->onMessage = function($conn, $data) {
        $packet = Packet::unpack($data);

        switch ($packet['opcode']) {
            case Opcode::POPULARITY_VALUE:
                Worker::safeEcho(sprintf("人气值: %d\n", Packet::parsePayload($packet['opcode'], $packet['payload'])));
                break;
            case Opcode::CMD:
                $payload = Packet::parsePayload($packet['opcode'], $packet['payload']);
                if (empty($payload)) {
                    break;
                }

                switch ($payload['cmd']) {
                    case 'INTERACT_WORD':
                        Worker::safeEcho("{$payload['data']['uname']} 进入直播间\n");
                        break;
                    case 'DANMU_MSG':
                        Worker::safeEcho("{$payload['info'][2][1]}: {$payload['info'][1]}\n");
                        break;
                    case 'SEND_GIFT':
                        Worker::safeEcho("{$payload['data']['uname']} {$payload['data']['action']} {$payload['data']['giftName']}\n");
                        break;
                    case 'COMBO_SEND':
                        Worker::safeEcho("{$payload['data']['uname']} {$payload['data']['action']} {$payload['data']['gift_name']} [combo]\n");
                        break;
                }
                break;
            case Opcode::SERVER_HEARTBEAT:
                Worker::safeEcho("加入房间成功\n");
                break;
            default:
                var_dump($packet);
                break;
        }
    };

    $conn->connect();
};

Worker::runAll();
