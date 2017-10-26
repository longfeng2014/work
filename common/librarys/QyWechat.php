<?php
/**
 *    微信公众平台企业号PHP-SDK, 官方API类库
 *  @author  binsee <binsee@163.com>
 *  @link https://github.com/binsee/wechat-php-sdk
 *  @version 1.0
 *  usage:
 *   $options = array(
 *            'token'=>'tokenaccesskey', //填写应用接口的Token
 *            'encodingaeskey'=>'encodingaeskey', //填写加密用的EncodingAESKey
 *            'appid'=>'wxdk1234567890', //填写高级调用功能的app id
 *            'appsecret'=>'xxxxxxxxxxxxxxxxxxx', //填写高级调用功能的密钥
 *            'agentid'=>'1', //应用的id
 *            'debug'=>false, //调试开关
 *            '_logcallback'=>'logg', //调试输出方法，需要有一个string类型的参数
 *        );
 *
 */
namespace common\librarys {

    use common\admin\models\Apps;
    use common\helpers\Helper;
    use common\helpers\MCache;
    use common\librarys\Prpcrypt;
    use common\models\DebugLog;
    use Yii;
    use yii\helpers\Json;
    use yii\swiftmailer\Mailer;

    class QyWechat {
        const MSGTYPE_TEXT = 'text';
        const MSGTYPE_IMAGE = 'image';
        const MSGTYPE_LOCATION = 'location';
        const MSGTYPE_LINK = 'link'; //暂不支持
        const MSGTYPE_EVENT = 'event';
        const MSGTYPE_MUSIC = 'music'; //暂不支持
        const MSGTYPE_NEWS = 'news';
        const MSGTYPE_VOICE = 'voice';
        const MSGTYPE_VIDEO = 'video';
        const API_URL_PREFIX = 'https://qyapi.weixin.qq.com/cgi-bin';
        const USER_CREATE_URL = '/user/create?';
        const USER_UPDATE_URL = '/user/update?';
        const USER_DELETE_URL = '/user/delete?';
        const USER_GET_URL = '/user/get?';
        const USER_LIST_URL = '/user/simplelist?';
        const USER_LIST_INFO_URL = '/user/list?';
        const USER_GETINFO_URL = '/user/getuserinfo?';
        const USER_GETDETAIL_URL = '/user/getuserdetail?';
        const DEPARTMENT_CREATE_URL = '/department/create?';
        const DEPARTMENT_UPDATE_URL = '/department/update?';
        const DEPARTMENT_DELETE_URL = '/department/delete?';
        const DEPARTMENT_MOVE_URL = '/department/move?';
        const DEPARTMENT_LIST_URL = '/department/list?';
        const TAG_CREATE_URL = '/tag/create?';
        const TAG_UPDATE_URL = '/tag/update?';
        const TAG_DELETE_URL = '/tag/delete?';
        const TAG_GET_URL = '/tag/get?';
        const TAG_ADDUSER_URL = '/tag/addtagusers?';
        const TAG_DELUSER_URL = '/tag/deltagusers?';
        const TAG_LIST_URL = '/tag/list?';
        const MEDIA_UPLOAD_URL = '/media/upload?';
        const MEDIA_UPLOADIMG_URL = '/media/uploadimg?';
        const MEDIA_GET_URL = '/media/get?';
        const AUTHSUCC_URL = '/user/authsucc?';
        const MASS_SEND_URL = '/message/send?';
        const MENU_CREATE_URL = '/menu/create?';
        const MENU_GET_URL = '/menu/get?';
        const MENU_DELETE_URL = '/menu/delete?';
        const TOKEN_GET_URL = '/gettoken?';
        const OAUTH_PREFIX = 'https://open.weixin.qq.com/connect/oauth2';
        const OAUTH_AUTHORIZE_URL = '/authorize?';
        const TO_OPENID_URL = '/user/convert_to_openid?access_token=';
        const TO_USERID_URL = '/user/convert/to_userid?access_token=';

        const INVOICE_GET_URL = '/card/invoice/reimburse/getinvoiceinfo?access_token=';
        const INVOICE_UPDATE_URL = '/card/invoice/reimburse/updateinvoicestatus?access_token=';

        const CORP_SUITE_TOKEN_GET_URL = '/service/get_suite_token';
        const CORP_TOKEN_GET_URL = '/service/get_corp_token?';
        const CORP_JSAPI_TICKET = '/get_jsapi_ticket?access_token=';
        //设置企业号应用
        const CORP_SET_AGENT = '/service/set_agent?suite_access_token=';
        const CORP_GET_AUTH_INFO = '/service/get_auth_info?suite_access_token=';
        //获取摇周边的设备及用户信息
        const SHAKEAROUND_GETSHAKEINFO = '/shakearound/getshakeinfo?access_token=';
        //邀请关注
        const INVITE_SEND_URL = '/invite/send?access_token=';
        //企业号jsapi 选择企业联系人ticket
        const TICKET_GET_URL = '/ticket/get?access_token=';
        // 分页拉取数据
        const GET_PAGE_URL = '/sync/getpage?access_token=';
        // 指纹验签
        const VERIFY_SIGNATURE = '/soter/verify_signature?access_token=';

        //设置为通讯录套件
        const MARK_AS_CONTACTS_SUITE = '/service/mark_as_contacts_suite?suite_access_token=';

        private $token;
        private $encodingAesKey;
        private $appid; //也就是企业号的CorpID
        private $appsecret;
        private $oauth_scope; //OAuth 授权范围
        public $access_token;
        private $agentid; //应用id   AgentID
        private $postxml;
        private $agentidxml; //接收的应用id   AgentID
        private $_msg;
        private $_receive;
        private $_sendmsg; //主动发送消息的内容
        private $_text_filter = true;
        public $debug = false;
        public $errCode = 40001;
        public $errMsg = "no access";
        private $_logcallback;

        private $suite_id;
        private $suite_secret;
        private $u;
        private $ticket;

        private $auth_corpid;
        public $permanent_code;
        private $suite_access_token;

        public $isCallbackMode = false;

        public function __construct($options) {
            $cache = 120;
            $select = 'we_account_id,appstore_id,we_agent_id,we_permanent_code,is_callback_mode,secret';
            //此方法在新通讯录体系里已废弃 ，必须使用各自应用来操作应用的通讯录,切换套件到新版后，会删除此方法
            if (isset($options['aid']) && $options['aid'] > 0 && empty($options['appstore_id'])) {
                /*
                $apps = Apps::find()->select($select)
                ->where(['we_account_id'=>$options['aid'],'status'=>0])
                ->andWhere("we_department_id like ',1,%'")
                ->cache($cache)
                ->one();
                if(empty($apps)){
                $apps = Apps::find()->select($select)
                ->where(['we_account_id'=>$options['aid'],'status'=>0])
                ->cache($cache)
                ->one();
                }
                $options['bgy_app_id'] = $apps;
                 */
                //新版通讯录权限，如果要操作员工，必须有通讯录的权限，而且须有通讯录的读写权限
                $moduleId = Yii::$app->controller->module->id;
                if ($moduleId == 'admin') {
                    $moduleId = Yii::$app->controller->module->module->id;
                }
                if (empty($moduleId)) {
                    $moduleId = explode('/', Yii::$app->request->getUrl())[1];
                    if (strpos($moduleId, '?') !== false) {
                        $moduleId = explode('?', $moduleId)[0];
                    }
                }
                if ($moduleId != 'common' && $moduleId != 'console') {
                    $appstore = MCache::appstore($moduleId);
                    $this->oauth_scope = $appstore['oauth_scope'];
                    if ($appstore['id'] > 0) {
                        $apps = Apps::find()->select($select)->where(['we_account_id' => $options['aid'], 'appstore_id' => $appstore['id'], 'status' => 0])->cache($cache)->one();
                    }
                }
                if (empty($apps)) {
                    $apps = Apps::find()->select($select)
                        ->where(['we_account_id' => $options['aid'], 'appstore_id' => [12, 57], 'status' => 0])
                        ->cache($cache)
                        ->one();
                }
                if (empty($apps)) {
                    $apps = Apps::find()->select($select)
                        ->where(['we_account_id' => $options['aid'], 'status' => 0])
                        ->cache($cache)
                        ->one();
                }
                if (empty($apps)) {
                    DebugLog::log($options['aid'], 'is not installed address app,can not oprate address.' . $moduleId . '==' . $apps['we_agent_id'] . '==' . $appstore['id'], Yii::$app->request->getIsConsoleRequest() ? 'CMD:' . implode(' ', Yii::$app->request->getParams()) : 'URL:' . Yii::$app->request->absoluteUrl);
                    return false;
                }
                $options['bgy_app_id'] = $apps;
            } elseif ($options['appstore_id'] > 0) {
                $apps = Apps::find()->select($select)
                    ->where(['we_account_id' => $options['aid'], 'appstore_id' => $options['appstore_id'], 'status' => 0])
                    ->cache($cache)
                    ->one();
                $options['bgy_app_id'] = $apps;
            }
            //企业号套件用法   bgy_app_id 可以是ID号，也可以是Apps对象或者数组
            if (isset($options['bgy_app_id'])) {
                if (is_numeric($options['bgy_app_id'])) {
                    $apps = Apps::find()->select($select)
                        ->where(['id' => $options['bgy_app_id']])
                        ->cache($cache)
                        ->one();
                } else {
                    $apps = $options['bgy_app_id'];
                }
                if (empty($apps)) {
                    DebugLog::log(0, $options['bgy_app_id'], 'bgy_app_id=' . $options['bgy_app_id']);
                    return false;
                }
                if (Yii::$app->session['weaccount']) {
                    $weAccount = Yii::$app->session['weaccount'];
                } else {
                    $weAccount = MCache::weaccount($apps['we_account_id']);
                }
                // 回调模式设置参数
                if ($apps['is_callback_mode'] == 1) {
                    $this->initParamForCallback($apps, $weAccount['we_corp_id'], $weAccount['appsecret']);
                    return;
                }
                //套件相关参数
                $appstore = MCache::appstore($apps['appstore_id']);
                $suite = MCache::suite($appstore['be_suite_id']);
                $this->oauth_scope = $appstore['oauth_scope'];
                $this->suite_id = $options['suite_id'] ?: $suite['suite_id'];
                $this->token = $options['token'] ?: $suite['token'];
                $this->encodingAesKey = $options['EncodingAESKey'] ?: $suite['EncodingAESKey'];
                $this->suite_secret = $options['suite_secret'] ?: $suite['suite_secret'];
                $this->appsecret = $options['suite_secret'] ?: $suite['suite_secret'];
                $this->u = $options['u'] ?: $suite['u'];
                //企业相关参数
                $this->appid = $this->auth_corpid = $options['auth_corpid'] ?: $weAccount['we_corp_id'];
                $this->permanent_code = $options['permanent_code'] ?: $apps['we_permanent_code'];
                $this->agentid = $apps['we_agent_id'];
                //授权
                $this->ticket = MCache::ticket($this->u);
                $this->suite_access_token = $this->getSuiteAccessToken();
                $this->access_token = $this->getAccessToken($this->permanent_code);
                //企业号常规用法
            } else {
                if ($options['u']) {
                    $suite = MCache::suite($options['u']);
                    $this->u = $options['u'] ?: $suite['u'];

                    $this->suite_id = $suite['suite_id'] ?: '';
                    $this->token = $suite['token'] ?: '';
                    $this->encodingAesKey = $suite['EncodingAESKey'] ?: '';
                    $this->suite_secret = $this->appsecret = $options['appsecret'] ?: $suite['suite_secret'];

                    $this->auth_corpid = $this->appid = $options['appid'] ?: $suite['suite_id'];
                    $this->agentid = $options['agentid'] ?: 0;
                    $this->permanent_code = $options['permanent_code'] ?: '';
                    $this->debug = $options['debug'] ?: false;
                    $this->_logcallback = $options['logcallback'] ?: false;

                    //授权
                    $this->ticket = MCache::ticket($this->u);
                    $this->suite_access_token = $this->getSuiteAccessToken();
                } else {
                    $this->suite_id = $options['appid'] ?: '';
                    $this->token = $options['token'] ?: '';
                    $this->encodingAesKey = $options['encodingaeskey'] ?: '';
                    $this->auth_corpid = $this->appid = $options['appid'] ?: '';
                    $this->suite_secret = $this->appsecret = $options['appsecret'] ?: '';
                    $this->agentid = $options['agentid'] ?: 0;
                    $this->permanent_code = $options['permanent_code'] ?: '';
                    $this->u = $options['u'] ?: '';
                    $this->debug = $options['debug'] ?: false;
                    $this->_logcallback = $options['logcallback'] ?: false;
                }
            }
        }

        /**
         * 回调模式参数设置
         */
        public function initParamForCallback($app, $appid, $secret) {
            $this->isCallbackMode = true;
            $this->token = $app['token'];
            $this->encodingAesKey = $app['encoding_aeskey'];
            $this->appid = $appid;
            $this->appsecret = $app['secret'];
            $this->agentid = $app['we_agent_id'];
        }

        private function log($log) {
            if ($this->debug && function_exists($this->_logcallback)) {
                if (is_array($log)) {
                    $log = print_r($log, true);
                }

                return call_user_func($this->_logcallback, $log);
            }
        }

        /**
         * 数据XML编码
         * @param mixed $data 数据
         * @return string
         */
        public static function dataToXml($data) {
            $xml = '';
            foreach ($data as $key => $val) {
                is_numeric($key) && $key = "item id=\"$key\"";
                $xml .= "<$key>";
                $xml .= (is_array($val) || is_object($val)) ? self::dataToXml($val) : self::xmlSafeStr($val);
                list($key) = explode(' ', $key);
                $xml .= "</$key>";
            }
            return $xml;
        }

        public static function xmlSafeStr($str) {
            return '<![CDATA[' . preg_replace("/[\\x00-\\x08\\x0b-\\x0c\\x0e-\\x1f]/", '', $str) . ']]>';
        }

        /**
         * XML编码
         * @param mixed $data 数据
         * @param string $root 根节点名
         * @param string $item 数字索引的子节点名
         * @param string $attr 根节点属性
         * @param string $id   数字索引子节点key转换的属性名
         * @param string $encoding 数据编码
         * @return string
         */
        public function xmlEncode($data, $root = 'xml', $item = 'item', $attr = '', $id = 'id', $encoding = 'utf-8') {
            if (is_array($attr)) {
                $_attr = array();
                foreach ($attr as $key => $value) {
                    $_attr[] = "{$key}=\"{$value}\"";
                }
                $attr = implode(' ', $_attr);
            }
            $attr = trim($attr);
            $attr = empty($attr) ? '' : " {$attr}";
            $xml = "<{$root}{$attr}>";
            $xml .= self::dataToXml($data, $item, $id);
            $xml .= "</{$root}>";
            return $xml;
        }

        /**
         * 微信api不支持中文转义的json结构
         * @param array $arr
         */
        static function jsonEncode($arr) {
            $parts = array();
            $is_list = false;
            //Find out if the given array is a numerical array
            $keys = array_keys($arr);
            $max_length = count($arr) - 1;
            if (($keys[0] === 0) && ($keys[$max_length] === $max_length)) {
                //See if the first key is 0 and last key is length - 1
                $is_list = true;
                for ($i = 0; $i < count($keys); $i++) {
                    //See if each key correspondes to its position
                    if ($i != $keys[$i]) {
                        //A key fails at position check.
                        $is_list = false; //It is an associative array.
                        break;
                    }
                }
            }
            foreach ($arr as $key => $value) {
                if (is_array($value)) {
                    //Custom handling for arrays
                    if ($is_list) {
                        $parts[] = self::jsonEncode($value);
                    }
                    /* :RECURSION: */
                    else {
                        $parts[] = '"' . $key . '":' . self::jsonEncode($value);
                    }
                    /* :RECURSION: */
                } else {
                    $str = '';
                    if (!$is_list) {
                        $str = '"' . $key . '":';
                    }

                    //Custom handling for multiple data types
                    if (is_numeric($value) && $value < 2000000000) {
                        $str .= $value;
                    }
                    //Numbers
                    elseif ($value === false) {
                        $str .= 'false';
                    }
                    //The booleans
                    elseif ($value === true) {
                        $str .= 'true';
                    } else {
                        $str .= '"' . addslashes($value) . '"';
                    }
                    //All other things
                    // :TODO: Is there any more datatype we should be in the lookout for? (Object?)
                    $parts[] = $str;
                }
            }
            $json = implode(',', $parts);
            if ($is_list) {
                return '[' . $json . ']';
            }
            //Return numerical JSON
            return '{' . $json . '}'; //Return associative JSON
        }

        /**
         * 过滤文字回复\r\n换行符
         * @param string $text
         * @return string|mixed
         */
        private function _auto_text_filter($text) {
            if (!$this->_text_filter) {
                return $text;
            }

            return str_replace("\r\n", "\n", $text);
        }

        /**
         * GET 请求
         * @param string $url
         */
        private function httpGet($url) {

            $oCurl = curl_init();
            if (stripos($url, "https://") !== FALSE) {
                curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
                curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, FALSE);
                curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
                //curl_setopt($oCurl, CURLOPT_HEADER, 0);
                curl_setopt($oCurl, CURLOPT_HTTPHEADER, ["Host: qyapi.weixin.qq.com"]);
            }
            curl_setopt($oCurl, CURLOPT_URL, $url);
            curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
            // 设置超时2秒
            curl_setopt($oCurl, CURLOPT_CONNECTTIMEOUT, 2);
            curl_setopt($oCurl, CURLOPT_TIMEOUT, 10);
            $sContent = curl_exec($oCurl);
            $aStatus = curl_getinfo($oCurl);
            $errno = curl_errno($oCurl);
            $error = curl_error($oCurl);
            curl_close($oCurl);
            //请求日志记录
            API_TRACE && \common\models\WechatApiLog::logSave(intval(Yii::$app->session['weaccount']['id']), $this->appid, $this->suite_id, $this->agentid, $url, '', $sContent, $errno, $error);
            if (intval($aStatus["http_code"]) == 200) {
                return $sContent;
            }
            return false;
        }

        /**
         * POST 请求
         * @param string $url
         * @param array $param
         * @param boolean $post_file 是否文件上传
         * @return string content
         */
        private function httpPost($url, $param, $post_file = false) {
            $oCurl = curl_init();
            if (stripos($url, "https://") !== FALSE) {
                curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
                curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
                //curl_setopt($oCurl, CURLOPT_HEADER, 0);
                curl_setopt($oCurl, CURLOPT_HTTPHEADER, ["Host: qyapi.weixin.qq.com"]);
            }
            if (is_string($param) || $post_file) {
                $strPOST = $param;
            } else {
                $aPOST = array();
                foreach ($param as $key => $val) {
                    $aPOST[] = $key . "=" . urlencode($val);
                }
                $strPOST = join("&", $aPOST);
            }
            curl_setopt($oCurl, CURLOPT_URL, $url);
            curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($oCurl, CURLOPT_POST, true);
            curl_setopt($oCurl, CURLOPT_POSTFIELDS, $strPOST);
            // 设置超时2秒
            curl_setopt($oCurl, CURLOPT_CONNECTTIMEOUT, 2);
            curl_setopt($oCurl, CURLOPT_TIMEOUT, 10);
            $sContent = curl_exec($oCurl);
            $aStatus = curl_getinfo($oCurl);
            $errno = curl_errno($oCurl);
            $error = curl_error($oCurl);
            curl_close($oCurl);
            API_TRACE && \common\models\WechatApiLog::logSave(intval(Yii::$app->session['weaccount']['id']), $this->appid, $this->suite_id, $this->agentid, $url, $param, $sContent, $errno, $error);
            if (intval($aStatus["http_code"]) == 200) {
                return $sContent;
            }
            return false;
        }

        /**
         * For weixin server validation
         */
        private function checkSignature($str) {
            $signature = isset($_GET["msg_signature"]) ? $_GET["msg_signature"] : '';
            $timestamp = isset($_GET["timestamp"]) ? $_GET["timestamp"] : '';
            $nonce = isset($_GET["nonce"]) ? $_GET["nonce"] : '';
            $tmpArr = array($str, $this->token, $timestamp, $nonce); //比普通公众平台多了一个加密的密文
            sort($tmpArr, SORT_STRING);
            $tmpStr = implode($tmpArr);
            $shaStr = sha1($tmpStr);
            if ($shaStr == $signature) {
                return true;
            } else {
                return false;
            }
        }

        /**
         * 微信验证，包括post来的xml解密
         * @param bool $return 是否返回
         */
        public function valid($return = false) {
            $encryptStr = "";
            if ($_SERVER['REQUEST_METHOD'] == "POST") {
                $postStr = file_get_contents("php://input");
                $array = (array) simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
                $this->log($postStr);
                if (isset($array['Encrypt'])) {
                    $encryptStr = $array['Encrypt'];
                    $this->agentidxml = isset($array['AgentID']) ? $array['AgentID'] : '';
                }
            } else {
                $encryptStr = isset($_GET["echostr"]) ? $_GET["echostr"] : '';
            }
            if ($encryptStr) {
                $ret = $this->checkSignature($encryptStr);
            }
            if (!isset($ret) || !$ret) {
                if (!$return) {
                    die('no access');
                } else {
                    return false;
                }
            }
            $pc = new Prpcrypt($this->encodingAesKey);
            $array = $pc->decrypt($encryptStr, $this->appid);
            if (!isset($array[0]) || ($array[0] != 0)) {
                if (!$return) {
                    die('解密失败！');
                } else {
                    return false;
                }
            }
            if ($_SERVER['REQUEST_METHOD'] == "POST") {
                $this->postxml = $array[1];
                //$this->log($array[1]);
                return ($this->postxml != "");
            } else {
                $echoStr = $array[1];
                if ($return) {
                    return $echoStr;
                } else {
                    die($echoStr);
                }
            }
            return false;
        }

        /**
         * 获取微信服务器发来的信息
         */
        public function getRev() {
            if ($this->_receive) {
                return $this;
            }

            $postStr = $this->postxml;
            $this->log($postStr);
            if (!empty($postStr)) {
                $this->_receive = (array) simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
                if (!isset($this->_receive['AgentID'])) {
                    $this->_receive['AgentID'] = $this->agentidxml; //当前接收消息的应用id
                }
            }
            return $this;
        }

        /**
         * 获取微信服务器发来的信息
         */
        public function getRevData() {
            return $this->_receive;
        }

        /**
         * 获取微信服务器发来的原始加密信息
         */
        public function getRevPostXml() {
            return $this->postxml;
        }

        /**
         * 获取消息发送者
         */
        public function getRevFrom() {
            if (isset($this->_receive['FromUserName'])) {
                return $this->_receive['FromUserName'];
            } else {
                return false;
            }

        }

        /**
         * 获取消息接受者
         */
        public function getRevTo() {
            if (isset($this->_receive['ToUserName'])) {
                return $this->_receive['ToUserName'];
            } else {
                return false;
            }

        }

        /**
         * 获取接收消息的应用id
         */
        public function getRevAgentID() {
            if (isset($this->_receive['AgentID'])) {
                return $this->_receive['AgentID'];
            } else {
                return false;
            }

        }

        /**
         * 获取接收消息的类型
         */
        public function getRevType() {
            if (isset($this->_receive['MsgType'])) {
                return $this->_receive['MsgType'];
            } else {
                return false;
            }

        }

        /**
         * 获取消息ID
         */
        public function getRevID() {
            if (isset($this->_receive['MsgId'])) {
                return $this->_receive['MsgId'];
            } else {
                return false;
            }

        }

        /**
         * 获取消息发送时间
         */
        public function getRevCtime() {
            if (isset($this->_receive['CreateTime'])) {
                return $this->_receive['CreateTime'];
            } else {
                return false;
            }

        }

        /**
         * 获取接收消息内容正文
         */
        public function getRevContent() {
            if (isset($this->_receive['Content'])) {
                return $this->_receive['Content'];
            } else {
                return false;
            }

        }

        /**
         * 获取接收消息图片
         */
        public function getRevPic() {
            if (isset($this->_receive['PicUrl'])) {
                return array(
                    'mediaid' => $this->_receive['MediaId'],
                    'picurl' => (string) $this->_receive['PicUrl'], //防止picurl为空导致解析出错
                );
            } else {
                return false;
            }

        }

        /**
         * 获取接收地理位置
         */
        public function getRevGeo() {
            if (isset($this->_receive['Location_X'])) {
                return array(
                    'x' => $this->_receive['Location_X'],
                    'y' => $this->_receive['Location_Y'],
                    'scale' => (string) $this->_receive['Scale'],
                    'label' => (string) $this->_receive['Label'],
                );
            } else {
                return false;
            }

        }

        /**
         * 获取上报地理位置事件
         */
        public function getRevEventGeo() {
            if (isset($this->_receive['Latitude'])) {
                return array(
                    'x' => $this->_receive['Latitude'],
                    'y' => $this->_receive['Longitude'],
                    'precision' => $this->_receive['Precision'],
                );
            } else {
                return false;
            }

        }

        /**
         * 获取接收事件推送
         */
        public function getRevEvent() {
            if (isset($this->_receive['Event'])) {
                $array['event'] = $this->_receive['Event'];
            }
            if (isset($this->_receive['EventKey'])) {
                $array['key'] = $this->_receive['EventKey'];
            }
            if (isset($array) && count($array) > 0) {
                return $array;
            } else {
                return false;
            }
        }

        /**
         * 获取自定义菜单的扫码推事件信息
         *
         * 事件类型为以下两种时则调用此方法有效
         * Event     事件类型，scancode_push
         * Event     事件类型，scancode_waitmsg
         *
         * @return: array | false
         * array (
         *     'ScanType'=>'qrcode',
         *     'ScanResult'=>'123123'
         * )
         */
        public function getRevScanInfo() {
            if (isset($this->_receive['ScanCodeInfo'])) {
                if (!is_array($this->_receive['SendPicsInfo'])) {
                    $array = (array) $this->_receive['ScanCodeInfo'];
                    $this->_receive['ScanCodeInfo'] = $array;
                } else {
                    $array = $this->_receive['ScanCodeInfo'];
                }
            }
            if (isset($array) && count($array) > 0) {
                return $array;
            } else {
                return false;
            }
        }

        /**
         * 获取自定义菜单的图片发送事件信息
         *
         * 事件类型为以下三种时则调用此方法有效
         * Event     事件类型，pic_sysphoto        弹出系统拍照发图的事件推送
         * Event     事件类型，pic_photo_or_album  弹出拍照或者相册发图的事件推送
         * Event     事件类型，pic_weixin          弹出微信相册发图器的事件推送
         *
         * @return: array | false
         * array (
         *   'Count' => '2',
         *   'PicList' =>array (
         *         'item' =>array (
         *             0 =>array ('PicMd5Sum' => 'aaae42617cf2a14342d96005af53624c'),
         *             1 =>array ('PicMd5Sum' => '149bd39e296860a2adc2f1bb81616ff8'),
         *         ),
         *   ),
         * )
         *
         */
        public function getRevSendPicsInfo() {
            if (isset($this->_receive['SendPicsInfo'])) {
                if (!is_array($this->_receive['SendPicsInfo'])) {
                    $array = (array) $this->_receive['SendPicsInfo'];
                    if (isset($array['PicList'])) {
                        $array['PicList'] = (array) $array['PicList'];
                        $item = $array['PicList']['item'];
                        $array['PicList']['item'] = array();
                        foreach ($item as $key => $value) {
                            $array['PicList']['item'][$key] = (array) $value;
                        }
                    }
                    $this->_receive['SendPicsInfo'] = $array;
                } else {
                    $array = $this->_receive['SendPicsInfo'];
                }
            }
            if (isset($array) && count($array) > 0) {
                return $array;
            } else {
                return false;
            }
        }

        /**
         * 获取自定义菜单的地理位置选择器事件推送
         *
         * 事件类型为以下时则可以调用此方法有效
         * Event     事件类型，location_select        弹出系统拍照发图的事件推送
         *
         * @return: array | false
         * array (
         *   'Location_X' => '33.731655000061',
         *   'Location_Y' => '113.29955200008047',
         *   'Scale' => '16',
         *   'Label' => '某某市某某区某某路',
         *   'Poiname' => '',
         * )
         *
         */
        public function getRevSendGeoInfo() {
            if (isset($this->_receive['SendLocationInfo'])) {
                if (!is_array($this->_receive['SendLocationInfo'])) {
                    $array = (array) $this->_receive['SendLocationInfo'];
                    if (empty($array['Poiname'])) {
                        $array['Poiname'] = "";
                    }
                    if (empty($array['Label'])) {
                        $array['Label'] = "";
                    }
                    $this->_receive['SendLocationInfo'] = $array;
                } else {
                    $array = $this->_receive['SendLocationInfo'];
                }
            }
            if (isset($array) && count($array) > 0) {
                return $array;
            } else {
                return false;
            }
        }

        /**
         * 获取接收语音推送
         */
        public function getRevVoice() {
            if (isset($this->_receive['MediaId'])) {
                return array(
                    'mediaid' => $this->_receive['MediaId'],
                    'format' => $this->_receive['Format'],
                );
            } else {
                return false;
            }

        }

        /**
         * 获取接收视频推送
         */
        public function getRevVideo() {
            if (isset($this->_receive['MediaId'])) {
                return array(
                    'mediaid' => $this->_receive['MediaId'],
                    'thumbmediaid' => $this->_receive['ThumbMediaId'],
                );
            } else {
                return false;
            }

        }

        /**
         * 设置回复消息
         * Example: $obj->text('hello')->reply();
         * @param string $text
         */
        public function text($text = '') {
            $msg = array(
                'ToUserName' => $this->getRevFrom(),
                'FromUserName' => $this->getRevTo(),
                'MsgType' => self::MSGTYPE_TEXT,
                'Content' => $this->_auto_text_filter($text),
                'CreateTime' => time(),
            );
            $this->Message($msg);
            return $this;
        }

        /**
         * 设置回复消息
         * Example: $obj->image('media_id')->reply();
         * @param string $mediaid
         */
        public function image($mediaid = '') {
            $msg = array(
                'ToUserName' => $this->getRevFrom(),
                'FromUserName' => $this->getRevTo(),
                'MsgType' => self::MSGTYPE_IMAGE,
                'Image' => array('MediaId' => $mediaid),
                'CreateTime' => time(),
            );
            $this->Message($msg);
            return $this;
        }

        /**
         * 设置回复消息
         * Example: $obj->voice('media_id')->reply();
         * @param string $mediaid
         */
        public function voice($mediaid = '') {
            $msg = array(
                'ToUserName' => $this->getRevFrom(),
                'FromUserName' => $this->getRevTo(),
                'MsgType' => self::MSGTYPE_IMAGE,
                'Voice' => array('MediaId' => $mediaid),
                'CreateTime' => time(),
            );
            $this->Message($msg);
            return $this;
        }

        /**
         * 设置回复消息
         * Example: $obj->video('media_id','title','description')->reply();
         * @param string $mediaid
         */
        public function video($mediaid = '', $title = '', $description = '') {
            $msg = array(
                'ToUserName' => $this->getRevFrom(),
                'FromUserName' => $this->getRevTo(),
                'MsgType' => self::MSGTYPE_IMAGE,
                'Video' => array(
                    'MediaId' => $mediaid,
                    'Title' => $title,
                    'Description' => $description,
                ),
                'CreateTime' => time(),
            );
            $this->Message($msg);
            return $this;
        }

        /**
         * 设置回复图文
         * @param array $newsData
         * 数组结构:
         *  array(
         *      "0"=>array(
         *          'Title'=>'msg title',
         *          'Description'=>'summary text',
         *          'PicUrl'=>'http://www.domain.com/1.jpg',
         *          'Url'=>'http://www.domain.com/1.html'
         *      ),
         *      "1"=>....
         *  )
         */
        public function news($newsData = array()) {

            $count = count($newsData);

            $msg = array(
                'ToUserName' => $this->getRevFrom(),
                'FromUserName' => $this->getRevTo(),
                'MsgType' => self::MSGTYPE_NEWS,
                'CreateTime' => time(),
                'ArticleCount' => $count,
                'Articles' => $newsData,

            );
            $this->Message($msg);
            return $this;
        }

        /**
         * 设置发送消息
         * @param array $msg 消息数组
         * @param bool $append 是否在原消息数组追加
         */
        public function Message($msg = '', $append = false) {
            if (is_null($msg)) {
                $this->_msg = array();
            } elseif (is_array($msg)) {
                if ($append) {
                    $this->_msg = array_merge($this->_msg, $msg);
                } else {
                    $this->_msg = $msg;
                }

                return $this->_msg;
            } else {
                return $this->_msg;
            }
        }

        /**
         *
         * 回复微信服务器, 此函数支持链式操作
         * Example: $this->text('msg tips')->reply();
         * @param string $msg 要发送的信息, 默认取$this->_msg
         * @param bool $return 是否返回信息而不抛出到浏览器 默认:否
         */
        public function reply($msg = array(), $return = false) {
            if (empty($msg)) {
                $msg = $this->_msg;
            }

            $xmldata = $this->xmlEncode($msg);
            $this->log($xmldata);
            $pc = new Prpcrypt($this->encodingAesKey);
            $array = $pc->encrypt($xmldata, $this->appid);
            $ret = $array[0];
            if ($ret != 0) {
                $this->log('encrypt err!');
                return false;
            }
            $timestamp = time();
            $nonce = rand(77, 999) * rand(605, 888) * rand(11, 99);
            $encrypt = $array[1];
            $tmpArr = array($this->token, $timestamp, $nonce, $encrypt); //比普通公众平台多了一个加密的密文
            sort($tmpArr, SORT_STRING);
            $signature = implode($tmpArr);
            $signature = sha1($signature);
            $smsg = $this->generate($encrypt, $signature, $timestamp, $nonce);
            $this->log($smsg);
            if ($return) {
                return $smsg;
            } elseif ($smsg) {
                echo $smsg;
                return true;

            } else {
                return false;
            }

        }

        private function generate($encrypt, $signature, $timestamp, $nonce) {
            //格式化加密信息
            $format = "<xml>
<Encrypt><![CDATA[%s]]></Encrypt>
<MsgSignature><![CDATA[%s]]></MsgSignature>
<TimeStamp>%s</TimeStamp>
<Nonce><![CDATA[%s]]></Nonce>
</xml>";
            return sprintf($format, $encrypt, $signature, $timestamp, $nonce);
        }

        /**
         * 获取套件suite_access_token
         */
        public function getSuiteAccessToken($renew = false) {
            $this->suite_access_token = MCache::suite_access_token($this->suite_id);
            if (empty($this->suite_access_token) || $renew) {
                $params = [
                    "suite_id" => $this->suite_id,
                    "suite_secret" => $this->suite_secret,
                    "suite_ticket" => $this->ticket,
                ];
                $result = $this->httpPost(self::API_URL_PREFIX . self::CORP_SUITE_TOKEN_GET_URL, Json::encode($params));
                if ($result) {
                    $result = Json::decode($result, true);
                    if (!$result || !isset($result['suite_access_token'])) {
                        $this->errCode = $result['errcode'];
                        $this->errMsg = $result['errmsg'];
                        return '';
                    }
                    $this->suite_access_token = $result['suite_access_token'];
                    $expire = $result['expires_in'] ? intval($result['expires_in']) - 1000 : 1800;
                    MCache::suite_access_token($this->suite_id, $this->suite_access_token, $expire);
                    return $this->suite_access_token;
                }
            }
            return $this->suite_access_token;
        }

        /**
         * 获取应用套件access_token
         */
        public function getAccessToken($permanentCode, $renew = false) {
            if ($this->isCallbackMode) {
                $this->getAccessTokenForCallback();
                return $this->access_token;
            }
            $this->access_token = MCache::accessToken($permanentCode);
            if (empty($this->access_token) || $renew) {
                $params = [
                    "suite_id" => $this->suite_id,
                    "auth_corpid" => $this->auth_corpid,
                    "permanent_code" => $this->permanent_code,
                ];
                $result = $this->httpPost(self::API_URL_PREFIX . self::CORP_TOKEN_GET_URL . 'suite_access_token=' . $this->suite_access_token, Json::encode($params));
                if ($result) {
                    $result = Json::decode($result, true);
                    if (!$result || !isset($result['access_token'])) {
                        $this->errCode = $result['errcode'];
                        $this->errMsg = $result['errmsg'];
                        return '';
                    }
                    $this->access_token = $result['access_token'];
                    $expire = $result['expires_in'] ? intval($result['expires_in']) - 600 : 3600;
                    MCache::accessToken($this->permanent_code, $this->access_token, $expire);
                    return $this->access_token;
                }
            }
            return $this->access_token;
        }

        /**
         * 回调模式获取access_token
         */
        public function getAccessTokenForCallback() {
            $this->access_token = MCache::qyWechatToken($this->appid);
            if ($this->access_token) {
                return $this->access_token;
            }
            $result = $this->httpGet(self::API_URL_PREFIX . self::TOKEN_GET_URL . 'corpid=' . $this->appid . '&corpsecret=' . $this->appsecret);
            if ($result) {
                $json = Json::decode($result, true);
                if (!$json || isset($json['errcode'])) {
                    $this->errCode = $json['errcode'];
                    $this->errMsg = $json['errmsg'];
                    return false;
                }
                $this->access_token = $json['access_token'];
                $expire = $json['expires_in'] ? intval($json['expires_in']) - 1200 : 3600;
                MCache::qyWechatToken($this->appid, $this->access_token, $expire);
                return $this->access_token;
            }
        }
        /**
         * 通用auth验证方法
         * @param string $appid
         * @param string $appsecret
         * @param string $token 手动指定access_token，非必要情况不建议用
         */
        public function checkAuth($appid = '', $appsecret = '', $token = '') {
            //套件方式验证
            if ($this->permanent_code) {
                return $this->getAccessToken($this->permanent_code);
            }
            if (!$appid || !$appsecret) {
                $appid = $this->appid;
                $appsecret = $this->appsecret;
            }
            if ($token) {
                //手动指定token，优先使用
                $this->access_token = $token;
                return $this->access_token;
            }
            if (MCache::qyWechatToken($appid)) {
                $this->access_token = MCache::qyWechatToken($appid);
                API_DEBUG && Yii::log('获取授权(缓存):appid=' . $appid . '|appsecret=' . $appsecret . '|token' . $this->token . '|access_token=' . $this->access_token);
                return $this->access_token;
            } else {
                $result = $this->httpGet(self::API_URL_PREFIX . self::TOKEN_GET_URL . 'corpid=' . $appid . '&corpsecret=' . $appsecret);
                if ($result) {
                    $json = Json::decode($result, true);
                    if (!$json || !empty($json['errcode'])) {
                        $this->errCode = $json['errcode'];
                        $this->errMsg = $json['errmsg'];
                        return false;
                    }
                    $this->access_token = $json['access_token'];
                    $expire = $json['expires_in'] ? intval($json['expires_in']) - 100 : 3600;
                    MCache::qyWechatToken($appid, $this->access_token, $expire);
                    return $this->access_token;
                }
            }
            return false;
        }

        /**
         * 删除验证数据
         * @param string $appid
         */
        public function resetAuth($appid = '') {
            if (!$appid) {
                $appid = $this->appid;
            }

            $this->access_token = '';
            MCache::qyWechatToken($appid, 'clear');
            return true;
        }

        /**
         * 创建菜单
         * @param array $data 菜单数组数据
         * example:
         *     array (
         *         'button' => array (
         *           0 => array (
         *             'name' => '扫码',
         *             'sub_button' => array (
         *                 0 => array (
         *                   'type' => 'scancode_waitmsg',
         *                   'name' => '扫码带提示',
         *                   'key' => 'rselfmenu_0_0',
         *                 ),
         *                 1 => array (
         *                   'type' => 'scancode_push',
         *                   'name' => '扫码推事件',
         *                   'key' => 'rselfmenu_0_1',
         *                 ),
         *             ),
         *           ),
         *           1 => array (
         *             'name' => '发图',
         *             'sub_button' => array (
         *                 0 => array (
         *                   'type' => 'pic_sysphoto',
         *                   'name' => '系统拍照发图',
         *                   'key' => 'rselfmenu_1_0',
         *                 ),
         *                 1 => array (
         *                   'type' => 'pic_photo_or_album',
         *                   'name' => '拍照或者相册发图',
         *                   'key' => 'rselfmenu_1_1',
         *                 )
         *             ),
         *           ),
         *           2 => array (
         *             'type' => 'location_select',
         *             'name' => '发送位置',
         *             'key' => 'rselfmenu_2_0'
         *           ),
         *         ),
         *     )
         * type可以选择为以下几种，会收到相应类型的事件推送。请注意，3到8的所有事件，仅支持微信iPhone5.4.1以上版本，
         * 和Android5.4以上版本的微信用户，旧版本微信用户点击后将没有回应，开发者也不能正常接收到事件推送。
         * 1、click：点击推事件
         * 2、view：跳转URL
         * 3、scancode_push：扫码推事件
         * 4、scancode_waitmsg：扫码推事件且弹出“消息接收中”提示框
         * 5、pic_sysphoto：弹出系统拍照发图
         * 6、pic_photo_or_album：弹出拍照或者相册发图
         * 7、pic_weixin：弹出微信相册发图器
         * 8、location_select：弹出地理位置选择器
         */
        public function createMenu($data, $agentid = '') {
            if ($agentid == '') {
                $agentid = $this->agentid;
            }
            if (!$this->access_token && !$this->checkAuth()) {
                return false;
            }

            $result = $this->httpPost(self::API_URL_PREFIX . self::MENU_CREATE_URL . 'access_token=' . $this->access_token . '&agentid=' . $agentid, Json::encode($data));
            if ($result) {
                $json = Json::decode($result, true);
                if (!$json || !empty($json['errcode']) || $json['errcode'] != 0) {
                    $this->errCode = $json['errcode'];
                    $this->errMsg = $json['errmsg'];
                    return false;
                }
                return true;
            }
            return false;
        }

        /**
         * 获取菜单
         * @return array('menu'=>array(....s))
         */
        public function getMenu($agentid = '') {
            if ($agentid == '') {
                $agentid = $this->agentid;
            }
            if (!$this->access_token && !$this->checkAuth()) {
                return false;
            }

            $result = $this->httpGet(self::API_URL_PREFIX . self::MENU_GET_URL . 'access_token=' . $this->access_token . '&agentid=' . $agentid);
            if ($result) {
                $json = Json::decode($result, true);
                if (!$json || isset($json['errcode']) || $json['errcode'] != 0) {
                    $this->errCode = $json['errcode'];
                    $this->errMsg = $json['errmsg'];
                    return false;
                }
                return $json;
            }
            return false;
        }

        /**
         * 删除菜单
         * @return boolean
         */
        public function deleteMenu($agentid = '') {
            if ($agentid == '') {
                $agentid = $this->agentid;
            }
            if (!$this->access_token && !$this->checkAuth()) {
                return false;
            }

            $result = $this->httpGet(self::API_URL_PREFIX . self::MENU_DELETE_URL . 'access_token=' . $this->access_token . '&agentid=' . $agentid);
            if ($result) {
                $json = Json::decode($result, true);
                if (!$json || !empty($json['errcode'])) {
                    $this->errCode = $json['errcode'];
                    $this->errMsg = $json['errmsg'];
                    return false;
                }
                return true;
            }
            return false;
        }

        /**
         * 上传多媒体文件 (只有三天的有效期，过期自动被删除)
         * 注意：数组的键值任意，但文件名前必须加@，使用单引号以避免本地路径斜杠被转义
         * @param array $data {"media":'@Path\filename.jpg'}
         * @param type 媒体文件类型:图片（image）、语音（voice）、视频（video），普通文件(file)
         * @return boolean|array
         * {
         *    "type": "image",
         *    "media_id": "0000001",
         *    "created_at": "1380000000"
         * }
         */
        public function uploadMedia($filapath, $type) {
            if (!$this->access_token && !$this->checkAuth()) {
                return false;
            }
            //文件名
            $fileinfo = pathinfo($filapath);
            //关键是判断curlfile,官网推荐php5.5或更高的版本使用curlfile来实例文件
            if (class_exists('\CURLFile')) {
                $media = new \CURLFile(realpath($filapath));
                $media->setPostFilename($fileinfo['basename']);
                $media->setMimeType($type);
                $file = array(
                    'media' => $media,
                );
            } else {
                $file = array(
                    "media" => '@' . realpath($filapath) . ";type=" . $type . ";filename=" . $fileinfo['basename'], //文件路径，前面要加@，表明是文件上传.
                );
            }

            $curl = curl_init();
            $url = self::API_URL_PREFIX . self::MEDIA_UPLOAD_URL . 'access_token=' . $this->access_token . '&type=' . $type;
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_USERAGENT, 'Opera/9.80 (Windows NT 6.2; Win64; x64) Presto/2.12.388 Version/12.15');
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('User-Agent: Opera/9.80 (Windows NT 6.2; Win64; x64) Presto/2.12.388 Version/12.15', 'Referer: http://qy.weixin.qq.com', 'Content-Type: multipart/form-data'));
            if (stripos($url, "https://") !== FALSE) {
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            }
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $file);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HEADER, 0);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 2);
            curl_setopt($curl, CURLOPT_TIMEOUT, 10);
            $result = curl_exec($curl); //$result 获取页面信息

            // \common\models\DebugLog::log('100100209',[$url,$result],json_encode($file));
            // \common\models\WechatApiLog::logSave(100100209, $this->appid, $this->suite_id, $this->agentid, $url, $file);

            curl_close($curl);
            if ($result) {
                $json = Json::decode($result, true);
                if (!$json || !empty($json['errcode'])) {
                    $this->errCode = $json['errcode'];
                    $this->errMsg = $json['errmsg'];
                    return false;
                }
                return $json;
            }
            return false;
        }
        /**
         * 上传保密多图文保密消息里的内容中的图片
         * @param string $filepath 需要上传的文件路径
         * @return boolean |　array
         * {
         *     "url": "http://shp.qpic.cn/mmocbiz/xxxxxxxxxxxxx/"
         * }
         */
        public function uploadImg($filapath) {
            if (!$this->access_token && !$this->checkAuth()) {
                return false;
            }

            if (class_exists('\CURLFile')) {
                //关键是判断curlfile,官网推荐php5.5或更高的版本使用curlfile来实例文件
                $file = array(
                    'media' => new \CURLFile(realpath($filapath)),
                );
            } else {
                $file = array(
                    "media" => '@' . realpath($filapath) , //文件路径，前面要加@，表明是文件上传.
                );
            }

            $curl = curl_init();
            $url = self::API_URL_PREFIX . self::MEDIA_UPLOADIMG_URL . 'access_token=' . $this->access_token;
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_USERAGENT, 'Opera/9.80 (Windows NT 6.2; Win64; x64) Presto/2.12.388 Version/12.15');
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('User-Agent: Opera/9.80 (Windows NT 6.2; Win64; x64) Presto/2.12.388 Version/12.15', 'Referer: http://qy.weixin.qq.com', 'Content-Type: multipart/form-data'));
            if (stripos($url, "https://") !== FALSE) {
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            }
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $file);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HEADER, 0);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 2);
            curl_setopt($curl, CURLOPT_TIMEOUT, 10);
            $result = curl_exec($curl); //$result 获取页面信息

            // $curlInfo = curl_getinfo($curl);
            // DebugLog::log('100100209',$result,$curlInfo);

            curl_close($curl);
            if ($result) {
                $json = Json::decode($result, true);
                if (!$json || !empty($json['errcode'])) {
                    $this->errCode = $json['errcode'];
                    $this->errMsg = $json['errmsg'];
                    return false;
                }
                return $json['url'];
            }
            return false;
        }
        /**
         * 根据媒体文件ID获取媒体文件
         * @param string $media_id 媒体文件id
         * @return string url
         */
        public function getMedia($media_id, $path = '/uploads/') {
            if (!$this->access_token && !$this->checkAuth()) {
                return false;
            }

            //微信允许的上传的mime类型
            $mime = [
                'image' => 'jpg',
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'audio/mpeg' => 'mp3',
                'audio/amr' => 'amr',
                'audio/mp4' => 'mp4',
            ];
            $url = self::API_URL_PREFIX . self::MEDIA_GET_URL . 'access_token=' . $this->access_token . '&media_id=' . $media_id;
            $oCurl = curl_init();
            if (stripos($url, "https://") !== FALSE) {
                curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
                curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, FALSE);
                curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
            }
            curl_setopt($oCurl, CURLOPT_URL, $url);
            curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
            // 设置超时5秒
            curl_setopt($oCurl, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($oCurl, CURLOPT_TIMEOUT, 8);

            $sContent = curl_exec($oCurl);
            $aStatus = curl_getinfo($oCurl);
            $errno = curl_errno($oCurl);
            $error = curl_error($oCurl);
            curl_close($oCurl);

            if (intval($aStatus["http_code"]) == 200) {
                $suffix = $mime[$aStatus['content_type']];
                // if (!$suffix) {
                //     return false;
                // }

                $filename = $media_id . '.' . $suffix;
                $filepath = Yii::getAlias('@siteroot') . $path;
                \yii\helpers\FileHelper::createDirectory($filepath);
                $filepath = $filepath . $filename;
                if (!file_exists($filepath)) {
                    $fp = fopen($filepath, 'w');
                    if (false !== $fp) {
                        fwrite($fp, $sContent);
                        fclose($fp);
                    }
                }
                // API_TRACE && \common\models\WechatApiLog::logSave(intval(Yii::$app->session['weaccount']['id']), $this->appid, $this->suite_id, $this->agentid, $url, '', $path . $filename, $errno, $aStatus['content_type']);

                return $path . $filename;
            }
            // API_TRACE && \common\models\WechatApiLog::logSave(intval(Yii::$app->session['weaccount']['id']), $this->appid, $this->suite_id, $this->agentid, $url, '', $aStatus["http_code"], $errno, $aStatus['content_type']);
            return false;
        }

        /**
         * 创建部门
         * @param array $data     结构体为:
         * array (
         *     "name" => "邮箱产品组",   //部门名称
         *     "parentid" => "1"         //父部门id
         *     "order" =>  "1",            //(非必须)在父部门中的次序。从1开始，数字越大排序越靠后
         * )
         * @return boolean|array
         * 成功返回结果
         * {
         *   "errcode": 0,        //返回码
         *   "errmsg": "created",  //对返回码的文本描述内容
         *   "id": 2               //创建的部门id。
         * }
         */
        public function createDepartment($data) {
            if (!$this->access_token && !$this->checkAuth()) {
                return false;
            }

            $result = $this->httpPost(self::API_URL_PREFIX . self::DEPARTMENT_CREATE_URL . 'access_token=' . $this->access_token, Json::encode($data));
            if ($result) {
                $json = Json::decode($result, true);
                if (!$json || !empty($json['errcode']) || $json['errcode'] != 0) {
                    $this->errCode = $json['errcode'];
                    $this->errMsg = $json['errmsg'];
                    return false;
                }
                return $json;
            }
            return false;
        }

        /**
         * 更新部门
         * @param array $data     结构体为:
         * array(
         *     "id" => "1"               //(必须)部门id
         *     "name" =>  "邮箱产品组",   //(非必须)部门名称
         *     "parentid" =>  "1",         //(非必须)父亲部门id。根部门id为1
         *     "order" =>  "1",            //(非必须)在父部门中的次序。从1开始，数字越大排序越靠后
         * )
         * @return boolean|array 成功返回结果
         * {
         *   "errcode": 0,        //返回码
         *   "errmsg": "updated"  //对返回码的文本描述内容
         * }
         */
        public function updateDepartment($data) {
            if (!$this->access_token && !$this->checkAuth()) {
                return false;
            }

            $result = $this->httpPost(self::API_URL_PREFIX . self::DEPARTMENT_UPDATE_URL . 'access_token=' . $this->access_token, Json::encode($data));
            if ($result) {
                $json = Json::decode($result, true);
                if (!$json || !empty($json['errcode']) || $json['errcode'] != 0) {
                    $this->errCode = $json['errcode'];
                    $this->errMsg = $json['errmsg'];
                    return false;
                }
                return $json;
            }
            return false;
        }

        /**
         * 删除部门
         * @param $id
         * @return boolean|array 成功返回结果
         * {
         *   "errcode": 0,        //返回码
         *   "errmsg": "deleted"  //对返回码的文本描述内容
         * }
         */
        public function deleteDepartment($id) {
            if (!$this->access_token && !$this->checkAuth()) {
                return false;
            }

            $result = $this->httpGet(self::API_URL_PREFIX . self::DEPARTMENT_DELETE_URL . 'access_token=' . $this->access_token . '&id=' . $id);
            if ($result) {
                $json = Json::decode($result, true);
                if (!$json || !empty($json['errcode']) || $json['errcode'] != 0) {
                    $this->errCode = $json['errcode'];
                    $this->errMsg = $json['errmsg'];
                    return false;
                }
                return $json;
            }
            return false;
        }

        /**
         * 移动部门
         * @param $data
         * array(
         *    "department_id" => "5",    //所要移动的部门
         *    "to_parentid" => "2",        //想移动到的父部门节点，根部门为1
         *    "to_position" => "1"        //(非必须)想移动到的父部门下的位置，1表示最上方，往后位置为2，3，4，以此类推，默认为1
         * )
         * @return boolean|array 成功返回结果
         * {
         *   "errcode": 0,        //返回码
         *   "errmsg": "ok"  //对返回码的文本描述内容
         * }
         */
        public function moveDepartment($data) {
            if (!$this->access_token && !$this->checkAuth()) {
                return false;
            }

            $result = $this->httpGet(self::API_URL_PREFIX . self::DEPARTMENT_MOVE_URL . 'access_token=' . $this->access_token, Json::encode($data));
            if ($result) {
                $json = Json::decode($result, true);
                if (!$json || !empty($json['errcode']) || $json['errcode'] != 0) {
                    $this->errCode = $json['errcode'];
                    $this->errMsg = $json['errmsg'];
                    return false;
                }
                return $json;
            }
            return false;
        }

        /**
         * 获取部门列表
         * @return boolean|array     成功返回结果
         * {
         *    "errcode": 0,
         *    "errmsg": "ok",
         *    "department": [          //部门列表数据。以部门的order字段从小到大排列
         *        {
         *            "id": 1,
         *            "name": "广州研发中心",
         *            "parentid": 0
         *        },
         *       {
         *          "id": 2
         *          "name": "邮箱产品部",
         *          "parentid": 1
         *       }
         *    ]
         * }
         */
        public function getDepartment($id = 0) {
            if (!$this->access_token && !$this->checkAuth()) {
                return false;
            }

            if ($id > 0) {
                $url = self::API_URL_PREFIX . self::DEPARTMENT_LIST_URL . 'access_token=' . $this->access_token . '&id=' . $id;
            } else {
                $url = self::API_URL_PREFIX . self::DEPARTMENT_LIST_URL . 'access_token=' . $this->access_token;
            }
            $result = $this->httpGet($url);
            if ($result) {
                try {
                    $json = Json::decode($result, true);
                } catch (\Exception $e) {
                    return false;
                }
                if (!$json || !empty($json['errcode'])) {
                    $this->errCode = $json['errcode'];
                    $this->errMsg = $json['errmsg'];
                    return false;
                }
                return $json;
            }
            return false;
        }

        /**
         * 创建成员
         * @param array $data     结构体为:
         * array(
         *    "userid" => "zhangsan",
         *    "name" => "张三",
         *    "department" => [1, 2],
         *    "position" => "产品经理",
         *    "mobile" => "15913215421",
         *    "gender" => 1,     //性别。gender=0表示男，=1表示女
         *    "tel" => "62394",
         *    "email" => "zhangsan@gzdev.com",
         *    "weixinid" => "zhangsan4dev"
         * )
         * @return boolean|array
         * 成功返回结果
         * {
         *   "errcode": 0,        //返回码
         *   "errmsg": "created",  //对返回码的文本描述内容
         * }
         */
        public function createUser($data) {
            if (!$this->access_token && !$this->checkAuth()) {
                return false;
            }

            $result = $this->httpPost(self::API_URL_PREFIX . self::USER_CREATE_URL . 'access_token=' . $this->access_token, Json::encode($data));
            if ($result) {
                $json = Json::decode($result, true);
                if (!$json || !empty($json['errcode']) || $json['errcode'] != 0) {
                    $this->errCode = $json['errcode'];
                    $this->errMsg = $json['errmsg'];
                    return false;
                }
                return $json;
            }
            return false;
        }

        /**
         * 创建成员返回微信状态码
         * @param unknown $data
         * @return boolean|mixed
         */
        public function createUserRe($data) {
            if (!$this->access_token && !$this->checkAuth()) {
                return false;
            }

            $result = $this->httpPost(self::API_URL_PREFIX . self::USER_CREATE_URL . 'access_token=' . $this->access_token, Json::encode($data));
            if ($result) {
                $json = Json::decode($result, true);
                if (!$json || !empty($json['errcode']) || $json['errcode'] != 0) {
                    $this->errCode = $json['errcode'];
                    $this->errMsg = $json['errmsg'];
                }
                return $json;
            }
            return false;
        }

        /**
         * 更新成员
         * @param array $data     结构体为:
         * array(
         *    "userid" => "zhangsan",
         *    "name" => "张三",
         *    "department" => [1, 2],
         *    "position" => "产品经理",
         *    "mobile" => "15913215421",
         *    "gender" => 1,     //性别。gender=0表示男，=1表示女
         *    "tel" => "62394",
         *    "email" => "zhangsan@gzdev.com",
         *    "weixinid" => "zhangsan4dev"
         * )
         * @return boolean|array 成功返回结果
         * {
         *   "errcode": 0,        //返回码
         *   "errmsg": "updated"  //对返回码的文本描述内容
         * }
         */
        public function updateUser($data) {
            if (!$this->access_token && !$this->checkAuth()) {
                return false;
            }

            $result = $this->httpPost(self::API_URL_PREFIX . self::USER_UPDATE_URL . 'access_token=' . $this->access_token, Json::encode($data));
            if ($result) {
                $json = Json::decode($result, true);
                if (!$json || !empty($json['errcode']) || $json['errcode'] != 0) {
                    $this->errCode = $json['errcode'];
                    $this->errMsg = $json['errmsg'];
                    return false;
                }
                return $json;
            }
            return false;
        }

        /**
         * 删除成员
         * @param $userid  员工UserID。对应管理端的帐号
         * @return boolean|array 成功返回结果
         * {
         *   "errcode": 0,        //返回码
         *   "errmsg": "deleted"  //对返回码的文本描述内容
         * }
         */
        public function deleteUser($userid) {
            if (!$this->access_token && !$this->checkAuth()) {
                return false;
            }

            $result = $this->httpGet(self::API_URL_PREFIX . self::USER_DELETE_URL . 'access_token=' . $this->access_token . '&userid=' . $userid);
            if ($result) {
                $json = Json::decode($result, true);
                if (!$json || !empty($json['errcode']) || $json['errcode'] != 0) {
                    $this->errCode = $json['errcode'];
                    $this->errMsg = $json['errmsg'];
                    return false;
                }
                return $json;
            }
            return false;
        }

        /**
         * 获取成员信息
         * @param $userid  员工UserID。对应管理端的帐号
         * @return boolean|array     成功返回结果
         * {
         *    "errcode": 0,
         *    "errmsg": "ok",
         *    "userid": "zhangsan",
         *    "name": "李四",
         *    "department": [1, 2],
         *    "position": "后台工程师",
         *    "mobile": "15913215421",
         *    "gender": 1,     //性别。gender=0表示男，=1表示女
         *    "tel": "62394",
         *    "email": "zhangsan@gzdev.com",
         *    "weixinid": "lisifordev",        //微信号
         *    "avatar": "http://wx.qlogo.cn/mmopen/ajNVdqHZLLA3W..../0",   //头像url。注：如果要获取小图将url最后的"/0"改成"/64"即可
         *    "status": 1      //关注状态: 1=已关注，2=已冻结，4=未关注
         * }
         */
        public function getUserInfo($userid) {
            if (!$this->access_token && !$this->checkAuth()) {
                return false;
            }

            $result = $this->httpGet(self::API_URL_PREFIX . self::USER_GET_URL . 'access_token=' . $this->access_token . '&userid=' . $userid);
            if ($result) {
                $json = Json::decode($result, true);
                if (!$json || !empty($json['errcode']) || $json['errcode'] != 0) {
                    $this->errCode = $json['errcode'];

                    $this->errMsg = $json['errmsg'];
                    return false;
                }
                return $json;
            }
            return false;
        }

        /**
         * 获取部门成员
         * @param $department_id   部门id
         * @param $fetch_child     1/0：是否递归获取子部门下面的成员
         * @param $status          0获取全部员工，1获取已关注成员列表，2获取禁用成员列表，4获取未关注成员列表。status可叠加
         * @return boolean|array     成功返回结果
         * {
         *    "errcode": 0,
         *    "errmsg": "ok",
         *    "userlist": [
         *            {
         *                   "userid": "zhangsan",
         *                   "name": "李四"
         *            }
         *      ]
         * }
         */
        public function getUserList($department_id, $fetch_child = 0, $status = 0) {
            if (!$this->access_token && !$this->checkAuth()) {
                return false;
            }

            $result = $this->httpGet(self::API_URL_PREFIX . self::USER_LIST_URL . 'access_token=' . $this->access_token
                . '&department_id=' . $department_id . '&fetch_child=' . $fetch_child . '&status=' . $status);
            if ($result) {
                try {
                    $json = Json::decode($result, true);
                } catch (\Exception $e) {
                    return false;
                }
                if (!$json || !empty($json['errcode']) || $json['errcode'] != 0) {
                    $this->errCode = $json['errcode'];
                    $this->errMsg = $json['errmsg'];
                    return false;
                }
                return $json;
            }
            return false;
        }

        /**
         * 获取部门成员详情
         * @param $department_id   部门id
         * @param $fetch_child     1/0：是否递归获取子部门下面的成员
         * @param $status          0获取全部员工，1获取已关注成员列表，2获取禁用成员列表，4获取未关注成员列表。status可叠加
         * @return boolean|array     成功返回结果
         * {
         *    "errcode": 0,
         *    "errmsg": "ok",
         *    "userlist": [
         *            {
         *                   "userid": "zhangsan",
         *                   "name": "李四",
         *                   "department": [1, 2],
         *                   "position": "后台工程师",
         *                   "mobile": "15913215421",
         *                   "gender": 1,     //性别。gender=0表示男，=1表示女
         *                   "tel": "62394",
         *                   "email": "zhangsan@gzdev.com",
         *                   "weixinid": "lisifordev",        //微信号
         *                   "avatar": "http://wx.qlogo.cn/mmopen/ajNVdqHZLLA3W..../0",   //头像url。注：如果要获取小图将url最后的"/0"改成"/64"即可
         *                   "status": 1      //关注状态: 1=已关注，2=已冻结，4=未关注
         *                   "extattr": {"attrs":[{"name":"爱好","value":"旅游"},{"name":"卡号","value":"1234567234"}]}
         *            }
         *      ]
         * }
         */
        public function getUserListInfo($department_id, $fetch_child = 0, $status = 0) {
            if (!$this->access_token && !$this->checkAuth()) {
                return false;
            }

            $result = $this->httpGet(self::API_URL_PREFIX . self::USER_LIST_INFO_URL . 'access_token=' . $this->access_token
                . '&department_id=' . $department_id . '&fetch_child=' . $fetch_child . '&status=' . $status);
            if ($result) {
                //过滤特殊字符串导致json  转换失败
                //修复json中特殊字符的问题 [\x00-\x1f] 不能完全替换 edit by tasal 20150923
                $result = preg_replace('/[\\x00-\\x08\\x0b-\\x0c\\x0e-\\x1f]/', '', $result);
                try {
                    $json = Json::decode($result, true);
                } catch (Exception $e) {
                    DebugLog::log(0, 'appid:' . $this->appid . ',permanent_code:' . $this->permanent_code . ',agent_id:' . $this->agentid, 'qywechat getUserListInfo error ,Json::decode faild');
                }
                if (!$json || !empty($json['errcode']) || $json['errcode'] != 0) {
                    $this->errCode = $json['errcode'];
                    $this->errMsg = $json['errmsg'];
                    return false;
                }

                return $json;
            }
            return false;
        }

        /**
         * 分页拉取数据
         * @param int $seq  变更序号
         * @param int $offset  变更序号偏移量
         */
        public function getPageUser($seq, $offset = 0, $return = [], $all = false) {
            if (!$this->access_token && !$this->checkAuth()) {
                return false;
            }

            $param = [
                'seq' => $seq,
                'offset' => $offset,
            ];
            $result = Helper::http_post(self::API_URL_PREFIX . self::GET_PAGE_URL . $this->access_token, Json::encode($param));
            if ($result) {
                $result = Json::decode($result, true);
                if (!$result || !empty($result['errcode']) || $result['errcode'] != 0) {
                    $this->errCode = $result['errcode'];
                    $this->errMsg = $result['errmsg'];
                    DebugLog::log(0, $this->errCode . $this->errMsg, '通讯录变更事件接口调用失败');
                    return false;
                }
                $return[] = $result;
                // 1：最后一页  0 ：未拉取完
                if ($result['is_last'] == 0 && $all) {
                    $return = $this->getPageUser($result['next_seq'], $result['next_offset'], $return, $all);
                }
                return $return;
            }
            return false;
        }

        /**
         * 根据code获取成员信息
         * 通过Oauth2.0或者设置了二次验证时获取的code，用于换取成员的UserId和DeviceId
         *
         * @param $code        Oauth2.0或者二次验证时返回的code值
         * @param $agentid     跳转链接时所在的企业应用ID，未填则默认为当前配置的应用id
         * @return boolean|array 成功返回数组
         * array(
         *     'UserId' => 'USERID',       //员工UserID
         *     'DeviceId' => 'DEVICEID'    //手机设备号(由微信在安装时随机生成)
         * )
         */
        public function getUserId($code, $agentid = '') {
            if ($agentid == '') {
                $agentid = $this->agentid;
            }

            if (!$this->access_token && !$this->checkAuth()) {
                return false;
            }

            $result = $this->httpGet(self::API_URL_PREFIX . self::USER_GETINFO_URL . 'access_token=' . $this->access_token . '&code=' . $code . '&agentid=' . $agentid);
            if (strpos(Yii::$app->request->getUrl(), '/predpack/fetch/fetch') === 0) {
                DebugLog::log('380001', self::API_URL_PREFIX . self::USER_GETINFO_URL . 'access_token=' . $this->access_token . '&code=' . $code . '&agentid=' . $agentid . ' | ' . $result, '红包返回');
            }
            if ($result) {
                $json = Json::decode($result, true);
                if (!$json || !empty($json['errcode']) || $json['errcode'] != 0) {
                    $this->errCode = $json['errcode'];
                    $this->errMsg = $json['errmsg'];
                    return false;
                }
                return $json;
            }
            return false;
        }

        /**
         * 根据user_ticket获取成员详细
         * 通过Oauth2.0或者设置了二次验证时获取的code，用于换取成员的UserId和DeviceId
         *
         * @param $user_ticket        Oauth2.0或者二次验证时返回的值
         * @return boolean|array 成功返回数组
         * 请求
         * array(
         *     'user_ticket' => 'USERTICKET    //员工UserID
         * )
         * 返回array(
         *     'userid' => '',
         *     'name' => '',
         *     'gender' => '',
         *     'department' => '',
         *     'position' => '',
         *     'avatar' => '',
         *     'mobile' => '',
         *     'email' =>''
         * )
         */
        public function getUserDetail($user_ticket) {

            if (!$this->access_token && !$this->checkAuth()) {
                return false;
            }
            $param = [
                'user_ticket' => $user_ticket,
            ];
            $result = $this->httpPost(self::API_URL_PREFIX . self::USER_GETDETAIL_URL . 'access_token=' . $this->access_token, Json::encode($param));
            if ($result) {
                $json = Json::decode($result, true);
                if (!$json || !empty($json['errcode']) || $json['errcode'] != 0) {
                    $this->errCode = $json['errcode'];
                    $this->errMsg = $json['errmsg'];
                    return false;
                }
                return $json;
            }
            return false;
        }

        /**
         * 创建标签
         * @param array $data     结构体为:
         * array(
         *    "tagname" => "UI"
         * )
         * @return boolean|array
         * 成功返回结果
         * {
         *   "errcode": 0,        //返回码
         *   "errmsg": "created",  //对返回码的文本描述内容
         *   "tagid": "1"
         * }
         */
        public function createTag($data) {
            if (!$this->access_token && !$this->checkAuth()) {
                return false;
            }

            $result = $this->httpPost(self::API_URL_PREFIX . self::TAG_CREATE_URL . 'access_token=' . $this->access_token, Json::encode($data));
            if ($result) {
                $json = Json::decode($result, true);
                if (!$json || !empty($json['errcode']) || $json['errcode'] != 0) {
                    $this->errCode = $json['errcode'];
                    $this->errMsg = $json['errmsg'];
                    return false;
                }
                return $json;
            }
            return false;
        }

        /**
         * 更新标签
         * @param array $data     结构体为:
         * array(
         *    "tagid" => "1",
         *    "tagname" => "UI design"
         * )
         * @return boolean|array 成功返回结果
         * {
         *   "errcode": 0,        //返回码
         *   "errmsg": "updated"  //对返回码的文本描述内容
         * }
         */
        public function updateTag($data) {
            if (!$this->access_token && !$this->checkAuth()) {
                return false;
            }

            $result = $this->httpPost(self::API_URL_PREFIX . self::TAG_UPDATE_URL . 'access_token=' . $this->access_token, Json::encode($data));
            if ($result) {
                $json = Json::decode($result, true);
                if (!$json || !empty($json['errcode']) || $json['errcode'] != 0) {
                    $this->errCode = $json['errcode'];
                    $this->errMsg = $json['errmsg'];
                    return false;
                }
                return $json;
            }
            return false;
        }

        /**
         * 删除标签
         * @param $tagid  标签TagID
         * @return boolean|array 成功返回结果
         * {
         *   "errcode": 0,        //返回码
         *   "errmsg": "deleted"  //对返回码的文本描述内容
         * }
         */
        public function deleteTag($tagid) {
            if (!$this->access_token && !$this->checkAuth()) {
                return false;
            }

            $result = $this->httpGet(self::API_URL_PREFIX . self::TAG_DELETE_URL . 'access_token=' . $this->access_token . '&tagid=' . $tagid);
            if ($result) {
                $json = Json::decode($result, true);
                if (!$json || !empty($json['errcode']) || $json['errcode'] != 0) {
                    $this->errCode = $json['errcode'];
                    $this->errMsg = $json['errmsg'];
                    return false;
                }
                return $json;
            }
            return false;
        }

        /**
         * 获取标签成员
         * @param $tagid  标签TagID
         * @return boolean|array     成功返回结果
         * {
         *    "errcode": 0,
         *    "errmsg": "ok",
         *    "userlist": [
         *          {
         *              "userid": "zhangsan",
         *              "name": "李四"
         *          }
         *      ]
         * }
         */
        public function getTag($tagid) {
            if (!$this->access_token && !$this->checkAuth()) {
                return false;
            }

            $result = $this->httpGet(self::API_URL_PREFIX . self::TAG_GET_URL . 'access_token=' . $this->access_token . '&tagid=' . $tagid);
            if ($result) {
                $json = Json::decode($result, true);
                if (!$json || !empty($json['errcode']) || $json['errcode'] != 0) {
                    $this->errCode = $json['errcode'];
                    $this->errMsg = $json['errmsg'];
                    return false;
                }
                return $json;
            }
            return false;
        }

        /**
         * 增加标签成员
         * @param array $data     结构体为:
         * array (
         *    "tagid" => "1",
         *    "userlist" => array(    //企业员工ID列表
         *         "user1",
         *         "user2"
         *     )
         * )
         * @return boolean|array
         * 成功返回结果
         * {
         *   "errcode": 0,        //返回码
         *   "errmsg": "ok",  //对返回码的文本描述内容
         *   "invalidlist"："usr1|usr2|usr"     //若部分userid非法，则会有此段。不在权限内的员工ID列表，以“|”分隔
         * }
         */
        public function addTagUser($data) {
            if (!$this->access_token && !$this->checkAuth()) {
                return false;
            }

            $result = $this->httpPost(self::API_URL_PREFIX . self::TAG_ADDUSER_URL . 'access_token=' . $this->access_token, Json::encode($data));
            if ($result) {
                $json = Json::decode($result, true);
                if (!$json || !empty($json['errcode']) || $json['errcode'] != 0) {
                    $this->errCode = $json['errcode'];
                    $this->errMsg = $json['errmsg'];
                    return false;
                }
                return $json;
            }
            return false;
        }

        /**
         * 删除标签成员
         * @param array $data     结构体为:
         * array (
         *    "tagid" => "1",
         *    "userlist" => array(    //企业员工ID列表
         *         "user1",
         *         "user2"
         *     )
         * )
         * @return boolean|array
         * 成功返回结果
         * {
         *   "errcode": 0,        //返回码
         *   "errmsg": "deleted",  //对返回码的文本描述内容
         *   "invalidlist"："usr1|usr2|usr"     //若部分userid非法，则会有此段。不在权限内的员工ID列表，以“|”分隔
         * }
         */
        public function delTagUser($data) {
            if (!$this->access_token && !$this->checkAuth()) {
                return false;
            }

            $result = $this->httpPost(self::API_URL_PREFIX . self::TAG_DELUSER_URL . 'access_token=' . $this->access_token, Json::encode($data));
            if ($result) {
                $json = Json::decode($result, true);
                if (!$json || !empty($json['errcode']) || $json['errcode'] != 0) {
                    $this->errCode = $json['errcode'];
                    $this->errMsg = $json['errmsg'];
                    return false;
                }
                return $json;
            }
            return false;
        }

        /**
         * 获取标签列表
         * @return boolean|array     成功返回数组结果，这里附上json样例
         * {
         *    "errcode": 0,
         *    "errmsg": "ok",
         *    "taglist":[
         *       {"tagid":1,"tagname":"a"},
         *       {"tagid":2,"tagname":"b"}
         *    ]
         * }
         */
        public function getTagList() {
            if (!$this->access_token && !$this->checkAuth()) {
                return false;
            }

            $result = $this->httpGet(self::API_URL_PREFIX . self::TAG_LIST_URL . 'access_token=' . $this->access_token);
            if ($result) {
                $json = Json::decode($result, true);
                if (!$json || !empty($json['errcode'])) {
                    $this->errCode = $json['errcode'];
                    $this->errMsg = $json['errmsg'];
                    return false;
                }
                return $json;
            }
            return false;
        }

        /**
         * 主动发送信息接口
         * @param array $data     结构体为:
         * array(
         *         "touser" => "UserID1|UserID2|UserID3",
         *         "toparty" => "PartyID1|PartyID2 ",
         *         "totag" => "TagID1|TagID2 ",
         *         "safe":"0"            //是否为保密消息，对于news无效
         *         "agentid" => "001",    //应用id
         *         "msgtype" => "text",  //根据信息类型，选择下面对应的信息结构体
         *
         *         "text" => array(
         *                 "content" => "Holiday Request For Pony(http://xxxxx)"
         *         ),
         *
         *         "image" => array(
         *                 "media_id" => "MEDIA_ID"
         *         ),
         *
         *         "voice" => array(
         *                 "media_id" => "MEDIA_ID"
         *         ),
         *
         *         " video" => array(
         *                 "media_id" => "MEDIA_ID",
         *                 "title" => "Title",
         *                 "description" => "Description"
         *         ),
         *
         *         "file" => array(
         *                 "media_id" => "MEDIA_ID"
         *         ),
         *
         *         "news" => array(            //不支持保密
         *                 "articles" => array(    //articles  图文消息，一个图文消息支持1到10个图文
         *                     array(
         *                         "title" => "Title",             //标题
         *                         "description" => "Description", //描述
         *                         "url" => "URL",                 //点击后跳转的链接。可根据url里面带的code参数校验员工的真实身份。
         *                         "picurl" => "PIC_URL",          //图文消息的图片链接,支持JPG、PNG格式，较好的效果为大图640*320，
         *                                                         //小图80*80。如不填，在客户端不显示图片
         *                     ),
         *                 )
         *         ),
         *
         *         "mpnews" => array(
         *                 "articles" => array(    //articles  图文消息，一个图文消息支持1到10个图文
         *                     array(
         *                         "title" => "Title",             //图文消息的标题
         *                         "thumb_media_id" => "id",       //图文消息缩略图的media_id
         *                         "author" => "Author",           //图文消息的作者(可空)
         *                         "content_source_url" => "URL",  //图文消息点击“阅读原文”之后的页面链接(可空)
         *                         "content" => "Content"          //图文消息的内容，支持html标签
         *                         "digest" => "Digest description",   //图文消息的描述
         *                         "show_cover_pic" => "0"         //是否显示封面，1为显示，0为不显示(可空)
         *                     ),
         *                 )
         *         )
         * )
         * 请查看官方开发文档中的 发送消息 -> 消息类型及数据格式
         *
         * @return boolean|array
         * 如果对应用或收件人、部门、标签任何一个无权限，则本次发送失败；
         * 如果收件人、部门或标签不存在，发送仍然执行，但返回无效的部分。
         * {
         *    "errcode": 0,
         *    "errmsg": "ok",
         *    "invaliduser": "UserID1",
         *    "invalidparty":"PartyID1",
         *    "invalidtag":"TagID1"
         * }
         */
        public function sendMessage($data) {
            if (!$this->access_token && !$this->checkAuth()) {
                return false;
            }

            echo PHP_EOL . PHP_EOL . 'begin_time=' . date("Y-m-d H:i:s") . PHP_EOL . PHP_EOL;
            $result = $this->httpPost(self::API_URL_PREFIX . self::MASS_SEND_URL . 'access_token=' . $this->access_token, Json::encode($data));
            echo PHP_EOL . PHP_EOL . 'end_time=' . date("Y-m-d H:i:s") . ' result' . $result . PHP_EOL . PHP_EOL;
            if ($result) {
                $json = Json::decode($result, true);
                if ($json['errcode'] == 42009) {
                    echo PHP_EOL . PHP_EOL . 'errcode=' . $json['errcode'] . PHP_EOL . PHP_EOL;
                    //重新获取token   重复消息
                    $this->getSuiteAccessToken(true);
                    $this->access_token = $this->getAccessToken($this->permanent_code, true);
                    return $this->sendMessage($data);
                }
                if (!$json || !empty($json['errcode']) || $json['errcode'] != 0) {
                    // API_TRACE && $this->apiErrormsg($data,$json);
                    $this->errCode = $json['errcode'];
                    $this->errMsg = $json['errmsg'];
                    echo PHP_EOL . PHP_EOL . 'errcode=' . $json['errcode'] . $json['errmsg'] . PHP_EOL . PHP_EOL;
                    return false;
                }
                return $json;
            }
            return false;
        }

        /**
         * 二次验证
         * 企业在开启二次验证时，必须填写企业二次验证页面的url。
         * 当员工绑定通讯录中的帐号后，会收到一条图文消息，
         * 引导员工到企业的验证页面验证身份，企业在员工验证成功后，
         * 调用如下接口即可让员工关注成功。
         *
         * @param $userid
         * @return boolean|array 成功返回结果
         * {
         *   "errcode": 0,        //返回码
         *   "errmsg": "ok"  //对返回码的文本描述内容
         * }
         */
        public function authSucc($userid) {
            if (!$this->access_token && !$this->checkAuth()) {
                return false;
            }
            $result = $this->httpGet(self::API_URL_PREFIX . self::AUTHSUCC_URL . 'access_token=' . $this->access_token . '&userid=' . $userid);
            if ($result) {
                $json = Json::decode($result, true);
                if (!$json || !empty($json['errcode']) || $json['errcode'] != 0) {
                    $this->errCode = $json['errcode'];
                    $this->errMsg = $json['errmsg'];
                    return false;
                }
                return $json;
            }
            return false;
        }

        /**
         * oauth 授权跳转接口
         * @param string $callback 回调URI
         * @param string $state 重定向后会带上state参数，企业可以填写a-zA-Z0-9的参数值
         * @return string
         */
        public function getOauthRedirect($callback, $state = 'STATE', $scope = 'snsapi_base') {
            if ($this->oauth_scope) {
                $scope = $this->oauth_scope;
            }
            return self::OAUTH_PREFIX . self::OAUTH_AUTHORIZE_URL . 'appid=' . $this->appid . '&agentid=' . $this->agentid . '&redirect_uri=' . urlencode($callback) . '&response_type=code&scope=' . $scope . '&state=' . $state . '#wechat_redirect';
        }

        //JS_SDK 分享  以下三个方法： getSignPackage、createNonceStr、 getJsApiTicket    edit 刘凤悦 2015-01-23
        /**
         * JS_SDk分享 获取配置参数
         * @return array
         */
        public function getSignPackage() {
            $jsapiTicket = $this->getJsApiTicket();
            $uri = explode($_SERVER[HTTP_HOST], $_SERVER[REQUEST_URI]);
            $resultUrl = end($uri);
            $url = (strpos(SITE_URL, 'https') === false ? "http" : "https") . "://" . $_SERVER[HTTP_HOST] . $resultUrl;
//        $url = "http://$_SERVER[HTTP_HOST]".$resultUrl;
            $timestamp = time();
            $nonceStr = $this->createNonceStr();
            // 这里参数的顺序要按照 key 值 ASCII 码升序排序
            $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";
            $signature = sha1($string);
            $signPackage = array(
                "appId" => $this->appid,
                "nonceStr" => $nonceStr,
                "timestamp" => $timestamp,
                "url" => $url,
                "signature" => $signature,
                "rawString" => $string,
            );
            return $signPackage;
        }

        /**
         * 获取随机码
         * @return string
         */
        private function createNonceStr($length = 16) {
            $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
            $str = "";
            for ($i = 0; $i < $length; $i++) {
                $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
            }
            return $str;
        }

        /**
         * 获取jsapi_ticket
         * @return boolean|array
         */
        private function getJsApiTicket() {
            $key = 'jsapiticket' . $this->appid;
            if (!$this->access_token && !$this->getAccessToken($this->permanent_code)) {
                return false;
            }

            $ticket = MCache::get($key);
            if ($ticket) {
                return $ticket;
            }
            $url = self::API_URL_PREFIX . self::CORP_JSAPI_TICKET . $this->access_token;
            $result = $this->httpGet($url);
            if ($result) {
                $res = Json::decode($result, true);
                if (isset($res['errcode']) && $res['errcode'] == 0) {
                    MCache::set($key, $res['ticket'], 3600);
                    return $res['ticket'];
                } else {
                    DebugLog::log(888888, $res, 'get js_api_ticket faild');
                }
            }

        }

        /**
         * 指纹验证成功后向微信平台进行验签
         * @param $result_json_signature  使用SOTER安全密钥对result_json的签名
         * @param $result_json  在设备安全区域（TEE）内获得的本机安全信息（如TEE名称版本号等以及防重放参数）以及本次认证信息（仅Android支持，本次认证的指纹ID)
         */
        public function touchID($openid, $result_json, $result_json_signature) {
            if (!$this->access_token && !$this->getAccessToken($this->permanent_code)) {
                return false;
            }

            $params = [
                "openid" => $openid,
                "json_string" => $result_json,
                "json_signature" => $result_json_signature,
            ];

            $result = Helper::http_post(self::API_URL_PREFIX . self::VERIFY_SIGNATURE . $this->suite_access_token, Json::encode($params));
            if ($result) {
                $result = Json::decode($result, true);
                if (!$result || !isset($result['errcode']) || $result['errcode'] != 0) {
                    $this->errCode = $result['errcode'];
                    $this->errMsg = $result['errmsg'];
                    return false;
                } else {
                    return true;
                }
            }
            return false;
        }

        /**
         * 企业应用设置（如：可信域名，地理位置上报等等）
         */
        public function setAgent($agent) {
            $params = [
                "suite_id" => $this->suite_id,
                "auth_corpid" => $this->auth_corpid,
                "permanent_code" => $this->permanent_code,
                'agent' => $agent,
            ];
            $result = $this->httpPost(self::API_URL_PREFIX . self::CORP_SET_AGENT . $this->suite_access_token, Json::encode($params));
            if ($result) {
                $result = Json::decode($result, true);
                if (!$result || !isset($result['errcode']) || $result['errcode'] != 0) {
                    $this->errCode = $result['errcode'];
                    $this->errMsg = $result['errmsg'];
                    return false;
                } else {
                    return true;
                }
            }
            return false;
        }
        /**
         * 企业号应用列表
         */
        public function getAuthInfo($data) {
            $this->suite_access_token = $this->getSuiteAccessToken();
            $params = [
                "suite_id" => $data['SuiteId'],
                "auth_corpid" => $data['auth_corpid'],
                "permanent_code" => $data['permanent_code'],
            ];
            $result = $this->httpPost(self::API_URL_PREFIX . self::CORP_GET_AUTH_INFO . $this->suite_access_token, Json::encode($params));
            if ($result) {
                $result = Json::decode($result, true);
                if (isset($result['auth_corp_info']) == false) {
                    $this->errCode = $result['errcode'];
                    $this->errMsg = $result['errmsg'];
                    return false;
                } else {
                    return $result;
                }
            }
            return false;
        }
        /**
         * 企业userid 转换 openid
         */
        public function toOpenid($userId, $agentid = 0) {
            if (!$this->access_token && !$this->checkAuth()) {
                return false;
            }

            $params['userid'] = $userId;
            if ($agentid) {
                $params['agentid'] = (int) $agentid;
            }

            $result = $this->httpPost(self::API_URL_PREFIX . self::TO_OPENID_URL . $this->access_token, Json::encode($params));
            if ($result) {
                $result = Json::decode($result, true);
                return $result;
                if (!$result || !isset($result['errcode']) || $result['errcode'] != 0) {
                    $this->errCode = $result['errcode'];
                    $this->errMsg = $result['errmsg'];
                    return false;
                } else {
                    return $result;
                }
            }
        }
        /**
         * 企业userid 转换 openid
         */
        public function toUserid($openid) {
            if (!$this->access_token && !$this->checkAuth()) {
                return false;
            }

            $params = [
                "openid" => $openid,
            ];
            echo 'token=' . $this->access_token;
            $result = $this->httpPost(self::API_URL_PREFIX . self::TO_USERID_URL . $this->access_token, Json::encode($params));
            return $result;
        }
        /**
         * api调用错误
         */
        public function apiErrormsg($data, $error) {
            $m = new Mailer();
            $m->getTransport();
            $config = \Yii::$app->components['mailer']['transport'];
            $toEmail = Yii::$app->params['apiEmail'];
            $mailCfg = [
                'class' => 'Swift_SmtpTransport',
                'host' => $config['host'],
                'username' => $config['username'],
                'password' => $config['password'],
            ];
            $m->setTransport($mailCfg);
            $msg = $m->compose();
            $msg->setCharset('UTF-8');
            $msg->setFrom([$config['username'] => '办公逸-api跟踪']);
            $msg->setTo($toEmail);
            $msg->setSubject('办公逸-api跟踪');
            $msg->setHtmlBody('信息：' . Json::encode($data) . PHP_EOL . '错误信息：' . Json::encode($error));
            $rlt = $msg->send();
        }

        /**
         * [getShakeInfo 获取设备信息，包括UUID、major、minor，以及距离、openID 等信息。]
         * @param string $ticket 摇周边业务的ticket，可在摇到的URL 中得到，ticket生效时间为30 分钟
         * @return boolean|mixed
         * 正确返回JSON 数据示例:
         * {
         *  "data": {
         *      "page_id ": 14211,
         *      "beacon_info": {
         *          "distance": 55.00620700469034,
         *          "major": 10001,
         *          "minor": 19007,
         *          "uuid": "FDA50693-A4E2-4FB1-AFCF-C6EB07647825"
         *      },
         *      "openid": "oVDmXjp7y8aG2AlBuRpMZTb1-cmA"
         *  },
         *  "errcode": 0,
         *  "errmsg": "ok"
         * }
         * 字段说明:
         * beacon_info 设备信息，包括UUID、major、minor，以及距离
         * UUID、major、minor UUID、major、minor
         * distance Beacon 信号与手机的距离
         * page_id 摇周边页面唯一ID
         * openid 商户AppID 下用户的唯一标识
         * poi_id 门店ID，有的话则返回，没有的话不会在JSON 格式内
         */
        public function getShakeInfo($ticket) {
            if (!$this->access_token && !$this->checkAuth()) {
                return false;
            }

            $data = array('ticket' => $ticket);
            $result = $this->httpPost(self::API_URL_PREFIX . self::SHAKEAROUND_GETSHAKEINFO . $this->access_token, Json::encode($data));
            $this->log($result);
            if ($result) {
                $json = json_decode($result, true);
                if (!$json || !empty($json['errcode'])) {
                    $this->errCode = $json['errcode'];
                    $this->errMsg = $json['errmsg'];
                    return false;
                }
                return $json;
            }
            return false;
        }
        /**
         * 邀请员工关注
         * 每月邀请的总人次不超过成员上限的2倍；每7天对同一个成员只能邀请一次
         */
        public function inviteuser($userid) {
            if (!$this->access_token && !$this->checkAuth()) {
                return false;
            }

            $data = ['userid' => (string) $userid];
            $result = $this->httpPost(self::API_URL_PREFIX . self::INVITE_SEND_URL . $this->access_token, Json::encode($data));
            if ($result) {
                $json = Json::decode($result);
                if (!$json || $json['errcode'] != 0) {
                    $this->errCode = $json['errcode'];
                    $this->errMsg = $json['errmsg'];
                    return false;
                }
                return $json;
            }
            return false;
        }
        /**
         * 微信企业号选择联系人插件，获取签名
         */
        public function getSignatureOfContact($url) {
            if (!$this->access_token && !$this->checkAuth()) {
                return false;
            }

            $json = MCache::contactTicket();
            if (!$json) {
                $result = $this->httpGet(self::API_URL_PREFIX . self::TICKET_GET_URL . $this->access_token . '&type=contact');
                if ($result) {
                    $json = Json::decode($result);
                }
                if (empty($json) || $json['errcode'] != 0) {
                    $this->errCode = $json['errcode'];
                    $this->errMsg = $json['errmsg'];
                    DebugLog::log(0, $json, 'getSignatureOfContact faild');
                    return false;
                }
                MCache::contactTicket($json, intval($json['expires_in']) ? (intval($json['expires_in']) - 1200) : 3600);
            }
            $nonceStr = $this->createNonceStr();
            $timestamp = time();
            $str = "group_ticket={$json['ticket']}&noncestr=$nonceStr&timestamp=$timestamp&url=$url";
            $signature = sha1($str);
            $param['groupId'] = $json['group_id'];
            $param['timestamp'] = $timestamp;
            $param['nonceStr'] = $nonceStr;
            $param['signature'] = $signature;
            return $param;
        }
        /**
         * 获取卡券ticket
         */
        protected function getCardApiTicket() {
            if (!$this->access_token && !$this->checkAuth()) {
                return false;
            }

            $ticket = MCache::cardTicket($this->appid);
            if ($ticket) {
                return $ticket;
            }

            $result = $this->httpGet(self::API_URL_PREFIX . self::TICKET_GET_URL . $this->access_token . '&type=wx_card');
            if (!$result) {
                return false;
            }

            $json = Json::decode($result);
            if (empty($json) || $json['errcode'] != 0) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                DebugLog::log(0, $json, 'getCardApiTicket faild');
                return false;
            }
            MCache::cardTicket($this->appid, $json['ticket']);
            return $json['ticket'];
        }
        /**
         * 获取卡券签名
         */
        public function getCardSign() {
            require_once Yii::getAlias('@siteroot') . '/common/librarys/cardSDK.php';
            $ticket = MCache::cardTicket($this->appid);
            if (empty($ticket)) {
                $ticket = $this->getCardApiTicket();
            }
            if (empty($ticket)) {
                return false;
            }

            $time = time();
            $noncestr = $this->createNonceStr();
            $signature = new Signature();
            // ticket
            $signature->add_data($ticket);
            // app_id
            $signature->add_data($this->appid);
            // card_id
            $signature->add_data('');
            // card_type
            $signature->add_data('INVOICE');
            // location_id
            $signature->add_data('');
            //timestamp
            $signature->add_data($time);
            //nonce_str
            $signature->add_data($noncestr);
            $signStr = $signature->get_signature();

            $sign['appid'] = $this->appid;
            $sign['card_id'] = '';
            $sign['card_type'] = 'INVOICE';
            $sign['location_id'] = '';
            $sign['timestamp'] = $time;
            $sign['nonce_str'] = $noncestr;
            $sign['sign'] = $signStr;
            return $sign;

        }

        /**
         * 获取单个发票信息
         * {"errcode":0,"errmsg":"ok","card_id":"pIMMSwLjIVoCu7C88f4ipXQSDhQw","begin_time":1476972180,"end_time":2423656980,"user_card_status":"NORMAL","openid":"oxWoRuK4V1k1ox5fAbQRWZZZXZD0","type":"增值税电子普通发票","payee":"大象慧云信息技术有限公司","detail":"可在公司企业号内报销使用","user_info":{"fee":880,"title":"靠谱前程","billing_time":1482903300,"billing_no":"16741064","billing_code":"011001600111","info":[],"accept":true,"pdf_url":"?wx_invoice_token=DcILywBciYLXYR31FIl1-GpJzz_INBhJoxnj36k7tZEuNYHHgzCR-54X8ObqmQAr_nNoYX7No3_G9cQuN8w7WYxAd5Izit8cBkDodsh2ZIQ."}}
         */
        public function getInvoiceInfo($cardId, $encryptCode) {
            if (empty($cardId) || empty($encryptCode)) {
                return false;
            }
            $param = [
                'card_id' => $cardId,
                'encrypt_code' => $encryptCode,
            ];
            $result = $this->httpPost(self::API_URL_PREFIX . self::INVOICE_GET_URL . $this->access_token, json_encode($param));
            $result = json_decode($result, true);
            if ($result && $result['errcode'] == 0) {
                $result['errno'] = $result['errcode'];
                unset($result['errcode']);
                return $result;
            } else {
                $this->errCode = $result['errcode'];
                $this->errMsg = $result['errmsg'];
                return ['errno' => $this->errCode, 'errmsg' => $this->errMsg];
            }
        }

        /**
         * 更新发票状态
         * 可以通过该接口对某一张发票进行锁定/解锁/报销操作,
         * 注意:报销状态为不可逆状态,请慎重!
         * $status INVOICE_REIMBURSE_INIT 发票初始状态,未锁定
         *         INVOICE_REIMBURSE_LOCK 发票已锁定
         *         INVOICE_REIMBURSE_CLOSURE 发票已核销
         */
        public function updateInvoiceStatus($cardId, $encryptCode, $status) {
            if (empty($cardId) || empty($encryptCode)) {
                return false;
            }
            $param = [
                'card_id' => $cardId,
                'encrypt_code' => $encryptCode,
                'reimburse_status' => $status,
            ];
            $result = $this->httpPost(self::API_URL_PREFIX . self::INVOICE_UPDATE_URL . $this->access_token, json_encode($param));
            $result = json_decode($result, true);
            if ($result && $result['errcode'] == 0) {
                $result['errno'] = $result['errcode'];
                unset($result['errcode']);
                return $result;
            } else {
                $this->errCode = $result['errcode'];
                $this->errMsg = $result['errmsg'];
                return ['errno' => $this->errCode, 'errmsg' => $this->errMsg];
            }
        }

        /**
         * 设置为通讯录套件
         * @param array $data     结构体为:
         * array (
         *    "permanent_code" => "xxx",
         *    "suite_id" =>"",
         *    "auth_corpid" =>"",
         *    "is_contact_suite" =>true,
         * )
         * @return boolean|array
         * 成功返回结果
         * {
         *   "errcode": 0,        //返回码
         *   "errmsg": "ok",  //对返回码的文本描述内容
         * }
         */
        public function markAsContactSuite($data) {
            $this->suite_access_token = $this->getSuiteAccessToken();

            echo $this->suite_access_token;

            echo Json::encode($data);
            $result = $this->httpPost(self::API_URL_PREFIX . self::MARK_AS_CONTACTS_SUITE . $this->suite_access_token, Json::encode($data));
            echo $result;
            if ($result) {
                $json = Json::decode($result, true);

                if (!$json || !empty($json['errcode']) || $json['errcode'] != 0) {
                    $this->errCode = $json['errcode'];
                    $this->errMsg = $json['errmsg'];
                    return false;
                }
                return $json;
            }
            return false;
        }

        /**
         * 处理各种token过期，或者其他异常返回码
         */
        public function __destruct() {
            switch ($this->errCode) {
            //access_token超时
            case (42001):
                if ($this->isCallbackMode) {
                    MCache::qyWechatToken($this->appid, 'clear');
                } else {
                    MCache::accessToken($this->permanent_code, 'clear');
                }
                break;
            //suite_access_token超时
            case (42009):
                MCache::suite_access_token($this->suite_id, 'clear');
                break;
            //suitetoken无效
            case (48003):
                MCache::suite_access_token($this->suite_id, 'clear');
                break;
            case (41001):
                MCache::suite_access_token($this->suite_id, 'clear');
                break;
            case (40014): //无效的access_token
                MCache::suite_access_token($this->suite_id, 'clear');
                break;
            default:
                break;
            }
        }
    }
}
