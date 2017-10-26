<?php
namespace modules\wechat\controllers;

use Yii;

/**
 * 微信端前台控制器
 */
class IndexController extends \common\wechat\Controller
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        echo '前台首页1<br>';
        echo '当前域名'.Yii::$app->request->hostInfo.'<br>'; 
        echo '<a href="'.Yii::$app->request->hostInfo.'/admin">后台首页</a>';
        return $this->render('index');
    }
}
