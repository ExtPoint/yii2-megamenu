<?php

defined('YII_ENV') || define('YII_ENV', 'test');

require(__DIR__ . '/../../vendor/autoload.php');
require(__DIR__ . '/../../vendor/yiisoft/yii2/Yii.php');

Yii::setAlias('@tests', __DIR__);

$config = require __DIR__ . '/../web/config/web.php';
(new yii\web\Application($config));
