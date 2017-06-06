<?php
/**
 * Created by PhpStorm.
 * User: zhaoye
 * Date: 16/9/14
 * Time: 下午3:47
 */


namespace ZPHP\Coroutine\Tcp;

use ZPHP\Core\Log;
use ZPHP\Coroutine\Base\CoroutineBase;

class TcpCoroutine extends CoroutineBase{
    public $bind_id;
    /*
     * $this->data = $sql;
     */


    public function __construct(TcpAsynPool $tcpAsynPool)
    {
        $this->ioVector = $tcpAsynPool;
    }

}