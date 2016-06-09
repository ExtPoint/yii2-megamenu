<?php

namespace app\controllers;

use yii\web\Controller;

class SiteController extends Controller {

    public $layout = '@app/views/layouts/main';

    public function actionIndex() {
        return $this->render('index');
    }

    public function actionPage($name, $category = null) {
        return $this->render('page', [
            'name' => $name
        ]);
    }

}