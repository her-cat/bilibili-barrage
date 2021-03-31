<?php

namespace App;

/**
 * Bilibili 弹幕
 */
class BilibiliBarrage
{
    /**
     * 保存房间配置信息
     * @var array
     */
    private static $roomConfigs = [];

    /**
     * 心跳包时间间隔
     */
    const HEART_BEAT_INTERVAL = 20;

    /**
     * 直播间配置 URL
     */
    const CHAT_CONFIG_URL = 'https://api.live.bilibili.com/room/v1/Danmu/getConf?room_id=%d';

    /**
     * 获取直播间配置
     * @param $room_id
     * @return mixed
     * @throws \Exception
     */
    public static function getChatConfig($room_id)
    {
        if (isset(static::$roomConfigs[$room_id])) {
            return static::$roomConfigs[$room_id];
        }

        $response = file_get_contents(sprintf(self::CHAT_CONFIG_URL, $room_id));
        $response = json_decode($response, true);

        if (empty($response) || $response['code'] != 0) {
            throw new \Exception("Get chat conf failed, reason: {$response['msg']}");
        }

        static::$roomConfigs[$room_id] = $response['data'];

        return $response['data'];
    }

    /**
     * 获取认证包（加入房间）
     * @param $room_id
     * @param null $token
     * @return string
     * @throws \Exception
     */
    public static function getAuthenticatePacket($room_id, $token = null)
    {
        if (empty($token)) {
            $token = static::getChatConfig($room_id)['token'];
        }

        $payload = \json_encode([
            'uid' => 0,
            'roomid' => $room_id,
            'protover' => Packet::PROTOCOL_VERSION,
            'platform' => 'web',
            'token' => $token,
        ]);

        return Packet::pack(Opcode::AUTHENTICATION, $payload);
    }

    /**
     * 获取心跳包
     * @return string
     */
    public static function getHeartBeatPacket()
    {
        return Packet::pack(Opcode::CLIENT_HEARTBEAT);
    }
}
