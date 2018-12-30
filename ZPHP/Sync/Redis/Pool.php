<?php
/**
 * Created by PhpStorm.
 * User: Sing
 * Mail: 78276478@qq.com
 * Date: 2018/3/29/029
 * Time: 8:58
 */

namespace ZPHP\Sync\Redis;


class Pool
{
    private $redis;
    public $config;


    public function connect($value)
    {

        $this->config = $value;
        if (!$this->redis) {
            try {
                $this->redis = new \redis();
                $this->redis->connect($value['ip'], $value['port']);
                if ($value['password']) $this->redis->auth($value['password']);
            } catch (\Exception $e) {
                $this->redis = null;
                throw new \Exception($e->getMessage());
            }
        }
        return $this->redis;
    }

    public function checkDb()
    {
        return empty($this->redis) ? false : true;
    }


    public function __call($name, $arguments)
    {
        call_user_func_array([$this->redis, $name], $arguments);
        // TODO: Implement __call() method.
    }

    //禁止clone
    private function __clone()
    {
    }

}