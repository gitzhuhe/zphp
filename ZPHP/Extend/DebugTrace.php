<?php
/**
 * Created by PhpStorm.
 * User: zhaoye
 * Date: 2018/3/29
 * Time: 下午4:04
 */

namespace ZPHP\Extend;

class DebugTrace{
    const Name = "DebugTrace";
    const Valid = "DebugValid";
    private $_content = [];
    private $_valid = false;
    public function __construct($valid=false)
    {
        $this->_valid = $valid;
    }
    public function __clone()
    {
    }

    public function addParam($traceKey, $debugInfo){
        if(!$this->checkValid())return;
        $this->_content[$traceKey] = [
            "carrier" => $debugInfo["carrier"],
            "param" => $debugInfo["param"],
            "starttime" => $this->getNowMillSecond(),
        ];
    }


    public function addResult($traceKey, $result){
        if(!$this->checkValid())return;
        $this->_content[$traceKey]["endtime"] = $this->getNowMillSecond();
        if(!empty($result["exception"])){
            $this->_content[$traceKey]["exception"] = $result["exception"];
        }
        $this->_content[$traceKey]["result"] = $result;
        $this->_content[$traceKey]["costtime"] = sprintf("%.2f ms", $this->_content[$traceKey]["endtime"]
            - $this->_content[$traceKey]["starttime"]);
    }

    private function getNowMillSecond(){
        list($t1, $t2) = explode(' ', microtime());
        return ($t1+$t2)*1000;
    }

    public function getAll(){
        $content = [];
        foreach ($this->_content as $val){
            $content[] = $val;
        }
        return $content;
    }

    private function checkValid(){
        return $this->_valid;
    }


}