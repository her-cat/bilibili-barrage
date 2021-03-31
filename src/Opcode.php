<?php


namespace App;

/**
 * 操作码
 */
class Opcode
{
    /**
     * 客户端发送的心跳包
     */
    const CLIENT_HEARTBEAT = 2;

    /**
     * 人气值，数据不是JSON，是4字节整数
     */
    const POPULARITY_VALUE = 3;

    /**
     * 命令，数据中['cmd']表示具体命令
     */
    const CMD = 5;

    /**
     * 认证并加入房间
     */
    const AUTHENTICATION = 7;

    /**
     * 服务器发送的心跳包
     */
    const SERVER_HEARTBEAT = 8;
}
