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
        $config = Config::get('mysql', []);
        foreach ($config as $key => $value) {
            $this->mysqlPool[$key] = new Pool();
            if (!$this->mysqlPool[$key]->checkDb() && $value) {
                $this->mysqlPool[$key]->connect($value);
            }
        }
    }

    public function getPool()
    {
        return $this->mysqlPool;
    }
}