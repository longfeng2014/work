<?php

// 在部署到生产时，请注释以下两行
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');


//FIREFOX,CHROME的php调试插件, 需安装firephp或firelogger插件, 以下两个文件可任意使用一个或都不使用,注释掉即可
if(file_exists('../docs/phpdebug/firelogger.php')) require_once('../docs/phpdebug/firelogger.php');
if(file_exists('../docs/phpdebug/firephp.php')) require_once('../docs/phpdebug/firephp.php');


require(__DIR__ . '/vendor/autoload.php');
require(__DIR__ . '/vendor/yiisoft/yii2/Yii.php');

$config = yii\helpers\ArrayHelper::merge(
    require(__DIR__ . '/config/main-local.php'),
    require(__DIR__ . '/config/main.php')
);

Yii::$classMap['common\base\Application'] = __DIR__.'/common/base/Application.php';
$application = new \common\base\Application($config);
$application->run();
// (new yii\web\Application($config))->run();
