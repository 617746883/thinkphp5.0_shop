sul1ss_shop
===============
Author:SUL1SS  
Email:617746883@qq.com

## 简介与现状 ##

 + 自营+多商户
 + 管理后台+api接口
 + 核心功能组件化
##### 预计后续实现功能:积分商城、团购、拍卖、秒杀、夺宝等插件。客户端-web手机端将采用vue搭建脚手架框架。

由于个人能力有限，所以应用的代码在有些地方设计可能存在不合理，代码也显得臃肿，同时用户体验、应用流畅性、代码健壮性和可扩展性还有待改进。欢迎大家指正。
如果这个项目让你有所收获，请Star✨ and Fork有时间我会持续更新下去的。 注：如果遇到问题还请Issues,我会尽快回复。如果此项目中有更好的建议欢迎指教。

## 后台组件
**上传组件**
>图片上传与选择控件，**此组件支持单图片上传**
tpl_form_field_image2($name, $value = '', $default = '', $options = array())
- $name 表单字段的名称，同一页面不能为空
- $value 表单input值
- $default 默认显示的缩略图
- $options 图片上传配置信息

###### *示例*
```php
{:tpl_form_field_image2('thumb',$item['thumb'])}
```
------------
>批量上传图片，**此组件支持多图片上传**
tpl_form_field_multi_image2($name, $value = array(), $options = array())
- $name 表单字段的名称，同一页面不能为空
- $value 附件路径信息
- $options 图片上传配置信息

###### *示例*
```php
{:tpl_form_field_multi_image2('thumbs',$piclist)}
```
------------
>音频选择与上传，**此组件支持单音频上传**
tpl_form_field_audio($name, $value = '', $options = array())
- $name 表单字段的名称，同一页面不能为空
- $value 附件路径信息
- $options 图片上传配置信息

###### *示例*
```php
{:tpl_form_field_audio('audio_url',$article['file_url'])}
```
------------
>视频选择与上传，**此组件支持单视频上传**
tpl_form_field_audio($name, $value = '', $options = array())
- $name 表单字段的名称，同一页面不能为空
- $value 附件路径信息
- $options 图片上传配置信息

###### *示例*
```php
tpl_form_field_video2($name, $value = '', $options = array())
```
------------
##### 富文本编辑器
系统中的富文本编辑器基于百度编辑器（UEditor），扩展其上传图片组件及上传视频组件。
> tpl_ueditor($id, $value = '', $options = array())
- $id 生成富文本编辑器的名称，最终提交POST/GET也是通过此名称来取
- $value 默认的文本内容
- $options 参数配置数组，键值说明如下： 
  - height 正整数，结尾不加px。编辑器的高度
  

------------

##### 系统链接选择器
> tpl_selector($name, $options = array())

------------
##### 其它常用组件
> 時間選擇控件
tpl_form_field_daterange($name, $value = array(), $time = false)

------------

> 时间选择控件
tpl_daterange($name, $value = array(), $time = false)

------------

>日期选择控件 
tpl_form_field_date($name, $value = '', $withtime = false)

------------

> 经纬度获取控件
tpl_form_field_position($field, $value = array())

------------

> 颜色选择控件
tpl_form_field_color($name, $value = '')

------------

> input修改控件
tpl_form_field_editor($params = array(), $callback = NULL)




