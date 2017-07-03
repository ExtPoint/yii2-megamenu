<?php

namespace extpoint\megamenu\middleware;

use extpoint\yii2\base\Controller;
use yii\base\ActionEvent;
use yii\base\Object;
use yii\web\Application;
use yii\web\ForbiddenHttpException;

class AccessMiddleware extends Object
{
    /**
     * @param Application $app
     */
    public static function register($app)
    {
        if ($app instanceof Application) {
            $app->on(Controller::EVENT_BEFORE_ACTION, [static::className(), 'checkAccess']);
        }
    }

    /**
     * @param ActionEvent $event
     * @throws ForbiddenHttpException
     */
    public static function checkAccess($event)
    {
        // Skip debug module
        if ($event->action->controller->module->id === 'debug') {
            return;
        }

        $item = \Yii::$app->megaMenu->getActiveItem();
        if (!$item || !$item->checkVisible($item->normalizedUrl)) {
            if (\Yii::$app->user->isGuest) {
                \Yii::$app->user->loginRequired();
            }
            // TODO Show 403?
            //\Yii::$app->response->redirect(\Yii::$app->homeUrl);
            $event->isValid = false;
        }
    }
}
