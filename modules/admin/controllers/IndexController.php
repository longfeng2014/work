<?php
namespace modules\admin\controllers;

use Yii;
use modules\admin\models\Menu;

class IndexController extends BaseController {

    public function actionIndex() {
        $modelList = Menu::find()->asArray()->all();

        // print_r($modelList);die;
        // $this->renderJson($modelList);

        return $this->render('index');
    }
}
