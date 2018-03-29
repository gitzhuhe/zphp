<?php
/**
 * Created by PhpStorm.
 * User: Sing
 * Date: 2018/3/27/027
 * Time: 17:18
 */

namespace ZPHP\Sync\Db;


use ZPHP\Core\Config;
use ZPHP\Core\Log;

class DbSyncPool
{
    private $mysqlPool;

    public function __construct($workerId)
    {
        $config = Config::get('mysql');
        $this->mysqlPool = new Pool();
        foreach ($config as $key => $value) {
            if (empty($this->mysqlPool->checkDb($key))) {
                $this->mysqlPool->connect($key, $value);
            }
        }
    }

    public function getPool()
    {
        return $this->mysqlPool;
    }
}