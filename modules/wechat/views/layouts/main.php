<?php
use common\helpers\Helper;
use yii\helpers\Html;
?>
<?php $this->beginPage();?>
<!DOCTYPE html>
<html lang="<?=Yii::$app->language;?>">
<head>
    <meta charset="<?=Yii::$app->charset;?>">
    <meta name="description" content="" />
    <meta name="keywords" content="" />
    <meta name="format-detection" content="telephone=no" />
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-touch-fullscreen" content="YES" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black" />
    <meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no"/>
<?=Html::csrfMetaTags();?>
<script type="text/javascript">
    if(window.__wxjs_is_wkwebview){ 
        //WKWebview内核
        document.write("<scri"+"pt src='https://res.wx.qq.com/open/js/jweixin-1.2.0.js'></sc"+"ript>");
    }else{
        //UIWebView//内核
        document.write("<scri"+"pt src='https://res.wx.qq.com/open/js/jweixin-1.1.0.js'></sc"+"ript>");
    };
</script>
<link href="/statics/themes/wechat/weui/lib/weui.min.css" rel="stylesheet">
<link href="/statics/themes/wechat/weui/css/jquery-weui.css" rel="stylesheet">
<script type="text/javascript" src="/statics/themes/wechat/weui/lib/fastclick.js"></script>
<script type="text/javascript" src="/statics/themes/wechat/weui/lib/jquery-2.1.4.min.js"></script>
    <title><?=Html::encode($this->title);?></title>
<?php $this->head();?>

<script type="text/javascript">

</script>
<link rel="shortcut icon" href="/favicon.ico"/>
</head>
<body>
<?php $this->beginBody();?>
    <div class="main">
    <?php echo $content; ?>
    </div>
    <div class="clear"></div>
    <footer class="footer">
        <div class="container">
        <p class="pull-left"></p>
        </div>
    </footer>
<?php $this->endBody();?>
<script type="text/javascript" src="/statics/themes/wechat/weui/js/jquery-weui.min.js"></script>
</body>
</html>
<?php $this->endPage();?>
