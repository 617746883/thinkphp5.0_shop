<?php
namespace app\admin\validate;
use think\Validate;

class Community extends Validate
{
    protected $admin =   [
        'username'  => 'require|max:25',
        'password' => '',
    ];
    
    protected $message  =   [
        'username.require' => '账号不能为空',
        'password.require' => '手机号码不能为空',
        'mobile.number' =>'手机号码必须是数字',
        'mobile.max'     => '手机号码最多不能超过11个字符',
        'mobile.min'     => '手机号码最多不能少于11个字符',
        'tel.require'   => '电话不能为空',
    ];
    
}
?>