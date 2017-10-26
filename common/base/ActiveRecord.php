<?php
/**
 * AR基类
 *
 * @author tasal<fei.he@pcstars.com>
 * @version $Id: ActiveRecord.php 36424 2017-01-24 10:08:59Z A1165 $
 */

namespace common\base;

use Yii;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;

class ActiveRecord extends \yii\db\ActiveRecord
{
    public function init()
    {
        parent::init();
    }

    public static $tableName;

    public static function tableName()
    {
        if(empty(static::$tableName)){
            return '{{%' . Inflector::camel2id( StringHelper::basename(get_called_class()), '_') . '}}';
        }else{
            if(strpos(static::$tableName,'_')===false){
                return '{{%' . Inflector::camel2id(static::$tableName, '_') . '}}';
            }else{
                return '{{%' . static::$tableName . '}}';
            }
        }
    }

    /**
     * 根据Schema生成默认规则
     * @return array
     */
    public function rules()
    {
        $rules=[];
        foreach ($this->getTableSchema()->columns as $column) {
            if($column->autoIncrement===true)continue;
            if($column->allowNull===false){
                if($column->defaultValue===null){
                    $rules[]=[$column->name,'required'];
                }else{
                    $rules[]=[$column->name,'default','value'=>$column->defaultValue];
                }
            }
            switch ($column->type) {
                case 'integer':
                    $rules[]=[$column->name, 'integer'];
                    break;
                case 'smallint':
                    //tinyint的Type也是smallint,只能根据dbtype判断,暂时忽略
                    $rules[]=$column->unsigned?[$column->name, 'integer','min'=>0]:[$column->name,'integer'];
                    break;
                case 'string':
                    if($column->enumValues){
                        $rules[]=[$column->name, 'in', 'range' =>$column->enumValues];
                    }else{
                        //set类型的enumValues为null,size为0,只能根据dbType->"set('val1','val2')"判断,暂时忽略
                        if($column->size==0) break;
                        $rules[]=[$column->name, 'string', 'max' =>$column->size];
                    }
                    break;
                case 'text':
                    $textMap = ['tinytext'=>255,'text'=>65535,'mediumtext'=>16777215,'longtext'=>4294967295];
                    $rules[]=[$column->name, 'string', 'max' =>$textMap[$column->dbType]?:65535];
                    break;
                //date,time,datetime,decimal,double,(precision,scale),float ...
                default:
                    break;
            }
        }
        return $rules;
    }

    /**
     * 自动提取字段注释中( ,;:)字符前的文字作为字段名
     * @return array
     */
    public function attributeLabels()
    {
        $labels=[];
        foreach ($this->getTableSchema()->columns as $column) {
            $labels[$column->name]=current(preg_split('/ |\(|\,|\;|\:|（|，|；|：/',$column->comment));
        }
        return $labels;
    }

    public static function find()
    {
        return Yii::createObject(ActiveQuery::className(), [get_called_class()]);
    }
}
