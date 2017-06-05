<?php
/**
 * Created by PhpStorm.
 * User: zhaoye
 * Date: 2016/12/1
 * Time: 上午11:03
 */

namespace ZPHP\Network\Tcp;
use ZPHP\Common\Utils;
use ZPHP\Core\Config;
use ZPHP\Core\Rand;
use ZPHP\Session\Session;

/**
 * 关于http的输入,如get,post,session,cookie参数
 * Class Httpinput
 * @package ZPHP\Network\Http
 */
class Request{

    public $request;

    function __construct()
    {

    }

    public function init($request){
        $this->request = $request['param'];
    }

    /**
     * @param $key
     * @param bool $filter
     * @return string
     */
    public function __call($method, $param){
        if(empty($param)){
            return $this->$method;
        }else {
            return isset($this->$method[$param[0]])?$this->_getHttpVal($this->$method[$param[0]], isset($param[1]) ? $param[1]:true ):null;
        }
    }


    /**
     * @param $variableArray
     * @param $key
     * @param $filter
     * @return string
     */
    protected function _getHttpVal($value, $filter){
        if(!isset($value))
            return null;
        if($filter)
            return Utils::filter($value);
        else
            return $value;
    }

}