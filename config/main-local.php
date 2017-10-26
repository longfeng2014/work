<?php

//系统常量
// define('SITE_URL', 'http://mmkj.com/');
// $params = require(__DIR__ . '/params.php');
$config = [
	'id' => 'common',
	'basePath' => dirname(__DIR__),
	'bootstrap' => ['log'],
	// 配置语言
	'language' => 'zh-CN',
	// 配置时区
	'timeZone' => 'Asia/Chongqing',
	// 'defaultRoute' => 'index/index', //默认控制器
	// 'defaultAction' => 'list',   //默认方法
	'modules' => [
		'wechat' => 'modules\wechat\Module',
		'admin' => 'modules\admin\Module',
	],
	'aliases' => [
		'siteroot' => dirname(__DIR__),
		'statics' => dirname(__DIR__) . DIRECTORY_SEPARATOR . 'statics',
		'common' => dirname(__DIR__) . DIRECTORY_SEPARATOR . 'common',
		'console' => dirname(__DIR__) . DIRECTORY_SEPARATOR . 'console',
		'modules' => dirname(__DIR__) . DIRECTORY_SEPARATOR . 'modules',
	],
	'components' => [
		'db' => [
			'class' => 'yii\db\Connection',
			'dsn' => 'mysql:host=182.254.153.39;dbname=mmkj',
			'username' => 'root',
			'password' => '2014',
			'charset' => 'utf8',
			'tablePrefix' => 'mm_',
		],
		"authManager" => [
			"class" => 'yii\rbac\DbManager',
			// 'defaultRoles' => ['guest'],
		],
	    /**
         * 语言包配置
         * 将"源语言"翻译成"目标语言". 注意"源语言"默认配置为 'sourceLanguage' => 'en-US'
         * 使用: \Yii::t('common', 'title'); 将common/messages下的common.php中的title转为对应的中文
         */
        'i18n' => [
            'translations' => [
                '*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    // 'basePath' => '@common/messages',
                    'sourceLanguage' => 'en',
                    'fileMap' => [
                        // 'common' => 'common.php',
                        // 'frontend' => 'frontend.php',
                        // 'backend' => 'backend.php',
                    ],
                ],
            ],
        ],  
		'request' => [
			'cookieValidationKey' => 'JFw34j32io()*)*%3',
			// 'enableCsrfValidation' => false,
		],
		'cache' => [
			'class' => 'yii\caching\FileCache',
		],
		'session' => [
			// this is the name of the session cookie used for login on the app
			// 'name' => 'apps',
            // 'class' => 'yii\web\DbSession',
			'class' => 'yii\web\CacheSession',
			// 'cache' => 'sessioncache',
		],
		//身份认证类
        'user' => [
            'class' => 'yii\web\User',
            'identityClass' => 'modules\admin\models\Admin',
            'enableAutoLogin' => true,
            // 'identityCookie' => ['name' => '_identity-backend', 'httpOnly' => true],
            'loginUrl' => ['admin/index/login'], //配置登录url
        ],
		'errorHandler' => [
			'errorAction' => 'admin/index/error',
		],
		'mailer' => [
			'class' => 'yii\swiftmailer\Mailer',
			// send all mails to a file by default. You have to set
			// 'useFileTransport' to false and configure a transport
			// for the mailer to send real emails.
			'useFileTransport' => true,
		],
		'log' => [
			'traceLevel' => YII_DEBUG ? 3 : 0,
			'targets' => [
				[
					'class' => 'yii\log\FileTarget',
					'levels' => ['error', 'warning'],
				],
			],
		],
		'view' => [
			'theme' => [
		        'basePath' => '@statics/themes/admin',
		        'baseUrl' => '@statics/themes/admin',
		        /*'pathMap' => [
		            '@backend/views' => [
		                '@statics/themes/' . 'admin' . '/views',
		            ],
		        ],*/
		    ],
		],
		'assetManager' => [
			'basePath' => 'runtime/assets',
			'baseUrl' => '/../runtime/assets',
			'appendTimestamp' => true,			//是否添加版本号
			/*'bundles' => [
                'yii\web\YiiAsset',
                'yii\web\JqueryAsset',
                'yii\bootstrap\BootstrapAsset',
                // you can override AssetBundle configs here
            ],*/
		],

		'urlManager' => [
			'enablePrettyUrl' => true,
			'showScriptName' => false,
			'enableStrictParsing' => true,
			'suffix' => '',
			'rules' => [
				'' => '/wechat/index',
				'<module:\w+>' => '<module>/index', //兼容不带action的情况,如help?param=value时取不到值.
				'<module:\w+>/?' => '<module>/index',
				'<module:\w+>/<action:\w+>' => '<module>/<action>',
				'<module:\w+>/<action:\w+>/?' => '<module>/<action>',
				'<module>/<controller>/' => '<module>/<controller>/index',
				'<module>/<controller>/<action>/' => '<module>/<controller>/<action>',
			],
		],
	],
	// 'params' => $params,
	'params' => [
		'adminEmail' => 'admin@example.com',
		'configGroup' => [
					        1 => '基本配置',
					        2 => '邮箱配置',
					        3 => '附件配置',
					    ],
		'user.passwordResetTokenExpire' => 3600,
	    'availableLocales' => [
	        'en-US'=>'English (US)',
	        'zh-CN' => '简体中文',
	    ],
	],
];

if (YII_ENV_DEV) {
	// configuration adjustments for 'dev' environment
	$config['bootstrap'][] = 'debug';
	$config['modules']['debug'] = [
		'class' => 'yii\debug\Module',
	];

	$config['bootstrap'][] = 'gii';
	$config['modules']['gii'] = [
		'class' => 'yii\gii\Module',
	];
}

return $config;
