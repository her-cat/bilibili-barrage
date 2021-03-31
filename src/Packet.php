<?php

namespace App;

/**
 * 数据包
 */
class Packet
{
    /**
     * 头部长度
     */
    const HEADER_LEN = 16;

    /**
     * 协议版本
     */
    const PROTOCOL_VERSION = 2;

    /**
     * 魔法数字，设置为 1 即可
     */
    const MAGIC_NUMBER = 1;

    /**
     * 打包
     * @param int $opcode
     * @param string $payload
     * @return string
     */
    public static function pack($opcode, $payload = '')
    {
        $packetLen = static::HEADER_LEN;
        if (!empty($payload)) {
            $packetLen += strlen($payload);
        }

        return pack('NnnNN', $packetLen, static::HEADER_LEN, static::PROTOCOL_VERSION, $opcode, static::MAGIC_NUMBER).$payload;
    }

    /**
     * 解包
     * @param $data
     * @return array|false
     */
    public static function unpack($data)
    {
        if (empty($data)) {
            return [];
        }

        return unpack('Npacket_len/nheader_len/nprotocol_version/Nopcode/Nmagic_number/a*payload', $data);
    }

    /*
     * 解析携带的数据
     */
    public static function parsePayload($opcode, $payload)
    {
        switch ($opcode) {
            case Opcode::POPULARITY_VALUE:
                return unpack('N', $payload);
            case Opcode::CMD:
                if ($payload[0] != '{') {
                    $payload = static::unpack(gzuncompress($payload))['payload'];
                }
                return json_decode($payload, true);
            case Opcode::SERVER_HEARTBEAT:
                $payload = static::unpack($payload)['payload'];
                return json_decode($payload, true);
            default:
                return $payload;
        }
    }
}
