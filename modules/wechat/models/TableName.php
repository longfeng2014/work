<?php
/**
 * 微信端模型示例
 */

namespace modules\wechat\models;

use Yii;

class TableName extends \common\base\ActiveRecord
{
    /**
     * @inheritdoc
     */
    /*public static function tableName()
    {
        return '{{%table_name}}';
    }*/

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'content', 'created_at', 'updated_at'], 'required'],
            [['id', 'views', 'is_delete'], 'integer'],
            [['content'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['title'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'content' => 'Content',
            'views' => 'Views',
            'is_delete' => 'Is Delete',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

}
