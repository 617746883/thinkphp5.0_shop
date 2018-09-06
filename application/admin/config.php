<?php
return [
    'view_replace_str' => [
        '__STATIC__' => '/public/static',
        '__CSS__' => '/public/static/css',
        '__JS__' => '/public/static/js',
        '__IMG__' => '/public/static/images',
    ],    
    'AUTH_CODE' => 'SUL1SS2018',  //安装后不要更改
    'authkey' => 'SUL1SS2018', 
    //文件上传配置
    'UploadFile' => [
        //文件上传根目录
        'rootPath'        => 'attachment/',
        //图片上传目录
        'imageSavePath'   => 'images/',
        //视频上传目录
        'vedioSavePath'   => 'video/',
        //音频上传目录
        'audioSavePath'   => 'audio/',
        //编辑器上传目录
        'ueditorSavePath' => 'ueditor/',
        //图片上传大小
        'imageFileSize'   => 1024 * 1024 * 3,
        //视频上传大小
        'videoFileSize'   => 1024 * 1024 * 50,
        //音频上传大小限制
        'audioFileSize'   => 1024 * 1024 * 5,
        //图片格式
        'imageExts'       => ['jpg','png','gif','jpeg','webp'],
        //视频格式
        'vedioExts'       => ['mp4','mov'],
        //音频格式
        'audioExts'       => ['mp3','wma'],
        //是否上传七牛
        'isUploadQiniu'   => false,
    ],
    'session' => [
        'auto_start' => true,
        'name' => 'login@',
        'expire' => 1800000,   /*时间长度*/
    ],
]
?>