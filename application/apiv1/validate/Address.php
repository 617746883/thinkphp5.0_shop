<?php
namespace app\apiv1\validate;

use think\Validate;

class Address extends Validate
{
    protected $rule =   [
        'mid'  => 'require|integer',
        'realname'  => 'require',
        'mobile'  => 'require',
        'province'  => 'require',
        'city'  => 'require',
        'area'  => 'require',
        'address'  => 'require',
    ];
    
    protected $message  =   [
        'mid.require' => '参数错误',
        'mid.integer'     => '参数错误', 
        'realname.require' => '请填写姓名',
        'mobile.require' => '请填写联系电话',
        'province.require' => '请选择省',
        'city.require' => '请选择市',
        'area.require' => '请选择区',
        'address.require' => '请填写详细地址',
    ];

}