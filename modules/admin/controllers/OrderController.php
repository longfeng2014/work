<?php
namespace modules\admin\controllers;

use Yii;
use modules\admin\models\Menu;

class OrderController extends BaseController {

    public function actionIndex() {
        // $modelList = Menu::find()->asArray()->all();

        // print_r($modelList);die;
        // $this->renderJson($modelList);

        return $this->render('index');
    }

    /**
     * [actionIndex description]
     * @author longkui <jianglongkui@mmkongjian.com>
     * @return [type] [description]
     */
    public function actionSelect() {
        $modelList = Menu::find()->asArray()->all();

        print_r($modelList);die;
        // $this->renderJson($modelList);

        return $this->render('index');
    }

    /**
     * [actionIndex description]
     * @author longkui <jianglongkui@mmkongjian.com>
     * @return [type] [description]
     */
    public function actionList() {
        // $modelList = Menu::find()->asArray()->all();

        print_r('list1111');die;
        // $this->renderJson($modelList);

        return $this->render('index');
    }

}
