<?php
namespace modules\admin\controllers;
use modules\admin\models\Upload;
use yii\web\UploadedFile;
use Yii;

class UploadController extends BaseController {
    /**
     * [actionImgs 图片上传方法]
     * @return [type] [description]
     */
    public function actionImgs()
    {   
        $model = new Upload();
        $img = UploadedFile::getInstance($model, 'img');
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
