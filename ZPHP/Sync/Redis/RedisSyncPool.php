<?php
/**
 * Created by PhpStorm.
 * User: Sing
 * Date: 2018/3/27/027
 * Time: 17:18
 */

namespace ZPHP\Sync\Redis;


use ZPHP\Core\Config;
use ZPHP\Core\Log;

class RedisSyncPool
{
    private $redislPool;

    public function __construct()
    {
        $config = Config::get('redis');
        $this->redislPool = new Pool();
        // $this->redislPool->config = $config;
        if (empty($this->redislPool->checkDb()) && $config) {
            $this->redislPool->connect($config);
        }
    }

    public function getPool()
    {
        return $this->redislPool;
    }
}