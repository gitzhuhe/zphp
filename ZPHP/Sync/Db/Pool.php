<?php
/**
 * Created by PhpStorm.
 * User: Sing
 * Mail: 78276478@qq.com
 * Date: 2018/3/29/029
 * Time: 8:58
 */

namespace ZPHP\Sync\Db;


class Pool
{
    private $db;
    private $config;
    private $rec;


    public function connect($value)
    {
        $this->config = $value;
        try {
            $this->db = new Medoo([
                'database_type' => 'mysql',
                'database_name' => $value['database'],
                'server' => $value['host'],
                'port' => $value['port'],
                'username' => $value['user'],
                'password' => $value['password'],
                'charset' => $value['charset']
            ]);
        } catch (\Exception $e) {
            $this->db = null;
        }
    }

    public function checkDb()
    {
        return empty($this->db) ? false : true;
    }

    private function getObj()
    {
        if (!$this->checkDb() && $this->config) {
            $this->connect($this->config);
        }
        return $this->db;
    }

    public function __call($name, $arguments)
    {
        $obj = $this->getObj();
        $result = call_user_func_array([$obj, $name], $arguments);

        //if (!$result) {
        $info = $obj->error();
        if ($info[1] == 2006 && $this->rec < 1) {
            $this->connect($this->config);
            $this->rec += 1;
            return call_user_func_array([$this, $name], $arguments);
        }
        //}
        $this->rec = 0;
        return $result;
        // TODO: Implement __call() method.
    }

    //禁止clone
    private function __clone()
    {
    }
}