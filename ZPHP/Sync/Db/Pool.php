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
    private $sqlObj;
    private $config;
    private $rec;


    public function connect($key, $value)
    {
        $this->config[$key] = $value;
        try {
            $this->db[$key] = new Medoo([
                'database_type' => 'mysql',
                'database_name' => $value['database'],
                'server' => $value['host'],
                'port' => $value['port'],
                'username' => $value['user'],
                'password' => $value['password'],
                'charset' => $value['charset']
            ]);
        } catch (\Exception $e) {
            $this->db[$key] = null;
        }
    }

    public function checkDb($key)
    {
        return empty($this->db[$key]) ? false : true;
    }

    private function getObj($dbNum = '')
    {
        $dbNum = empty($dbNum) ? 'default' : $dbNum;
        return $this->db[$dbNum];
    }

    public function table($table)
    {
        $this->sqlObj = new \stdClass();
        $this->sqlObj->table = $table;
        $this->sqlObj->field = "*";
        $this->sqlObj->where = [];
        return $this;
    }

    public function where($where)
    {
        $this->sqlObj->where = $where;
        return $this;
    }

    public function field($field = '*')
    {
        $this->sqlObj->field = $field;
        return $this;
    }

    public function __call($name, $arguments)
    {
        list($dbNum, $table) = explode('#', $this->sqlObj->table);
        if (empty($table)) {
            $table = $dbNum;
            $dbNum = 'default';
        }
        $obj = $this->getObj($dbNum);
        switch ($name) {
            case 'select':
            case 'get':
                $result = call_user_func_array([$obj, $name], [
                    $table,
                    $this->sqlObj->field,
                    $this->sqlObj->where
                ]);
                break;
            case 'update':
                $result = call_user_func_array([$obj, $name], [
                    $table,
                    $arguments[0],
                    $this->sqlObj->where
                ]);
                break;
            case 'insert':
                $result = call_user_func_array([$obj, $name], [
                    $table,
                    $arguments[0]
                ]);
                break;
        }
        if (!$result) {
            $info = $obj->error();
            if ($info[1] == 2006 && $this->rec[$dbNum] < 1) {
                $this->connect($dbNum, $this->config[$dbNum]);
                $this->rec[$dbNum] += 1;
                return call_user_func_array([$this, $name], $arguments);
            }
        }
        $this->rec[$dbNum] = 0;
        return $result;
        // TODO: Implement __call() method.
    }

}