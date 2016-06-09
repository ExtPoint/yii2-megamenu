<?php

defined('YII_DEBUG') || define('YII_DEBUG', true);

require(__DIR__ . '/../../vendor/autoload.php');
require(__DIR__ . '/../../vendor/yiisoft/yii2/Yii.php');

(new \yii\web\Application(require __DIR__ . '/config/web.php'))->run();
