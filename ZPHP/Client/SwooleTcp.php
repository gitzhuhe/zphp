<?php
/**
 * Created by PhpStorm.
 * User: zhaoye
 * Date: 2016/12/26
 * Time: 上午9:35
 */

namespace ZPHP\Client;

use ZPHP\Core\App;
use ZPHP\Core\Container;
use ZPHP\Core\Db;
use ZPHP\Core\DI;
use ZPHP\Core\Dispatcher;
use ZPHP\Core\Factory;
use ZPHP\Core\Config;
use ZPHP\Core\Log;
use ZPHP\Core\Request;
use ZPHP\Core\Route;
use ZPHP\Core\Swoole;
use ZPHP\Coroutine\Base\CoroutineTask;
use ZPHP\Dora\Packet;
use ZPHP\Protocol\Response;
use ZPHP\Session\Session;
use ZPHP\Socket\Callback\SwooleTcp as ZSwooleTcp;
use ZPHP\Socket\IClient;

class SwooleTcp extends ZSwooleTcp
{
    /**
     * @var Dispatcher $dispatcher
     */
    protected $dispatcher;
    /**
     * @var CoroutineTask $coroutineTask
     */
    protected $coroutineTask;

    /**
     * @var Request $requestDeal;
     */
    protected $requestDeal;

    protected $taskObjectArray;

    public function onReceive(\swoole_server $serv, $fd, $from_id, $data)
    {
        try {
            $requestDeal = clone $this->requestDeal;
            $requestDeal->initTcp($serv, $fd , $data);
            $pack = $this->dispatcher->distribute($requestDeal);

            if($pack!=='NULL') {
                $serv->send($fd , $pack);
            }
        } catch (\Exception $e) {
            $message = explode('|',$e->getMessage());
            $code = intval($message[0]);
            if($code==0){
                $httpResult = Swoole::tcpInfo($e->getMessage());
            }else {
                $otherMessage = !empty($message[1])?' '.$message[1]:'';
                $httpResult = Swoole::tcpInfo($code.$otherMessage);
            }
            //TODO 异常暂时处理方式
            if(!empty($requestDeal->tcpData['guid'])){
                $guid = $requestDeal->tcpData['guid'];
                $httpResult = Packet::packFormat($guid,'error',$code,$httpResult);
                $httpResultData = Packet::packEncode($httpResult);
                $serv->send($fd , $httpResultData);
            }
        }
    }

    /**
     * @param $server
     * @param $workerId
     * @throws \Exception
     */
    public function onWorkerStart($server, $workerId)
    {
        parent::onWorkerStart($server, $workerId);
        $common = Config::get('common_file');
        if(!empty($common)){
            require ROOTPATH.$common;
        }

        if (!$server->taskworker) {
            App::init(DI::getInstance());
            //worker进程启动协程调度器
            //work一启动加载连接池的链接、组件容器、路由
            Db::init($server, $workerId);
            Route::init();
            Session::init();
            $this->coroutineTask = Container::Coroutine('Base/CoroutineTask');
            $this->dispatcher = Container::Core('Dispatcher');
            $this->requestDeal = Container::Core('Request', $this->coroutineTask);
        }
    }


    /**
     * @param $server
     * @param $workerId
     */
    public function onWorkerStop($server, $workerId){
        if(!$server->taskworker) {
            Db::getInstance()->freeMysqlPool();
            Db::getInstance()->freeRedisPool();
        }
        parent::onWorkerStop($server, $workerId);
    }


    public function onWorkerError($server, $workerId, $workerPid, $errorCode)
    {
        //errorCode可以用来警报 或者其他的定制化
        parent::onWorkerError($server, $workerId, $workerPid, $errorCode);
    }

    public function onTask($server, $taskId, $fromId, $data)
    {
        try{
            $checkParam = ['class', 'method'];
            foreach($checkParam as $p){
                if(empty($data[$p])){
                    throw new \Exception($p." can't be empty!");
                }
            }
            if(empty($this->taskObjectArray[$data['class']])){
                $classParam = !empty($data['class_param'])?$data['class_param']:null;
                $data['class'] = str_replace('/','\\', $data['class']);
                $this->taskObjectArray[$data['class']] = Factory::getInstance($data['class'],$classParam);
                $taskObject = $this->taskObjectArray[$data['class']];
                if(method_exists($taskObject, 'init')){
                    call_user_func([$taskObject, 'init']);
                }
            }else{
                $taskObject = $this->taskObjectArray[$data['class']];
            }
            if(method_exists($taskObject, 'before')){
                call_user_func([$taskObject, 'before']);
            }
            $res = call_user_func_array([$taskObject, $data['method']], $data['param']);
            if(method_exists($taskObject, 'complete')){
                call_user_func([$taskObject, 'complete']);
            }
            return ['result'=>$res];
        }catch(\Exception $e){
            return ['exception'=>$e->getMessage()];
        }
    }

}
