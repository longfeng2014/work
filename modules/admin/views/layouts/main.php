<?php
/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
// use yii\web\AssetBundle as AppAsset;
use common\admin\AppAsset;
use common\widgets\Alert;
use modules\admin\models\Menu;

AppAsset::register($this);
// AppAsset::addJs($this,Yii::$app->request->baseUrl."/statics/themes/admin/js/jquery.js");        //页面底部进入js
// AppAsset::addCss($this,Yii::$app->request->baseUrl."/statics/themes/admin/css/**.css");        //页面底部进入css
$allMenus = Menu::getMenu();
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Yii::$app->params['basic']['sitename'].' - '.Html::encode($this->title) ?></title>
    <?php $this->head() ?>
    <link href="/statics/themes/admin/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="/statics/themes/admin/layui/css/layui.css"  media="all">
    <script src="/statics/themes/admin/layui/layui.js" charset="utf-8"></script> 
<body>
<?php $this->beginBody() ?>
<div class="left-side sticky-left-side">
    <div class="logo">
        <a href="<?php echo Yii::$app->request->hostInfo.'/admin';?>"><img style="width: 80%;" src="/statics/themes/admin/images/logo.png" alt=""></a>
    </div>
    <!-- <div class="logo-icon text-center">
        <a href="index.html"><img src="/statics/themes/admin/images/logo_icon.png" alt=""></a>
    </div> -->

    <div class="left-side-inner">
        <ul class="nav nav-pills nav-stacked custom-nav">
            <?php
            foreach ($allMenus as $menus) {
            ?>
            <li class="menu-list">
                <a href="<?=$menus['url'];?>">
                    <i class="fa <?=$menus['icon_style'];?>"></i>
                    <span><?=$menus['name'];?></span>
                </a>
                <ul class="sub-menu-list">
                    <?php
                    if(!isset($menus['_child'])) break;
                    $menuArr = [];
                    foreach ($menus['_child'] as $menu) {
                        $menuArr = explode('/', $menu['url']);
                        $controller = count($menuArr) > 3 ? $menuArr[2] : '';
                    ?>
                    <li class="<?=Yii::$app->controller->id == $controller ? 'active' : '';?>"><a href="<?=$menu['url']?>"> <?=$menu['name'];?></a></li>
                    <?php }?>
                </ul>
            </li>
            <?php }?>
        </ul>
    </div>
</div>

<div class="main-content" >
    <div class="header-section">
    <?php
        NavBar::begin([
            'brandLabel' => '<i class="fa fa-dedent"></i>',
            'brandOptions' => ['class' => 'toggle-btn'],
            'brandUrl' => '#',
            'renderInnerContainer' => false,
            'options' => [
                'class' => 'navbar navbar-default',
            ],
        ]);
    //搜索
    /*$leftMenuItems = ['<li>'
            . Html::beginForm('/index/search', 'post', ['class' => 'searchform'])
            . Html::textInput('keyword', '', ['class' => 'form-control', 'placeholder' => 'Search here...'])
            . Html::endForm()
            . '</li>'
    ];
    echo Nav::widget([
        'options' => ['class' => 'navbar-nav navbar-left'],
        'items' => $leftMenuItems,
    ]);*/

    $menuItems = [];
    if (!\Yii::$app->user->isGuest) {
        $menuItems[] = '<li class="dropdown notification-menu">'
            . '<a href="#" class="dropdown-toggle" data-toggle="dropdown"><img src="/statics/themes/admin/images/user-avatar.png" alt="" />'
            . Yii::$app->user->identity->username
            . '<span class="caret"></span></a>'
            . '<ul class="dropdown-menu dropdown-menu-usermenu pull-right" role="menu">'
            // . '<li><a href="#"><i class="fa fa-user"></i>  Profile</a></li>'
            . '<li><a href="/admin/admin/update?id='.Yii::$app->user->identity->id.'"><i class="fa fa-cog"></i>  个人中心</a></li>'
            . '<li>'
            . Html::beginForm(['/admin/index/logout'], 'post')
            . Html::submitButton(
                '<i class="fa fa-sign-out"></i> '.Yii::t('backend', '退出'),
                ['class' => 'btn btn-link logout']
            )
            . Html::endForm()
            . '</ul>'
            . '</li>';
    }
    echo Nav::widget([
        'options' => ['class' => 'navbar-nav navbar-right'],
        'items' => $menuItems,
    ]);
    NavBar::end();
    ?>
    </div>

    <div class="page-heading">
        <?php echo Breadcrumbs::widget([
                'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
             ]) 
        ?>
    </div>
    <?= Alert::widget() ?>
    <div class="wrapper">
        <div class="panel">
            <div class="panel-body">
            <?= $content ?>
            </div>
        </div>
    </div>

</div>

<?php $this->endBody() ?>
<script src="/statics/themes/admin/js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="/statics/themes/admin/js/jquery.nicescroll.js"></script>
<script src="/statics/themes/admin/js/scripts.js"></script>
</body>
</html>
<?php $this->endPage() ?>
