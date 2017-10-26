<?php
namespace common\models;
/**
 * 调试日志记录
 * @author Yang Qi <qi.yang@bangongyi.com> 
 *
 */
use common\helpers\Helper;

class DebugLog extends \common\base\MActiveRecord{

    public static function log($weAccountId,$rawMessage,$desc){
        $debugLog = new DebugLog();
        $debugLog->we_account_id = intval($weAccountId);
        $debugLog->raw_message = (is_array($rawMessage) || is_object($rawMessage)) ? json_encode(Helper::objToArr($rawMessage)) : ($rawMessage?:'');
        $debugLog->desc = (is_array($desc) || is_object($desc)) ? json_encode(Helper::objToArr($desc)) : $desc;
        $debugLog->time = time();
        $debugLog->ip = Helper::getIp();
        $debugLog->save();
    }

}
// class DebugLog extends \yii\mongodb\ActiveRecord{
//     public static function collectionName()
//     {
//         return 'dv_debug_log';
//     }
    
//     public static function log($weAccountId,$rawMessage,$desc){
//         $debugLog = new DebugLog();
//         $debugLog->we_account_id = intval($weAccountId);
//         $debugLog->raw_message = $rawMessage;
//         $debugLog->desc = $desc; //描述
//         $debugLog->time = date('Y年m月d日 H:i:s');
//         $debugLog->ip = Helper::getIp();
//         $debugLog->save();
//     }
    
//     public function attributes(){
//         return ['_id','we_account_id', 'raw_message','desc','time','ip'];
//     }
// }