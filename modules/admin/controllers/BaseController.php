<?php
namespace modules\admin\controllers;

use Yii;
use modules\admin\models\LoginForm;
use yii\filters\VerbFilter;
use modules\admin\components\AccessControl;
use modules\admin\models\Config;

class BaseController extends \common\admin\Controller {

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['login', 'error'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    //'logout' => ['post'],
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * 初始化配置信息
     * 网站配置或模板配置等
     */
    public function init() {
        parent::init();
        Yii::$app->params['basic'] = Config::getConfigs('basic');
        return true;
    }

    /**
     * 后台登录
     */
    public function actionLogin() {
        $this->layout = false;
        if (!Yii::$app->user->isGuest) return $this->goHome();

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()){
            return $this->redirect('/admin');
            // return $this->goBack();
        }else{
             return $this->render('login', [
                                            'model' => $model,
                                        ]);
        }
    }

    /**
     * 退出登录
     */
    public function actionLogout() {
        Yii::$app->user->logout();
        return $this->redirect('/admin/index/login');
        // return $this->goHome();
    }

}