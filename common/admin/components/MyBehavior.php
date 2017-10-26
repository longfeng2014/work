<?php
namespace common\admin\components;

use Yii;

class MyBehavior extends \yii\base\ActionFilter
{
    public function beforeAction ($action)
    {
        return true;
    }

    /**
     * 判断当前用户身份
     * @return boolean [description]
     */
    /*public function isGuest ()
    {
        return Yii::$app->user->isGuest;
    }*/
}
