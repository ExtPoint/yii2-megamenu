<?php

namespace extpoint\megamenu\middleware;

use extpoint\yii2\base\Controller;
use yii\base\ActionEvent;
use yii\base\Object;
use yii\web\Application;
use yii\web\NotFoundHttpException;

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
     * @throws NotFoundHttpException
     */
    public static function checkAccess($event)
    {
        // Skip debug module
        if ($event->action->controller->module->id === 'debug') {
            return;
        }

        // Skip error action
        if ($event->action->uniqueId === \Yii::$app->errorHandler->errorAction) {
            return;
        }

        $item = \Yii::$app->megaMenu->getActiveItem();
        if (!$item) {
            throw new NotFoundHttpException();
        }
        if (!$item->checkVisible($item->normalizedUrl)) {
            if (\Yii::$app->user->isGuest) {
                \Yii::$app->user->loginRequired();
            }
            // TODO Show 403?
            //\Yii::$app->response->redirect(\Yii::$app->homeUrl);
            $event->isValid = false;
        }
    }
}
