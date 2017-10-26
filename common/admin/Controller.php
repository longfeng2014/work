<?php
/**
 * 控制器基类
 *
 * @author tasal<fei.he@pcstars.com>
 * @version $Id: Controller.php 43220 2017-07-02 03:16:19Z A1165 $
 */

namespace common\admin;

use Yii;
use yii\filters\VerbFilter;
use common\admin\components\MyBehavior;

class Controller extends \common\base\Controller {

    // public $layout = '@modules/backend/views/layouts/main';

    public function init() {
        parent::init();

    }

    /*public function behaviors()
    {
        return [
            //附加行为
            'myBehavior' => MyBehavior::className(),
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }*/

    /*public function checkNode($node, $myNodes) {

        $weAccountId = intval(Yii::$app->session['weaccount']['id']);

        if (empty($node)) {
            $this->dies('对不起，您无权访问此页面！请联系创建者授权。');
        }

        //状态为2时代表不需要验证的节点, add by tasal 20150921
        if ($node['status'] == 2) {
            return true;
        }

        //访问指定用户才可访问的节点时, 注意此处并没有验证父节点. 根据设置可能会出现无法访问父节点但可以访问子节点的情况
        if ($node['allow_aids'] && strpos($node['allow_aids'], (string) $weAccountId) === false) {
            $this->dies('对不起，您无权访问此页面！请联系创建者授权。');
        }

        if (in_array($node['id'], $myNodes)) {
            return true;
        } else {
            $this->dies('对不起，您无权访问此页面！请联系创建者授权。');
        }
    }*/
}
