<?php
namespace app\common\exception;

use Exception;
use think\exception\Handle;
use think\exception\HttpException;
class Http extends Handle
{

    public function render (Exception $e)
    {
        // 参数验证错误
        if ($e instanceof ValidateException) {
            return json($e->getError(), 422);
        }

        // 请求异常
        if ($e instanceof HttpException && request()->isAjax()) {
            return response($e->getMessage(), $e->getStatusCode());
        }

        if ( $e instanceof HttpException ) {
            if ( stristr ($e->getMessage (), "module not exists:") ) {
                echo "<script>window.location.href='http://{$_SERVER[ 'HTTP_HOST' ]}';</script>";exit;
            }
        }
        echo "<script>window.location.href='http://{$_SERVER[ 'HTTP_HOST' ]}';</script>";exit;
    }

}