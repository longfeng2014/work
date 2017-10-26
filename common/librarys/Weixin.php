<?php
/**
 * 基于YII的微信公众平台扩展类
 * 功能 : 模拟登录官网, 实现一些API不能完成的工作
 *           如: 读取用户信息, 同步素材库, 主动推送消息 等
 * 说明 : 用户id为通过getMsg()方法获取的FakeId值
 * 注意 : 此类需Snoopy
 * 
 * @author tasal<fei.he@pcstars.com>
 * @version 1.0
 * $Id: Weixin.php 3192 2014-12-19 08:28:58Z A1165 $
 */

namespace common\librarys;

class Weixin {
    private $encoding_aeskey;//43位随机字符串
    private $callback_encrypt_mode;//加密模式
    private $cookie;
    private $_cookiename;
    private $_cookieprefix='weixin_login_cookie_';
    private $_cookieexpired = 3600;
    private $_account;
    private $_password;
    private $_verify;
    private $_logcallback=array('Yii','log');
    private $_token;
    private $_logincode;
    private $_loginuser;
    private $_loginErrMsgArr=array('-1'=>'系统错误。','-2'=>'帐号或密码错误','-23'=>'密码错误。','-4'=>'不存在该帐户。','-5'=>'访问受限。','-6'=>'需要输入验证码','-7'=>'此帐号已绑定私人微信号，不可用于公众平台登录。','-8'=>'请输入验证码','-27'=>'验证码输入错误','-94'=>'请使用邮箱登录。','-100'=>'暂不支持海外帐号','-200'=>'因频繁提交虚假资料，该帐号被拒绝登录。','10'=>'该公众会议号已经过期，无法再登录使用。','65201'=>'成功登录，正在跳转...','65202'=>'成功登录，正在跳转...','0'=>'成功登录，正在跳转...','default'=>'未知的返回。');

    public $debug = false;
    public $errCode = 0;
    public $errMsg = '';
    /*@暂 time:20141024 author:zhuwei新增encoding_aeskey
    *callback_encrypt_mode参数加密模式
    */
    public function __construct($options=array()) {
        $this->_account = isset($options['account']) ? $options['account'] : (isset(Yii::app()->session['weaccount']['we_username'])?Yii::app()->session['weaccount']['we_username'] : '');
        $this->_password = isset($options['password']) ? $options['password'] : (isset(Yii::app()->session['weaccount']['we_password'])?Yii::app()->session['weaccount']['we_password'] : '');
        $this->encoding_aeskey=!empty($options['encoding_aeskey'])?$options['encoding_aeskey']:Helper::randString(43);//默认随机生成
        $this->callback_encrypt_mode=!empty($options['callback_encrypt_mode'])?$options['callback_encrypt_mode']:0;//默认明文
        //验证码
        $this->_verify = isset($options['verify'])?$options['verify']:'';
        $this->_logcallback = isset($options['logcallback']) ? $options['logcallback'] : $this->_logcallback;
        $this->_cookiename = $this->_cookieprefix . $this->_account;
        $this->debug = isset($options['debug']) ? $options['debug'] : YII_DEBUG;
        $this->cookie = $this->getCookie($this->_cookiename);
    }
    /**
     * 获取公众号信息, 
     * @return [type] [description]
     */
    public function getAdvancedInfo()
    {
        $send_snoopy = new Snoopy;
//        $send_snoopy->referer = "https://mp.weixin.qq.com/cgi-bin/advanced?action=dev&t=advanced/dev&token={$this->_token}&lang=zh_CN";
        $send_snoopy->referer = "https://mp.weixin.qq.com/advanced/advanced?action=dev&t=advanced/dev&token={$this->_token}&lang=zh_CN";
        $send_snoopy->rawheaders['Cookie'] = $this->cookie;
//        $url = "https://mp.weixin.qq.com/cgi-bin/advanced?action=dev&t=advanced/dev&token={$this->_token}&lang=zh_CN&f=json";
        $url = "https://mp.weixin.qq.com/advanced/advanced?action=dev&t=advanced/dev&token={$this->_token}&lang=zh_CN&f=json";
        $send_snoopy->fetch($url);
        $this->log($send_snoopy->results);
        return $send_snoopy->results;
    }

    /*
     * 设置开发模式
     *
     * 成功 {"ret":"0", "msg":"980226942"}
     *  
     */
    public function  setInterface($url,$token){
        $send_snoopy = new Snoopy;
        $post = array();
        $post['callback_token']=$token;
        $post['url']=$url;
        $post['callback_encrypt_mode']=$this->callback_encrypt_mode;
        $post['encoding_aeskey']=$this->encoding_aeskey;
        $post['operation_seq']=mt_rand(2000,3000).substr(time(),-5,5);//201166319随机字符串
        //$send_snoopy->referer = "https://mp.weixin.qq.com/cgi-bin/advanced?action=interface&t=advanced/interface&token={$this->_token}&lang=zh_CN";
        $send_snoopy->referer = "https://mp.weixin.qq.com/advanced/advanced?action=interface&t=advanced/interface&token={$this->_token}&lang=zh_CN";
        $send_snoopy->rawheaders['Cookie'] = $this->cookie;
//        $submit = "https://mp.weixin.qq.com/cgi-bin/callbackprofile?t=ajax-response&token={$this->_token}&lang=zh_CN";
        $submit = "https://mp.weixin.qq.com/advanced/callbackprofile?t=ajax-response&token={$this->_token}&lang=zh_CN";
        $send_snoopy->submit($submit, $post);
        $this->log($send_snoopy->results);
        $result = json_decode($send_snoopy->results);
        $result=$result->base_resp;
        if($result->ret != 0){
            $this->errCode=$result->ret;
            $this->errMsg=$result->err_msg;
            return false;
        }else{
            return true;
        }
    }

    /*
     * 切换高级功能
     * @param string $mode 切换模式, dev为开发模式, edit为编辑模式
     * @param string $action 模式开关, open为开启,close为关闭
     * @return json
     */
    public function switchAdvanced($mode='dev',$action='open')
    {
        $send_snoopy = new Snoopy;
        $post = array();
        if($mode=='dev'){
            $post['type']=2;
        }else{
            $post['type']=1;
        }
        if($action=='open'){
            $post['flag']=1;
        }else{
            $post['flag']=0;
        }
        $post['token']=$this->_token;
//        $send_snoopy->referer = "https://mp.weixin.qq.com/cgi-bin/advanced?action=interface&t=advanced/interface&token={$this->_token}&lang=zh_CN";
        $send_snoopy->referer = "https://mp.weixin.qq.com/advanced/advanced?action=dev&t=advanced/dev&token={$this->_token}&lang=zh_CN";
        $send_snoopy->rawheaders['Cookie'] = $this->cookie;
//        $submit = "https://mp.weixin.qq.com/cgi-bin/skeyform?form=advancedswitchform&lang=zh_CN";
        $submit = "https://mp.weixin.qq.com/misc/skeyform?form=advancedswitchform&lang=zh_CN";
        $send_snoopy->submit($submit, $post);
        $this->log($send_snoopy->results);
        $result = json_decode($send_snoopy->results);
        $result = $result->base_resp;

        if($result->ret != '0'){
            $this->errCode=$result->ret;
            $this->errMsg=$result->err_msg;
            return false;
        }else{
            return true;
        }
    }

    /**
     * 主动发消息
     * @param  string $id      用户的uid(即FakeId)
     * @param  string $content 发送的内容
     */
    public function send($id, $content) {
        $send_snoopy = new Snoopy;
        $post = array();
        $post['tofakeid'] = $id;
        $post['type'] = 1;
        $post['token'] = $this->_token;
        $post['content'] = $content;
        $post['ajax'] = 1;
        $send_snoopy->referer = "https://mp.weixin.qq.com/cgi-bin/singlesendpage?t=message/send&action=index&tofakeid=$id&token={$this->_token}&lang=zh_CN";
        $send_snoopy->rawheaders['Cookie'] = $this->cookie;
        $submit = "https://mp.weixin.qq.com/cgi-bin/singlesend?t=ajax-response";
        $send_snoopy->submit($submit, $post);
        $this->log($send_snoopy->results);
        return $send_snoopy->results;
    }

    /**
     * 获取用户列表列表
     * @param $page 页码(从0开始)
     * @param $pagesize 每页大小
     * @param $groupid 分组id
     * @return array ({contacts:[{id:12345667,nick_name:"昵称",remark_name:"备注名",group_id:0},{}....]})
     */
    function getUserList($page = 0, $pagesize = 10, $groupid = 0) {
        $send_snoopy = new Snoopy;
        $t = time() . strval(mt_rand(100, 999));
        $send_snoopy->referer = "https://mp.weixin.qq.com/cgi-bin/contactmanage?t=user/index&pagesize=" . $pagesize . "&pageidx=" . $page . "&type=0&groupid=0&lang=zh_CN&token=" . $this->_token;
        $send_snoopy->rawheaders['Cookie'] = $this->cookie;
        $submit = "https://mp.weixin.qq.com/cgi-bin/contactmanage?t=user/index&pagesize=" . $pagesize . "&pageidx=" . $page . "&type=0&groupid=$groupid&lang=zh_CN&f=json&token=" . $this->_token;
        $send_snoopy->fetch($submit);
        $result = $send_snoopy->results;
        $this->log('userlist:' . $result);
        $json = json_decode($result, true);
        if (isset($json['contact_list'])) {
            $json = json_decode($json['contact_list'], true);
            if (isset($json['contacts']))
                return $json['contacts'];
        }
        return false;
    }

    /**
     * 获取分组列表
     * @return array
     */
    function getGroupList() {
        $send_snoopy = new Snoopy;
        $t = time() . strval(mt_rand(100, 999));
        $send_snoopy->referer = "https://mp.weixin.qq.com/cgi-bin/contactmanage?t=user/index&pagesize=10&pageidx=0&type=0&groupid=0&lang=zh_CN&token=" . $this->_token;
        $send_snoopy->rawheaders['Cookie'] = $this->cookie;
        $submit = "https://mp.weixin.qq.com/cgi-bin/contactmanage?t=user/index&pagesize=10&pageidx=0&type=0&groupid=0&lang=zh_CN&f=json&token=" . $this->_token;
        $send_snoopy->fetch($submit);
        $result = $send_snoopy->results;
        $this->log('userlist:' . $result);
        $json = json_decode($result, true);
        if (isset($json['group_list'])) {
            $json = json_decode($json['group_list'], true);
            if (isset($json['groups']))
                return $json['groups'];
        }
        return false;
    }

    /**
     * 获取图文信息列表
     * @param $page 页码(从0开始)
     * @param $pagesize 每页大小
     * @return array
     */
    public function getNewsList($page, $pagesize = 10) {
        $send_snoopy = new Snoopy;
        $t = time() . strval(mt_rand(100, 999));
        $type = 10;
        $begin = $page * $pagesize;
        $send_snoopy->referer = "https://mp.weixin.qq.com/cgi-bin/masssendpage?t=mass/send&token=" . $this->_token . "&lang=zh_CN";
        $send_snoopy->rawheaders['Cookie'] = $this->cookie;
        $submit = "https://mp.weixin.qq.com/cgi-bin/appmsg?token=" . $this->_token . "&lang=zh_CN&type=$type&action=list&begin=$begin&count=$pagesize&f=json&random=0." . $t;
        $send_snoopy->fetch($submit);
        $result = $send_snoopy->results;
        $this->log('newslist:' . $result);
        $json = json_decode($result, true);
        if (isset($json['app_msg_info'])) {
            return $json['app_msg_info'];
        }
        return false;
    }

    /**
     * 发送图文信息,必须从图文库里选取消息ID发送
     * @param  string $id      用户的uid(即FakeId)
     * @param  string $msgid 图文消息id
     */
    public function sendNews($id, $msgid) {
        $send_snoopy = new Snoopy;
        $post = array();
        $post['tofakeid'] = $id;
        $post['type'] = 10;
        $post['token'] = $this->_token;
        $post['fid'] = $msgid;
        $post['appmsgid'] = $msgid;
        $post['error'] = 'false';
        $post['ajax'] = 1;
        $send_snoopy->referer = "https://mp.weixin.qq.com/cgi-bin/singlemsgpage?fromfakeid={$id}&msgid=&source=&count=20&t=wxm-singlechat&lang=zh_CN";
        $send_snoopy->rawheaders['Cookie'] = $this->cookie;
        $submit = "https://mp.weixin.qq.com/cgi-bin/singlesend?t=ajax-response";
        $send_snoopy->submit($submit, $post);
        $this->log($send_snoopy->results);
        return $send_snoopy->results;
    }

    /**
     * 上传附件(图片/音频/视频)
     * @param string $filepath 本地文件地址
     * @param int $type 文件类型: 2:图片 3:音频 4:视频
     */
    public function uploadFile($filepath, $type = 2) {
        $send_snoopy = new Snoopy;
        $send_snoopy->referer = "http://mp.weixin.qq.com/cgi-bin/indexpage?t=wxm-upload&lang=zh_CN&type=2&formId=1";
        $t = time() . strval(mt_rand(100, 999));
        $post = array('formId' => '');
        $postfile = array('uploadfile' => $filepath);
        $send_snoopy->rawheaders['Cookie'] = $this->cookie;
        $send_snoopy->set_submit_multipart();
        $submit = "http://mp.weixin.qq.com/cgi-bin/uploadmaterial?cgi=uploadmaterial&type=$type&token=" . $this->_token . "&t=iframe-uploadfile&lang=zh_CN&formId=	file_from_" . $t;
        $send_snoopy->submit($submit, $post, $postfile);
        $tmp = $send_snoopy->results;
        $this->log('upload:' . $tmp);
        preg_match("/formId,.*?\'(\d+)\'/", $tmp, $matches);
        if (isset($matches[1])) {
            return $matches[1];
        }
        return false;
    }

    /**
     * 创建图文消息
     * @param array $title 标题
     * @param array $summary 摘要
     * @param array $content 内容
     * @param array $photoid 素材库里的图片id(可通过uploadFile上传后获取)
     * @param array $srcurl 原文链接
     * @return json
     */
    public function addPreview($title, $author, $summary, $content, $photoid, $srcurl = '') {
        $send_snoopy = new Snoopy;
        $send_snoopy->referer = 'https://mp.weixin.qq.com/cgi-bin/operate_appmsg?lang=zh_CN&sub=edit&t=wxm-appmsgs-edit-new&type=10&subtype=3&token=' . $this->_token;

        $submit = "https://mp.weixin.qq.com/cgi-bin/operate_appmsg?lang=zh_CN&t=ajax-response&sub=create&token=" . $this->_token;
        $send_snoopy->rawheaders['Cookie'] = $this->cookie;

        $send_snoopy->set_submit_normal();
        $post = array(
            'token' => $this->_token,
            'type' => 10,
            'lang' => 'zh_CN',
            'sub' => 'create',
            'ajax' => 1,
            'AppMsgId' => '',
            'error' => 'false',
        );
        if (count($title) == count($author) && count($title) == count($summary) && count($title) == count($content) && count($title) == count($photoid)) {
            $i = 0;
            foreach ($title as $v) {
                $post['title' . $i] = $title[$i];
                $post['author' . $i] = $author[$i];
                $post['digest' . $i] = $summary[$i];
                $post['content' . $i] = $content[$i];
                $post['fileid' . $i] = $photoid[$i];
                if ($srcurl[$i])
                    $post['sourceurl' . $i] = $srcurl[$i];

                $i++;
            }
        }
        $post['count'] = $i;
        $post['token'] = $this->_token;
        $send_snoopy->submit($submit, $post);
        $tmp = $send_snoopy->results;
        $this->log('step2:' . $tmp);
        $json = json_decode($tmp, true);
        return $json;
    }

    /**
     * 发送媒体文件
     * @param $id 用户的uid(即FakeId)
     * @param $fid 文件id
     * @param $type 文件类型
     */
    public function sendFile($id, $fid, $type) {
        $send_snoopy = new Snoopy;
        $post = array();
        $post['tofakeid'] = $id;
        $post['type'] = $type;
        $post['token'] = $this->_token;
        $post['fid'] = $fid;
        $post['fileid'] = $fid;
        $post['error'] = 'false';
        $post['ajax'] = 1;
        $send_snoopy->referer = "https://mp.weixin.qq.com/cgi-bin/singlemsgpage?fromfakeid={$id}&msgid=&source=&count=20&t=wxm-singlechat&lang=zh_CN";
        $send_snoopy->rawheaders['Cookie'] = $this->cookie;
        $submit = "https://mp.weixin.qq.com/cgi-bin/singlesend?t=ajax-response";
        $send_snoopy->submit($submit, $post);
        $result = $send_snoopy->results;
        $this->log('sendfile:' . $result);
        $json = json_decode($result, true);
        if ($json && $json['ret'] == 0)
            return true;
        else
            return false;
    }

    /**
     * 获取素材库文件列表
     * @param $type 文件类型: 2:图片 3:音频 4:视频
     * @param $page 页码(从0开始)
     * @param $pagesize 每页大小
     * @return array
     */
    public function getFileList($type, $page, $pagesize = 10) {
        $send_snoopy = new Snoopy;
        $t = time() . strval(mt_rand(100, 999));
        $begin = $page * $pagesize;
        $send_snoopy->referer = "https://mp.weixin.qq.com/cgi-bin/masssendpage?t=mass/send&token=" . $this->_token . "&lang=zh_CN";
        $send_snoopy->rawheaders['Cookie'] = $this->cookie;
        $submit = "https://mp.weixin.qq.com/cgi-bin/filepage?token=" . $this->_token . "&lang=zh_CN&type=$type&random=0." . $t . "&begin=$begin&count=$pagesize&f=json";
        $send_snoopy->fetch($submit);
        $result = $send_snoopy->results;
        $this->log('filelist:' . $result);
        $json = json_decode($result, true);
        if (isset($json['page_info']))
            return $json['page_info'];
        else
            return false;
    }

    /**
     * 发送图文信息,必须从库里选取文件ID发送
     * @param  string $id      用户的uid(即FakeId)
     * @param  string $fid 文件id
     */
    public function sendImage($id, $fid) {
        return $this->sendFile($id, $fid, 2);
    }

    /**
     * 发送语音信息,必须从库里选取文件ID发送
     * @param  string $id      用户的uid(即FakeId)
     * @param  string $fid 语音文件id
     */
    public function sendAudio($id, $fid) {
        return $this->sendFile($id, $fid, 3);
    }

    /**
     * 发送视频信息,必须从库里选取文件ID发送
     * @param  string $id      用户的uid(即FakeId)
     * @param  string $fid 视频文件id
     */
    public function sendVideo($id, $fid) {
        return $this->sendFile($id, $fid, 4);
    }

    /**
     * 发送预览图文消息
     * @param string $account 账户名称(user_name)
     * @param string $title 标题
     * @param string $summary 摘要
     * @param string $content 内容
     * @param string $photoid 素材库里的图片id(可通过uploadFile上传后获取)
     * @param string $srcurl 原文链接
     * @return json
     */
    public function sendPreview($account, $title, $summary, $content, $photoid, $srcurl = '') {
        $send_snoopy = new Snoopy;
        $submit = "https://mp.weixin.qq.com/cgi-bin/operate_appmsg?sub=preview&t=ajax-appmsg-preview";
        $send_snoopy->set_submit_normal();
        $send_snoopy->rawheaders['Cookie'] = $this->cookie;
        $send_snoopy->referer = 'https://mp.weixin.qq.com/cgi-bin/operate_appmsg?sub=edit&t=wxm-appmsgs-edit-new&type=10&subtype=3&lang=zh_CN';
        $post = array(
            'AppMsgId' => '',
            'ajax' => 1,
            'content0' => $content,
            'count' => 1,
            'digest0' => $summary,
            'error' => 'false',
            'fileid0' => $photoid,
            'preusername' => $account,
            'sourceurl0' => $srcurl,
            'title0' => $title,
        );
        $post['token'] = $this->_token;
        $send_snoopy->submit($submit, $post);
        $tmp = $send_snoopy->results;
        $this->log('sendpreview:' . $tmp);
        $json = json_decode($tmp, true);
        return $json;
    }

    /**
     * 获取用户的信息
     * @param  string $id 用户的uid(即FakeId)
     * @return array  {fake_id:100001,nick_name:'昵称',user_name:'用户名',signature:'签名档',country:'中国',province:'广东',city:'广州',gender:'1',group_id:'0'},groups:{[id:0,name:'未分组',cnt:20]}
     */
    public function getInfo($id) {
        $send_snoopy = new Snoopy;
        $send_snoopy->rawheaders['Cookie'] = $this->cookie;
        $t = time() . strval(mt_rand(100, 999));
        $send_snoopy->referer = "https://mp.weixin.qq.com/cgi-bin/getmessage?t=wxm-message&lang=zh_CN&count=50&token=" . $this->_token;
        $submit = "https://mp.weixin.qq.com/cgi-bin/getcontactinfo";
        $post = array('ajax' => 1, 'lang' => 'zh_CN', 'random' => '0.' . $t, 'token' => $this->_token, 't' => 'ajax-getcontactinfo', 'fakeid' => $id);
        $send_snoopy->submit($submit, $post);
        $this->log($send_snoopy->results);
        $result = json_decode($send_snoopy->results, true);
        if (isset($result['contact_info'])) {
            return $result['contact_info'];
        }
        return false;
    }

    /**
     * 获取消息更新数目
     * @param int $lastid 最近获取的消息ID,为0时获取总消息数目
     * @return int 数目
     */
    public function getNewMsgNum($lastid = 0) {
        $send_snoopy = new Snoopy;
        $send_snoopy->rawheaders['Cookie'] = $this->cookie;
        $send_snoopy->referer = "https://mp.weixin.qq.com/cgi-bin/getmessage?t=wxm-message&lang=zh_CN&count=50&token=" . $this->_token;
        $submit = "https://mp.weixin.qq.com/cgi-bin/getnewmsgnum?t=ajax-getmsgnum&lastmsgid=" . $lastid;
        $post = array('ajax' => 1, 'token' => $this->_token);
        $send_snoopy->submit($submit, $post);
        $this->log($send_snoopy->results);
        $result = json_decode($send_snoopy->results, 1);
        if (!$result) {
            return false;
        }
        return intval($result['newTotalMsgCount']);
    }

    /**
     * 获取最新一条消息, 此方法获取的消息id可以作为检测新消息的$lastid依据
     * @return array {"id":"最新一条id","type":"类型号(1为文字,2为图片,3为语音)","fileId":"0","hasReply":"0","fakeId":"用户uid","nickName":"昵称","dateTime":"时间戳","content":"文字内容","playLength":"0","length":"0","source":"","starred":"0","status":"4"}        
     */
    public function getTopMsg() {
        $send_snoopy = new Snoopy;
        $send_snoopy->rawheaders['Cookie'] = $this->cookie;
        $send_snoopy->referer = "https://mp.weixin.qq.com/cgi-bin/message?t=message/list&count=20&day=7&lang=zh_CN&token=" . $this->_token;
        $submit = "https://mp.weixin.qq.com/cgi-bin/message?t=message/list&f=json&count=20&day=7&lang=zh_CN&token=" . $this->_token;
        $send_snoopy->fetch($submit);
        $this->log($send_snoopy->results);
        $result = $send_snoopy->results;
        $json = json_decode($result, true);
        if (isset($json['msg_items'])) {
            $json = json_decode($json['msg_items'], true);
            if (isset($json['msg_item']))
                return array_shift($json['msg_item']);
        }
        return false;
    }

    /**
     * 获取新消息, 列表将返回消息id, 用户id, 消息类型, 文字消息等参数
     * @param $lastid 传入最后的消息id编号,为0则从最新一条起倒序获取
     * @param $offset lastid起算第一条的偏移量
     * @param $perpage 每页获取多少条
     * @param $day 最近几天消息(0:今天,1:昨天,2:前天,3:更早,7:五天内)
     * @param $today 是否只显示今天的消息, 与$day参数不能同时大于0
     * @param $star 是否星标组信息
     * @return array[] 同getTopMsg() 返回的字段结构相同
     */
    public function getMsg($lastid = 0, $offset = 0, $perpage = 20, $day = 7, $today = 0, $star = 0) {
        $send_snoopy = new Snoopy;
        $send_snoopy->rawheaders['Cookie'] = $this->cookie;
        $send_snoopy->referer = "https://mp.weixin.qq.com/cgi-bin/message?t=message/list&lang=zh_CN&count=50&token=" . $this->_token;
        $lastid = $lastid === 0 ? '' : $lastid;
        $addstar = $star ? '&action=star' : '';
        $submit = "https://mp.weixin.qq.com/cgi-bin/message?t=message/list&f=json&lang=zh_CN{$addstar}&count=$perpage&timeline=$today&day=$day&frommsgid=$lastid&offset=$offset&token=" . $this->_token;
        $send_snoopy->fetch($submit);
        $this->log($send_snoopy->results);
        $result = $send_snoopy->results;
        $json = json_decode($result, true);
        if (isset($json['msg_items'])) {
            $json = json_decode($json['msg_items'], true);
            if (isset($json['msg_item']))
                return $json['msg_item'];
        }
        return false;
    }

    /**
     * 获取图片消息, 若消息type类型为2, 调用此方法获取图片数据
     * @param int $msgid 消息id
     * @param string $mode 图片尺寸(large/small)
     * @return jpg二进制文件
     */
    public function getMsgImage($msgid, $mode = 'large') {
        $send_snoopy = new Snoopy;
        $send_snoopy->rawheaders['Cookie'] = $this->cookie;
        $send_snoopy->referer = "https://mp.weixin.qq.com/cgi-bin/getmessage?t=wxm-message&lang=zh_CN&count=50&token=" . $this->_token;
        $url = "https://mp.weixin.qq.com/cgi-bin/getimgdata?token=" . $this->_token . "&msgid=$msgid&mode=$mode&source=&fileId=0";
        $send_snoopy->fetch($url);
        $result = $send_snoopy->results;
        $this->log('msg image:' . $msgid . ';length:' . strlen($result));
        if (!$result) {
            return false;
        }
        return $result;
    }

    /**
     * 获取语音消息, 若消息type类型为3, 调用此方法获取语音数据
     * @param int $msgid 消息id
     * @return mp3二进制文件
     */
    public function getMsgVoice($msgid) {
        $send_snoopy = new Snoopy;
        $send_snoopy->rawheaders['Cookie'] = $this->cookie;
        $send_snoopy->referer = "https://mp.weixin.qq.com/cgi-bin/getmessage?t=wxm-message&lang=zh_CN&count=50&token=" . $this->_token;
        $url = "https://mp.weixin.qq.com/cgi-bin/getvoicedata?token=" . $this->_token . "&msgid=$msgid&fileId=0";
        $send_snoopy->fetch($url);
        $result = $send_snoopy->results;
        $this->log('msg voice:' . $msgid . ';length:' . strlen($result));
        if (!$result) {
            return false;
        }
        return $result;
    }

    /**
     * 获取登录授权码, 通过授权码才能获取二维码
     */
    public function getLoginCode() {
        if ($this->_logincode)
            return $this->_logincode;
        $t = time() . strval(mt_rand(100, 999));
        $codeurl = 'https://login.weixin.qq.com/jslogin?appid=wx782c26e4c19acffb&redirect_uri=https%3A%2F%2Fwx.qq.com%2Fcgi-bin%2Fmmwebwx-bin%2Fwebwxnewloginpage&fun=new&lang=zh_CN&_=' . $t;
        $send_snoopy = new Snoopy;
        $send_snoopy->fetch($codeurl);
        $result = $send_snoopy->results;
        if ($result) {
            preg_match("/window.QRLogin.uuid\s+=\s+\"([^\"]+)\"/", $result, $matches);
            if (count($matches) > 1) {
                $this->_logincode = $matches[1];
                $_SESSION['login_step'] = 0;
                return $this->_logincode;
            }
        }
        return $result;
    }

    /**
     * 通过授权码获取对应的二维码图片地址
     * @param string $code
     * @return string image url
     */
    public function getCodeImage($code = '') {
        if ($code == '')
            $code = $this->_logincode;
        if (!$code)
            return false;
        return 'http://login.weixin.qq.com/qrcode/' . $this->_logincode . '?t=webwx';
    }

    /**
     * 设置二维码对应的授权码
     * @param string $code
     * @return class $this
     */
    public function setLoginCode($code) {
        $this->_logincode = $code;
        return $this;
    }

    /**
     * 二维码登录验证
     * 鉴定是否登录成功,返回200为最终授权成功
     * @return status:
     * >=400: invaild code; 408: not auth and wait, 400,401: not valid or expired
     * 201: just scaned but not confirm
     * 200: confirm then you can get user info
     */
    public function verifyCode() {
        if (!$this->_logincode)
            return false;
        $t = time() . strval(mt_rand(100, 999));

        $url = 'https://login.weixin.qq.com/cgi-bin/mmwebwx-bin/login?uuid=' . $this->_logincode . '&tip=1&_=' . $t;
        $send_snoopy = new Snoopy;
        $send_snoopy->referer = "https://wx.qq.com/";
        $send_snoopy->fetch($url);
        $result = $send_snoopy->results;
        $this->log('step1:' . $result);
        if ($result) {
            preg_match("/window\.code=(\d+)/", $result, $matches);
            if (count($matches) > 1) {
                $status = intval($matches[1]);
                if ($status == 201)
                    $_SESSION['login_step'] = 1;
                if ($status == 200) {
                    preg_match("/ticket=([0-9a-z-_]+)&lang=zh_CN&scan=(\d+)/", $result, $matches);
                    $this->log('step2:' . print_r($matches, true));
                    if (count($matches) > 1) {
                        $ticket = $matches[1];
                        $scan = $matches[2];
                        $loginurl = 'https://wx.qq.com/cgi-bin/mmwebwx-bin/webwxnewloginpage?ticket=' . $ticket . '&lang=zh_CN&scan=' . $scan . '&fun=new';
                        $send_snoopy = new Snoopy;
                        $send_snoopy->referer = "https://wx.qq.com/";
                        $send_snoopy->fetch($loginurl);
                        $this->log('step3:' . print_r($send_snoopy->headers, true));
                        $cookie='';
                        foreach ($send_snoopy->headers as $key => $value) {
                            $value = trim($value);
                            if (strpos($value, 'Set-Cookie: ') !== false) {
                                $tmp = str_replace("Set-Cookie: ", "", $value);
                                $tmp = str_replace("Path=/;", "", $tmp);
                                $tmp = preg_replace("/Expires=.*?GMT/i", "", $tmp);
                                $tmp = str_replace("Domain=.qq.com; ", "", $tmp);
                                $cookie.=$tmp;
                            }
                        }
                        $cookie .="Domain=.qq.com;";
                        $this->cookie = $cookie;
                        $this->log('step4:' . $cookie);
                        $this->saveCookie($this->_cookiename, $this->cookie);
                    }
                }
                return $status;
            }
        }
        return false;
    }

    /**
     * 获取登录的cookie
     *
     * @param bool $is_array 是否以数值方式返回，默认否，返回字符串
     * @return string|array
     */
    public function getLoginCookie($is_array = false) {
        if (!$is_array)
            return $this->cookie;
        $c_arr = explode(';', $this->cookie);
        $cookie = array();
        foreach ($c_arr as $item) {
            $kitem = explode('=', trim($item));
            if (count($kitem) > 1) {
                $key = trim($kitem[0]);
                $val = trim($kitem[1]);
                if (!empty($val))
                    $cookie[$key] = $val;
            }
        }
        return $cookie;
    }

    /**
     * 授权登录后获取用户登录信息
     * @return array
     * @fixme 接收不到数据 by tasal 20131113
     */
    public function getLoginInfo() {
        if (!$this->cookie)
            return false;
        $t = time() . strval(mt_rand(100, 999));
        $send_snoopy = new Snoopy;
        $submit = 'https://wx.qq.com/cgi-bin/mmwebwx-bin/webwxinit?r=' . $t;
        $send_snoopy->rawheaders['Cookie'] = $this->cookie;
        $send_snoopy->referer = "https://wx.qq.com/";
        $send_snoopy->submit($submit, array());
        $this->log('login_info:' . $send_snoopy->results);
        $result = json_decode($send_snoopy->results, true);
        if ($result['BaseResponse']['Ret'] < 0)
            return false;
        $this->_loginuser = $result['User'];
        return $result;
    }

    /**
     *  获取头像
     *  @param string $fakeid 传入从用户信息接口获取到的头像地址
     *  @return bin jpg二进制文件, 图像数据, 设置header('Content-Type: image/jpg')可以输出jpg图像
     *  @tudo 保存到本地, 并返回路径
     */
    public function getAvatar($fakeid) {
        if (!$this->cookie)
            return false;
//        $url = 'https://mp.weixin.qq.com/cgi-bin/getheadimg?fakeid='.$fakeid.'&token='.$this->_token.'&lang=zh_CN';
        $r = rand(100000, 999999);
        $url = 'https://mp.weixin.qq.com/misc/getheadimg?token='.$this->_token.'&fakeid='.$fakeid."&lang=zh_CN";
        $send_snoopy = new Snoopy;
        $send_snoopy->rawheaders['Cookie'] = $this->cookie;
        $send_snoopy->referer = "https://mp.weixin.qq.com/cgi-bin/getmessage?t=wxm-message&lang=zh_CN&count=50&token=".$this->_token;
        $send_snoopy->fetch($url);
        $result = $send_snoopy->results;
        if ($result)
            return $result;
        else
            return false;
    }

    /**
     * 登出当前登录用户
     * @return boolean
     */
    public function logout() {
        if (!$this->cookie)
            return false;
        preg_match("/wxuin=(\w+);/", $this->cookie, $matches);
        if (count($matches) > 1)
            $uid = $matches[1];
        preg_match("/wxsid=(\w+);/", $this->cookie, $matches);
        if (count($matches) > 1)
            $sid = $matches[1];
        $this->log('logout: uid=' . $uid . ';sid=' . $sid);
        $send_snoopy = new Snoopy;
        $submit = 'https://wx.qq.com/cgi-bin/mmwebwx-bin/webwxlogout?redirect=1&type=1';
        $send_snoopy->rawheaders['Cookie'] = $this->cookie;
        $send_snoopy->referer = "https://wx.qq.com/";
        $send_snoopy->submit($submit, array('uin' => $uid, 'sid' => $sid));
        $this->deleteCookie($this->_cookiename);
        return true;
    }

    /**
     * 模拟登录获取cookie
     * @return [type] [description]
     */
    public function login() {
        $snoopy = new Snoopy;
        $submit = "https://mp.weixin.qq.com/cgi-bin/login?lang=zh_CN";
        $post["username"] = $this->_account;
        $post["pwd"] = md5($this->_password);
        $post["f"] = "json";
        $post["imgcode"] = $this->_verify;

        $snoopy->referer = "https://mp.weixin.qq.com/";
        $snoopy->submit($submit, $post);
        $cookie = '';
        $this->log($snoopy->results);
        $result = json_decode($snoopy->results, true);
        $result['ErrCode'] = $result['base_resp']['ret'];
        $result['ErrMsg'] = $result['base_resp']['err_msg'];
        if ($result['ErrCode']=="65201"||$result['ErrCode']=="65202"||$result['ErrCode']=="0")
        {
            foreach ($snoopy->headers as $key => $value) {
                $value = trim($value);
                if (preg_match('/^set-cookie:[\s]+([^=]+)=([^;]+)/i', $value, $match))
                $cookie .=$match[1] . '=' . $match[2] . '; ';
            }
            preg_match("/token=(\d+)/i", $result['redirect_url'], $matches);
            if ($matches) {
                $this->_token = $matches[1];
                $this->log('token:' . $this->_token);
            }
            $this->saveCookie($this->_cookiename, $cookie);
            return $cookie;
        }else{
            //登录失败才记录错误代码,判断是否登录成功的标记
            $this->errCode=$result['ErrCode'];
            $this->errMsg=isset($this->_loginErrMsgArr[$result['ErrCode']])?$this->_loginErrMsgArr[$result['ErrCode']]:$this->_loginErrMsgArr['default'];
            return false;
        }
    }

    /**
     * 把cookie写入缓存
     * @param  string $cookiename 缓存名
     * @param  string $content  内容
     * @return bool
     */
    public function saveCookie($cookiename, $content) {
        return Yii::app()->cache->set($cookiename, $content,$this->_cookieexpired);
    }

    /**
     * 读取cookie缓存内容
     * @param  string $cookiename 缓存名
     * @return string cookie
     */
    public function getCookie($cookiename) {
        $data=Yii::app()->cache->get($cookiename);
        if ($data) {
            $send_snoopy = new Snoopy;
            $send_snoopy->rawheaders['Cookie'] = $data;
            $send_snoopy->maxredirs = 0;
            $url = "https://mp.weixin.qq.com/cgi-bin/indexpage?t=wxm-index&lang=zh_CN";
            $send_snoopy->fetch($url);
            $header = implode(',', $send_snoopy->headers);
            $this->log('header:' . print_r($send_snoopy->headers, true));
            preg_match("/token=(\d+)/i", $header, $matches);
            if (empty($matches)) {
                return $this->login();
            } else {
                $this->_token = $matches[1];
                $this->log('token:' . $this->_token);
                return $data;
            }
        } else {
            return $this->login();
        }
    }

    /**
     * 验证cookie的有效性
     * @return bool
     */
    public function checkValid() {
        if (!$this->cookie || !$this->_token)
            return false;
        $send_snoopy = new Snoopy;
        $post = array('ajax' => 1, 'token' => $this->_token);
        $submit = "https://mp.weixin.qq.com/cgi-bin/getregions?id=1017&t=ajax-getregions&lang=zh_CN";
        $send_snoopy->rawheaders['Cookie'] = $this->cookie;
        $send_snoopy->submit($submit, $post);
        $result = $send_snoopy->results;
        if (json_decode($result, 1)) {
            return true;
        } else {
            return false;
        }
    }
    
    /*
     * 记录日志
     * @param string $log 日志内容
     * @return 
     */
    private function log($log) {
        if ($this->debug) {
            if (is_callable($this->_logcallback)) {
                 if (is_array($log))$log = print_r($log, true);
                call_user_func($this->_logcallback,$log);
            }
        }
    }
}
