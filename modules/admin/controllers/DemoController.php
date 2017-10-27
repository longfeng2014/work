<?php
namespace modules\admin\controllers;
use modules\admin\models\Demo;
use yii\web\UploadedFile;
use Yii;

class DemoController extends BaseController {

    public function actionLst() 
    {   
    	// $this->layout = 'xiao';
    	$arr =Demo::find()->offset(0)->limit(1,5)->asArray()->all();
      return $this->render('lst');
    }
    public function actionData()
    {   
    	$page = Yii::$app->request->get('page');
    	$limit = Yii::$app->request->get('limit');
    	$pageSize= $limit * ($page - 1);
    	$arr =Demo::find()->offset($pageSize)->limit($limit)->asArray()->all();
        $count = Demo::find()->count();
        shows($count,$arr);
	  } 
    public function actionAdd()
    { 
         $model = new Demo();
         if(Yii::$app->request->isPost){
         	 $post = Yii::$app->request->post();
         	 dumps($post);die;
         }
         return $this->render('add' , ['model' => $model]);
    }
    public function actionImg()
    {
        return $this->render('img');
    }

    public function actionImgs()
    {   
        $model = new Demo();
        $img = UploadedFile::getInstance($model, 'img');
        // $dir = "../../public/uploads/".date("Ymd");
        $a = '2017';
        $dir = 'uploads/amdin/';
        $dirs = $dir.date('Y');
        $dir2 = $dirs . '/' . date('m');
        if (!is_dir($dir)){
            mkdir($dir);
        }
        if(!is_dir($dirs))
              mkdir($dirs);
        if(!is_dir($dir2))
              mkdir($dir2);
        if ($img) {
              $fileNames = uniqid();
              $img->saveAs( $dir2 . '/' . $fileNames . '.' . $img->extension);
        }
        $url = $dir2 . '/' . $fileNames . '.' . $img->extension;
        $msg['code'] =200;
        $msg['url'] = $url;
        $msg['imgid'] = $fileNames;
        return json_encode($msg);
    }

}
