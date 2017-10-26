<?php
/**
 * 缓存类, 统一管理缓存
 * 功能 : 统一管理系统缓存, 分配缓存KEY
 *
 * @author tasal<fei.he@pcstars.com>
 * @version $Id: Cache.php 3801 2015-02-28 05:40:33Z A1165 $
 */

namespace common\base;

use Yii;
use common\models\WeAccount;

class Cache
{

    public static function get($key)
    {
        return Yii::$app->cache->get($key);
    }

    public static function set($key,$value,$timeout=0)
    {
        return Yii::$app->cache->set($key,$value,$timeout);
    }

    public static function weaccount($xId,$renew=false)
    {
        $key = 'api_weaccount_'.$xId;
        if(self::get($key) && !$renew){
            return self::get($key);
        }else{
            if(is_numeric($xId)){
                $weAccountInfo = WeAccount::model()->findByPk((int)$xId);
            }else{
                $weAccountInfo = WeAccount::model()->findByAttributes(array('hash_id' => $xId));
            }
            self::set($key,$weAccountInfo,60*10);
            return $weAccountInfo;
        }
    }

}