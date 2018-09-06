<!-- <?php
    if(config('layout_on')) {
        echo '{__NOLAYOUT__}';
    }
?> -->
{__NOLAYOUT__}
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>跳转提示</title>
<script src="http://libs.baidu.com/jquery/1.9.1/jquery.min.js"></script>
<script src="__STATIC__/layer/layer.js"></script>
</head>
<body>
<input type="hidden" id='msg' name='msg' value="
<?php
if(isset($msg)){
  echo($msg);
}else{
  echo($error);
}
?>
" />
<input type="hidden" id='url' name='url' value="<?php echo($url); ?>" />
<input type="hidden" id='wait' name='wait' value="<?php echo($wait); ?>" />
<script type="text/javascript">
(function(){
// var wait = document.getElementById('wait'),href = document.getElementById('href').href;
var msg=$("#msg").val();
var url=$("#url").val();
var wait=$("#wait").val();

layer.open({
  content: msg,
  btn: ['确定'],
  yes: function(index, layero){
    //按钮【按钮一】的回调
    location.href=url;
  },
  cancel: function(){ 
    //右上角关闭回调
    location.href=url;
  }
});

var interval = setInterval(function(){
  var time = --wait.innerHTML;
  if(time <= 0) {
    location.href = url;
    clearInterval(interval);
  };
}, 1000);
})();
</script>
</body>
</html>

{__NOLAYOUT__}<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>跳转提示</title>
    <style type="text/css">
        *{ padding: 0; margin: 0; }
        body{ background: #fff; font-family: "Microsoft Yahei","Helvetica Neue",Helvetica,Arial,sans-serif; color: #333; font-size: 16px; }
        .system-message{ padding: 24px 48px; }
        .system-message h1{ font-size: 100px; font-weight: normal; line-height: 120px; margin-bottom: 12px; }
        .system-message .jump{ padding-top: 10px; }
        .system-message .jump a{ color: #333; }
        .system-message .success,.system-message .error{ line-height: 1.8em; font-size: 36px; }
        .system-message .detail{ font-size: 12px; line-height: 20px; margin-top: 12px; display: none; }
    </style>
</head>
<body>
    <div class="system-message">
        <?php switch ($code) {?>
            <?php case 1:?>
            <h1></h1>
            <p class="success"><?php echo(strip_tags($msg));?></p>
            <?php break;?>
            <?php case 0:?>
            <h1>:(</h1>
            <p class="error"><?php echo(strip_tags($msg));?></p>
            <?php break;?>
        <?php } ?>
        <p class="detail"></p>
        <p class="jump">
            页面自动 <a id="href" href="<?php echo($url);?>">跳转</a> 等待时间： <b id="wait"><?php echo($wait);?></b>
        </p>
    </div>
    <script type="text/javascript">
        (function(){
            var wait = document.getElementById('wait'),
                href = document.getElementById('href').href;
            var interval = setInterval(function(){
                var time = --wait.innerHTML;
                if(time <= 0) {
                    location.href = href;
                    clearInterval(interval);
                };
            }, 1000);
        })();
    </script>
</body>
</html>

