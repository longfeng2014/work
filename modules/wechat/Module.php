<?php
namespace modules\wechat;

/**
 * 微信端前台入口
 */
class module extends \common\wechat\Module
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'modules\wechat\controllers';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        
        # 该成员变量未设置值时，将调用父模块录的布局目
        # 该成员变量被设置值后，将调用当前模块的布局目录
        $this->layout = 'main';
        // custom initialization code goes here
    }
}
