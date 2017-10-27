<?php
/**
 * 前台控制器基类
 *
 */

namespace common\wechat;

use common\librarys\EScrypt;
use common\models\DebugLog;
use Yii;

class Controller extends \common\base\Controller {

    public $layout = '@modules/wechat/views/layouts/main';
    public $jsApiList = [];
    public $menuApiList = [];
    public $noMui = 0;

    public function init() {
        parent::init();
    }

    /*protected function encodeShare($get) {
        return (new EScrypt(CRYPT_KEY))->encrypt($get);
    }
    protected function decodeShare($share) {
        return (new EScrypt(CRYPT_KEY))->decrypt($share);
    }*/


    /**
     *  初始化微信端登录事件
     * @author longkui <longkui.jiang@bangongyi.com>
     * @return [type] [description]
     */
    protected function initLogin() {
        
    }
}
