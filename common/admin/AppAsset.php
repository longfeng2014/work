<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace common\admin;

use yii\web\AssetBundle;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [

    ];
    public $js = [
    ];
    public $depends = [
        "yii\web\YiiAsset",
        "yii\bootstrap\BootstrapAsset",
        "yii\web\AssetBundle",
    ];
    //定义按需加载JS方法，注意加载顺序在最后  
    public static function addJs($view, $jsfile) {  
        $view->registerJsFile($jsfile, [AppAsset::className(), "depends" => "common\admin\AppAsset"]);  
    }  
    //定义按需加载css方法，注意加载顺序在最后  
    public static function addCss($view, $cssfile) {  
        $view->registerCssFile($cssfile, [AppAsset::className(), "depends" => "common\admin\AppAsset"]);  
    } 
}
