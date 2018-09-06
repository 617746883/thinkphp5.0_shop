<?php
namespace app\admin\validate;
use think\Validate;

class Community extends Validate
{
    protected $rule =   [
        'mobile'  => 'require|max:11|min:11|number',
        'tel' => 'require',
    ];
    
    protected $message  =   [
        'mobile.require' => '手机号码不能为空',
        'mobile.number' =>'手机号码必须是数字',
        'mobile.max'     => '手机号码最多不能超过11个字符',
        'mobile.min'     => '手机号码最多不能少于11个字符',
        'tel.require'   => '电话不能为空',
    ];
    
}
?>