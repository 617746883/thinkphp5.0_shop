<?php
namespace app\apiv1\validate;

use think\Validate;
class Secondgoods extends Validate
{
	protected $rule =   [
        'id'  => 'integer',
        'cate'  => 'require',
        'title'  => 'require',
        'mobile'  => 'require|mobile',
        'degree'  => 'require',
        'productprice'  => 'require',
        'marketprice'  => 'require',
        // 'address'  => 'require',
    ];
    
    protected $message  =   [
        'id.integer'     => '参数错误', 
        'cate.require' => '请选择分类',
        'title.require' => '请輸入寶貝名稱',
        'mobile.require' => '请填写联系电话',
        'mobile.mobile' => '电话号码格式错误',
        'province.require' => '请輸入原價',
        'marketprice.require' => '请輸入市場價',
        // 'area.require' => '请选择区',
        // 'address.require' => '请填写详细地址',
    ];
}