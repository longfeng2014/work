<?php
/**
 * ActiveQuery
 *
 * @author tasal<fei.he@pcstars.com>
 * @version $Id: ActiveQuery.php 29756 2016-08-22 06:59:54Z A1236 $
 */

namespace common\base;

use Yii;
use common\base\Cache;

class ActiveQuery extends \yii\db\ActiveQuery
{

    public $cache = null;

    /**
     * 重写原函数实现自动缓存查询结果的功能
     */
    public function all($db = null)
    {
        // if($this->cache !== null){
            $sql = $this->createCommand()->getRawSql();
            $key = 'autosql'.substr(md5($sql),16);
        // }
        //修复查询为null时不会缓存的问题 edit by tasal 20151216
        if($key && Cache::get($key)!==false){
            $result = Cache::get($key);
            //集成firelogger 或 firephp 调试
            if(YII_DEBUG_FIRE_SHOW_SQL && (function_exists('flog') || function_exists('fb') )){
                function_exists('flog') && flog('info','Caching('.$key.'):',$sql);
                function_exists('fb') && fb($sql,'Caching('.$key.'):','log');
            }
        }else{
            $result = parent::all($db);
            if($this->cache !== null){
                Cache::set($key,$result,$this->cache);
                $this->cache = null;
            }
        }
        
        return $result;
    }

    /**
     * 重写原函数实现自动缓存查询结果的功能
     */
    public function one($db = null)
    {
        // if($this->cache !== null){
            $sql = $this->createCommand()->getRawSql();
            $key = 'autosql'.substr(md5($sql),16);
        // }

        if($key && Cache::get($key)!==false){
            $result = Cache::get($key);
            //集成firelogger 或 firephp 调试
            if(YII_DEBUG_FIRE_SHOW_SQL && (function_exists('flog') || function_exists('fb') )){
                function_exists('flog') && flog('info','Caching('.$key.'):',$sql);
                function_exists('fb') && fb($sql,'Caching('.$key.'):','log');
            }
        }else{
            $result = parent::one($db);
            if($this->cache !== null){
                Cache::set($key,$result,$this->cache);
                $this->cache = null;
            }
        }
        
        return $result;
    }

    /**
     * 增加条件status,
     * @param  mixed $status 为null时,查询全部,为空时查询>0的结果(我们约定小于0的状态一般是删除等不可见状态),为字符串时会强制转为int查询,为数组时会使用in查询
     * @return [type]         [description]
     */
    public function status($status='')
    {
        if ($status===null) {
            return $this;
        }

        if ($status === '') {
            $this->andWhere(['>=','status',0]);
        }elseif(is_array($status)){
            $this->andWhere(['status'=>$status]);
        }else{
            $this->andWhere(['status'=>(int)$status]);
        }

        return $this;
    }

    /**
     * 自动缓存查询结果
     * @param  integer $time 缓存时间,单位为秒,默认为5分钟
     */
    public function cache($time=300)
    {
        $this->cache=$time;
        return $this;
    }

}
