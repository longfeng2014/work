<?php

namespace modules\admin\models;

use Yii;

/**
 * This is the model class for table "yii2_config".
 *
 * @property integer $id
 * @property string $name
 * @property string $info
 * @property integer $groupid
 * @property string $value
 */
class AuthRuless extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%auth_rule}}';
    }

}
