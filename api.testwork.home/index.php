<?php

// comment out the following two lines when deployed to production
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Max-Age: 86400');
header('Access-Control-Allow-Headers: Cache-Control, Origin, X-Requested-With, Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');

require __DIR__ . '/../basic/vendor/autoload.php';
require __DIR__ . '/../basic/vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__ . '/../basic/config/web.php';

(new yii\web\Application($config))->run();
