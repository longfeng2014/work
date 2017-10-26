<?php
/**
 * 缓存类, 统一管理缓存
 * 功能 : 统一管理系统缓存, 分配缓存KEY
 *
 * @author tasal<fei.he@pcstars.com>
 * @version $Id: MCache.php 36866 2017-02-27 09:53:33Z A1165 $
 */

namespace common\helpers;

use common\admin\models\WeAccountCopyright;
use Yii;
use common\models\WeAccount;
use common\admin\models\Appstore;
use common\models\Suite;
use modules\redpack\models\RedpackSet;
use modules\redpack\models\RedpackSendLog;
use common\models\Version;
use common\models\DebugLog;

class MCache{

    const KEY_HYPEHN = ':';

    public static function get($key)
    {
        return Yii::$app->cache->get($key);
    }

    public static function set($key,$value,$timeout=0)
    {
        return Yii::$app->cache->set($key,$value,$timeout);
    }
    
    public static function delete($key){
        return Yii::$app->cache->delete($key);
    }

    /**
     * 存储ticket $key为套件u
     */
    public static function ticket($u,$ticket='',$timeout=600){
        $key = 'ticket_'.$u;
        if(empty($ticket)){
            $ticket = static::get($key);
            if(empty($ticket)){
                $ticket = Suite::findOne(['u'=>$u])['suite_ticket'];
            }
            static::set($key, $ticket,$timeout);
            return $ticket;
        }else{
            static::set($key, $ticket,$timeout);
            return $ticket;
        }
    }

    /**
     * 存储suite_access_token 7200 套件应用公用  $key : 套件ID(suite_id)
     * 
     */
    public static function suite_access_token($key,$suite_access_token='' ,$timeout=3600){
        $key = 'suite_access_token_N9c3f6_'.$key;
        if(empty($suite_access_token)){
            return static::get($key);
        }elseif($suite_access_token==='clear'){
            static::delete($key);
            return '';
        }else{
            static::set($key, $suite_access_token,$timeout);
            return $suite_access_token;
        }
    }

    /**
     * 存储预授权码（临时授权码），使用一次后失效  1200 
     */
    public static function preAuthCode($key,$pre_auth_code='',$timeout=600){
        $key = 'pre_auth_code_N9c3f6_'.$key;
        if(empty($pre_auth_code)){
            return static::get($key);
        }elseif($pre_auth_code==='clear'){
            static::delete($key);
            return '';
        }else{
            static::set($key, $pre_auth_code,$timeout);
            return $pre_auth_code;
        }
    }

    /**
     * 根据企业号套件获取到的access_token
     * @param string $permantcode 企业永久授权码
     * @param mix $value 设置的access_token值
     * @param int $timeout 过期时间  微信：7200
     * @return string access_token
     */
    public static function accessToken($permanentCode,$value='',$timeout=6000){
        $key = 'access_token_N9c3f6_'.$permanentCode;
        if(empty($value)) return static::get($key);
        elseif($value==='clear'){
            static::delete($key);
            return '';
        }else{
            static::set($key, $value,$timeout);
            return $value;
        }
    }

    /**
     * 手动绑定企业号，获取到的access_token
     * 函数accessToken($weAccountId,$value='',$timeout=3600) 为企业号套件安装应用获取到的access_token
     * @param string $appid 企业号appid
     * @param mix $value 设置的access_token值
     * @param int $timeout 过期时间
     * @return string access_token
     */
    public static function qyWechatToken($appid,$value='',$timeout=6000)
    {
        $key = 'access_token_appid_'.$appid;
        if(empty($value)) return static::get($key);
        elseif($value==='clear'){
            static::delete($key);
            return '';
        }else{
            static::set($key, $value,$timeout);
            return $value;
        }
    }

    public static function weaccount($xId,$renew=false)
    {
        $key = 'weaccount_N9c3f6_'.$xId;
        if(self::get($key) && !$renew){
            return self::get($key);
        }else{
            if(is_numeric($xId)){
                $weAccountInfo = (new WeAccount())->findOne(['id'=>(int)$xId,'status'=>0]);
            }else{
                $weAccountInfo = (new WeAccount())->findOne(['we_corp_id'=>$xId,'status'=>0]);
            }
            if(!empty($weAccountInfo['we_corp_id'])) self::set($key,$weAccountInfo,60*10);
            return $weAccountInfo;
        }
    }

    public static function redpack($redpack_id,$weAccountId,$keyname){
        $key = $redpack_id.$weAccountId.$keyname;
        if(self::get($key)){
            return self::get($key);
        }else{
            if($keyname=='redpack_redpackid_aid_endtime'){//结束时间
                $sendlog = RedpackSendLog::find()->select('create_time')->where(['redpack_id'=>$redpack_id,'status'=>[1,3]])->orderBy('id desc')->limit(1)->asArray()->one();
                self::set($key,$sendlog['create_time'],3600*24);
                return $create_time;
            }
            if($keyname=='redpack_redpackid_aid_getall'){//红包人员与金额
                $redpackset = (new RedpackSet())->find()->where(['id'=>$redpack_id,'we_account_id'=>$weAccountId,'status'=>[1,3]])->asArray()->one();
                self::set($key,$redpackset,3600*24);
                return $redpackset;
            }
            if($keyname=='redpack_redpackid_aid_sendnum' && $send_num==false){//抢到个数
                $count = RedpackSendLog::find()->where(['redpack_id'=>$redpack_id,'we_account_id'=>$weAccountId,'status'=>[1,3]])->count();
                if($count==false) $count = 0;
                MCache::set($key,$count,3600*24);
                return $count;
            }
            if($keyname=='redpack_redpackid_aid_prizes'){//抽奖
                $rank_num = [];
                $mcache_redpack =  MCache::redpack($redpack_id,$weAccountId,'redpack_redpackid_aid_getall');
                $prizeArr = unserialize($mcache_redpack['benefit_personnel'])['prize'];
                $sendlog = RedpackSendLog::find()->select('money,count(1) as num')->where(['redpack_id'=>$redpack_id,'we_account_id'=>$weAccountId,'status'=>[1,3]])->groupBy('money')->all();
                $count = RedpackSendLog::find()->where(['redpack_id'=>$redpack_id,'we_account_id'=>$weAccountId,'status'=>[1,3]])->count();
                foreach($prizeArr as $val){
                    $rank_num[$val['prize_rank']] = 0;
                }
                if($count!=false){//初始化相应领取的抽奖个数
                    $sendlog = RedpackSendLog::find()->select('money')->where(['redpack_id'=>$redpack_id,'we_account_id'=>$weAccountId,'status'=>[1,3]])->all();
                    //判定级别
                    foreach($sendlog as $val){
                        foreach($prizeArr as $itme){
                            if(floatval($val['money'])==$itme['prize_money']){
                                $rank_num[$itme['prize_rank']] += 1;
                            }
                        }
                    }
                }
                MCache::set($key,$rank_num);
                return $rank_num;
            }
            
        }
    }
    
    public static function appstore($id,$renew=false){
        API_DEBUG && $renew = true;
        $key = 'appstore_N9c3f6_'.$id;
        if(self::get($key) && !$renew){
            return self::get($key);
        }else{
            if(is_numeric($id)){
                $appstore = Appstore::findOne(['id'=>$id]);
            }else{
                $appstore = Appstore::findOne(['type'=>0,'mark'=>$id]);
            }
            if($appstore){
                $suite = self::suite($appstore['be_suite_id']);
                $appstore['token'] = $suite['token'];
                $appstore['u'] = $suite['u'];
                $appstore['suite_id'] = $suite['suite_id'];
                $appstore['suite_secret'] = $suite['suite_secret'];
                $appstore['EncodingAESKey'] = $suite['EncodingAESKey'];
                self::set($key, $appstore,7200);
                return $appstore;
            }
        }
    }
    /**
     * 套件缓存
     * @param string $key  套件u 和 主键ID
     * @param boolean $renew
     * @return Suite or null
     */
    public static function suite($id,$renew=false){
        API_DEBUG && $renew = true;
        $key = 'suite_N9c3f6_'.$id;
        if(static::get($key) && !$renew){
            return self::get($key);
        }else{
            if(is_numeric($id)){
                $suite = Suite::findOne($id);
            }else{
                $suite = Suite::findOne(['u'=>$id]);
            }
            if($suite) self::set($key, $suite);
            return $suite;
        }
    }
    public static function providerAccessToken($value='',$timeout=6000)
    {
        $key = 'provider_access_token_N9c3f6';
        if(empty($value)){
            return self::get($key);
        }else{
            self::set($key, $value,$timeout);
            return $value;
        }
    }

    public static function formKey()
    {
        return static::formKeyArray(func_get_args());
    }

    public static function formKeyWithNamespace($namespace, array $keySegments)
    {
        return $namespace . static::KEY_HYPEHN . static::formKeyArray($keySegments);
    }

    public static function formKeyArray(array $keySegments)
    {
        return implode(static::KEY_HYPEHN, $keySegments);
    }
    /**
     * 企业通讯录调用ticket
     */
    public static function contactTicket($value='', $timeout=3600, $new=false)
    {
        $key = 'open_enterprise_contact_ticket';
        if($new){
            self::delete($key);
            return false;
        }
        if(empty($value)){
            return self::get($key);
        }elseif($value === 'clear'){
            return self::delete($key);
        }else{
            self::set($key, $value, $timeout);
            return $value;
        }
    }
    
    /**
     * 获取企业
     */
    public static function cardTicket($appid,$value='',$timeout=6000)
    {
        if(empty($appid)) return false;
        $key = 'card_ticket'.$appid;
        if(empty($value)){
            return self::get($key);
        }elseif($value === 'clear'){
            return self::delete($key);
        }else{
            self::set($key, $value, $timeout);
            return $value;
        }
    }
    
    /**
     * 单点登录ticket
     * @param $corpidEmailKey corpid.email/mobile
     */
    public static function loginTicket($corpidEmailKey, $value='', $timeout=86000)
    {
        $key = 'sinlt_'.$corpidEmailKey;
        if(empty($corpidEmailKey)) return false;
        if(empty($value)){
            return self::get($key);
        }elseif($value === 'clear'){
            return self::delete($key);
        }else {
            self::set($key, $value, $timeout);
            return $value;
        }
    }

    /**
     * 获取高级版权设置
     * @param $aid
     * @param bool $renew
     */
    public static function weAccountCopyright($aid=false, $renew = false){
        if($aid===false) $aid = Yii::$app->session['weaccount']['id'];
        $key = 'bgy_copyright'.$aid;
        $allKey = 'bgy_version_all';
        //版本权限相关  edit boyue 2016-03-31
        $versionId = (int)Yii::$app->session['weaccount']['version_id'];
        
        if(static::get($allKey) && !$renew){
            $versionList = self::get($allKey);
        }else{
            $versionList = Version::find()->indexBy('id')->asArray()->all();
            self::set($allKey, $versionList, 3600*60);
        }
        if(empty($versionList[$versionId]) || $versionList[$versionId]['copyright'] != 1){
            $copyrightInfo['name'] = SITE_NAME;
            $copyrightInfo['domain_name'] = SITE_DOMAIN_NAME;
            $copyrightInfo['slogan'] = SITE_SLOGAN;
            $copyrightInfo['copyright'] = SITE_COPYRIGHT;
            $copyrightInfo['wxlogo'] = SITE_WXLOGO;
            $copyrightInfo['logo'] = SITE_LOGO;
            self::set($key, $copyrightInfo, 3600*60);
            return $copyrightInfo;
        }else{
            if(static::get($key) && !$renew){
                return self::get($key);
            }else{
                $copyrightInfo = WeAccountCopyright::find()->where(['we_account_id'=>intval($aid)])->asArray()->one();
                $copyrightInfo['setting'] = false;
                if($copyrightInfo){
                    $copyrightInfo['setting'] = true;
                }
                $copyrightInfo['name'] = $copyrightInfo['name']?:SITE_NAME;
                $copyrightInfo['domain_name'] = $copyrightInfo['domain_name']?:SITE_DOMAIN_NAME;
                $copyrightInfo['slogan'] = $copyrightInfo['slogan']?:SITE_SLOGAN;
                $copyrightInfo['copyright'] = $copyrightInfo['copyright']?:SITE_COPYRIGHT;
                $copyrightInfo['wxlogo'] = $copyrightInfo['wxlogo']?:SITE_WXLOGO;
                $copyrightInfo['logo'] = $copyrightInfo['logo']?:SITE_LOGO;
                self::set($key, $copyrightInfo, 3600*60);
                return $copyrightInfo;
            }
        }
    }
}


