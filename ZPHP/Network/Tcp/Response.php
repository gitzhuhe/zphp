<?php
/**
 * Created by PhpStorm.
 * User: zhaoye
 * Date: 2017/4/14
 * Time: 下午5:52
 */
namespace ZPHP\Network\Tcp;

use ZPHP\Dora\Packet;
use ZPHP\Network\BaseResponse;

class Response extends BaseResponse{
    protected $swServer;
    protected $swFd;
    protected $swContent;
    public function finish($server, $fd ,$tcpData)
    {
        $this->swServer = $server;
        $this->swFd = $fd;
        $this->swContent = Packet::packFormat($tcpData['guid'],'OK',0,array($this->content));
        $this->swContent['guid'] = $tcpData['guid'];
        $this->swContent = Packet::packEncode($this->swContent);
        $this->swServer->send($this->swFd, $this->swContent);
    }
}