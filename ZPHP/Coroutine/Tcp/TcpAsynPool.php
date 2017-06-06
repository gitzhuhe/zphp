<?php
/**
 * Created by PhpStorm.
 * User: zhaoye
 * Date: 16/9/14
 * Time: 下午2:58
 */

namespace ZPHP\Coroutine\Tcp;

use ZPHP\Core\Config;
use ZPHP\Core\Log;
use ZPHP\Coroutine\Base\IOvector;
use ZPHP\Coroutine\Pool\AsynPool;
use ZPHP\Dora\DoraConst;
use ZPHP\Dora\Packet;

class TcpAsynPool extends AsynPool implements IOvector
{

    protected $_asynName = 'Tcp';
    protected $_transList = [];
    /**
     * @var array
     */
    public $bind_pool;
    private $set;
    private $client;

    private static $asynclist;

    public function __construct()
    {
        $this->set = array(
            //'open_length_check' => 1,
            //'package_length_type' => 'N',
            'package_length_offset' => 0,
            'package_body_offset' => 4,
            'package_max_length' => 1024 * 1024 * 2,
            'open_tcp_nodelay' => 1,
            'socket_buffer_size' => 1024 * 1024 * 4,
        );

        parent::__construct();
    }


    /**
     * 执行一个sql语句
     * @param $callback
     * @param $bind_id 绑定的连接id，用于事务
     * @param $sql
     */
    public function command(callable $callback = null, $data = [])
    {
        $this->checkAndExecute($data, $callback);
    }


    /**
     * 重连或者连接
     * @param array $data ['token'] 异常回调的索引
     * @param null $client
     */
    public function reconnect($data, $tmpClient = null)
    {
        if($this->client == null){
            $this->client = new \swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_ASYNC);
            $this->client->set($this->set);
            $this->client->on("connect", function ($cli) use ($data) {
                $this->bind_pool = $cli;
                $this->commands->enqueue($data);
                $this->pushToPool($this->bind_pool);
            });
            $this->client->on("receive", function ($cli, $recdata) {
                //Log::write('receive', 1);
            });
            $this->client->on("error", function ($cli) {
                if ($cli->isConnected()) {
                    $cli->close();
                } else {
                    $this->client = null;
                }
            });
            $this->client->on("close", function ($cli) {
                Log::write('close', 1);
            });
        }
        $this->client->connect($this->config['ip'], $this->config['port']);
    }


    /**
     * 执行mysql命令
     * @param $data
     */
    public function execute($data)
    {
        //$data['result'] = null;
        //$this->distribute($data);
        //return;
        //Log::write('execute',1);

        //TODO 目前这里会阻塞work进程
        if ($this->client == null) {
            $this->max_count = 0;
            $this->prepareOne($data);
            return;
        }
        if (!$this->client->isConnected()) {
            $this->max_count--;
            unset($this->client);
            $this->client = null;
            $this->prepareOne($data);
            return;
        }

        $guid = $this->generateGuid();
        $packet = array(
            'path_info' => '',
            'request_method' => '',
            'param' => array(),
            'guid'=>$guid
        );
        $packet["type"] = DoraConst::SW_MODE_WAITRESULT_SINGLE;

        //$sendData = Packet::packFormat($guid, 'OK', 0 , $packet);
        $sendData = Packet::packEncode($packet);
        $this->client->send($sendData);

        //unset($this->client);
        //$this->client = null;
        $data['result'] = null;
        $this->distribute($data);
    }


    private function generateGuid()
    {
        //to make sure the guid is unique for the async result
        while (1) {
            $guid = md5(microtime(true) . mt_rand(1, 1000000) . mt_rand(1, 1000000));
            //prevent the guid on the async list
            if (!isset(self::$asynclist[$guid])) {
                return $guid;
            }
        }
    }

    /**
     * 释放事务连接,回归到连接池
     * @param $data
     */
    protected function freeTransConnect($data)
    {
        if (!empty($data['trans_id'])) {
            $client = $this->_transList[$data['trans_id']];
            $this->pushToPool($client);
            unset($this->_transList[$data['trans_id']]);
        }

    }

}