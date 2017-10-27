<?php

/* @var $this yii\web\View */

$this->title = '秘密空间';
$this->params['breadcrumbs'][] = '秘密空间';
Yii::$app->db->open();
?>
<div class="index-index">

        <header class="panel-heading">秘密空间</header>
        <div class="panel-body">
        <table class="table table-bordered table-hover">
            <tbody>
            <tr>
                <td>QQ交流群</td>
                <td>608230907</td>
            </tr>
            <tr>
                <td>下载地址</td>
                <td><a href="https://github.com/qiaohongbo/yii2-admin">点击下载</a></td>
            </tr>
            <tr>
                <td>Yii版本</td>
                <td><?=Yii::getVersion();?></td>
            </tr>
            <tr>

            </tbody>
        </table>
        </div>
</div>
