<?php
namespace modules\admin\models;
use Yii;


class Upload extends \common\base\ActiveRecord
{
    public static function tableName()
    {
        return '{{%Upload}}';
    }
    public function attributeLabels(){
         return [
           'title'=>'标题',
           'content'=>'内容',
           'sex'=>'性别',
         ];
    }

    public function rules()                                                                                                      
    {                                                                                                                           
       return [
          [['img'], 'file']
      ];                                                                                                   
    }

}
