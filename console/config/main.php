<?php
/**
 * 控制台配置文件会和config/main-local.php合并,
 * 可通过覆盖的方式来完成特殊设置
 * 当然,会剔除控制台不需要的组件[user],[request],[errorHandler]
 * @version  $Id: main.php 4108 2015-03-07 07:58:58Z A1165 $
 */
return [
    'id' => 'console',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'console\controllers',
];
