<?php
/**
 * Created by PhpStorm.
 * User: zhuhe
 * Date: 17/6/6
 * Time: 上午9:40
 */

namespace ZPHP\Model;



use ZPHP\Coroutine\Tcp\TcpCoroutine;

class Tcp
{
    protected $tcpPool;

    function __construct($tcpPool)
    {
        $this->tcpPool = $tcpPool;
    }

    public function send($data){

        $data = array('data'=>$data);
        return yield $this->command($data);
    }
    private function command($data){
        $tcp = new TcpCoroutine($this->tcpPool);
        return $tcp->command($data);
    }

}