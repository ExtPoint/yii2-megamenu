<?php

if (strpos($name, 'habr') === 0) {
    echo \yii\bootstrap\Nav::widget([
        'options' => ['class' => 'nav-tabs'],
        'items' => \Yii::$app->megaMenu->getMenu(['/site/page', 'name' => 'habrahabr']),
    ]);
}

echo \yii\helpers\Html::tag('h3', $name);