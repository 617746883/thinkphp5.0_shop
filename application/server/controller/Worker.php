<?php
namespace app\server\controller;
 
use think\worker\Server;
use Workerman\Lib\Timer;
class Worker extends Server
{
    protected $socket = 'websocket://0.0.0.0:2346';
    protected $processes = 1;
    
    public function onWorkerStart($work)
    {
        $handle = new Index();
        $handle->add_timer();
    }

    /**
     * 收到信息
     * @param $connection
     * @param $data
     */
    public function onMessage($connection, $data)
    {
        $connection->send('我收到你的信息了');
    }

    /**
     * 当连接建立时触发的回调函数
     * @param $connection
     */
    public function onConnect($connection)
    {

    }

    /**
     * 当连接断开时触发的回调函数
     * @param $connection
     */
    public function onClose($connection)
    {
        
    }

    /**
     * 当客户端的连接上发生错误时触发
     * @param $connection
     * @param $code
     * @param $msg
     */
    public function onError($connection, $code, $msg)
    {
        echo "error $code $msg\n";
    }

    /**
     * 每个进程启动
     * @param $worker
     */
    // public function onWorkerStart($worker)
    // {
    //     Timer::add(1, function()use($worker){
    //         $time_now = time();
    //         foreach($worker->connections as $connection) {
    //             // 有可能该connection还没收到过消息，则lastMessageTime设置为当前时间
    //             if (empty($connection->lastMessageTime)) {
    //                 $connection->lastMessageTime = $time_now;
    //                 file_put_contents("workerlog.log",$time_now,FILE_APPEND);//记录日志
    //                 continue;
    //             }
    //             // 上次通讯时间间隔大于心跳间隔，则认为客户端已经下线，关闭连接
    //             if ($time_now - $connection->lastMessageTime > HEARTBEAT_TIME) {
    //                 $connection->close();
    //             }
    //         }
    //     });
    // }
}