<?php

namespace extpoint\megamenu;

use Yii;
use yii\base\InvalidParamException;
use yii\helpers\ArrayHelper;
use yii\web\Request;

class MenuHelper
{
    /**
     * @TODO describe method
     *
     * @param  array $items
     * @return array
     */
    public static function menuToRules($items)
    {
        $rules = [];
        foreach ($items as $item) {
            $url = ArrayHelper::getValue($item, 'url');
            $urlRule = ArrayHelper::getValue($item, 'urlRule');

            if ($url && $urlRule && is_array($url)) {
                $defaults = $url;
                $route = array_shift($defaults);

                if (is_string($urlRule)) {
                    $rules[] = [
                        'pattern' => $urlRule,
                        'route' => $route,
                    ];
                } elseif (is_array($urlRule)) {
                    if (!isset($urlRule['route'])) {
                        $urlRule['route'] = $route;
                    }
                    $rules[] = $urlRule;
                }
            }

            $subItems = ArrayHelper::getValue($item, 'items');
            if (is_array($subItems)) {
                $rules = array_merge(static::menuToRules($subItems), $rules);
            }
        }
        return $rules;
    }


    /**
     * @param string $route
     * @return string
     * @throws InvalidParamException
     */
    public static function normalizeRoute($route)
    {
        $route = Yii::getAlias((string) $route);
        if (strncmp($route, '/', 1) === 0) {
            // absolute route
            return trim($route, '/');
        }

        // relative route
        if (Yii::$app->controller === null) {
            throw new InvalidParamException("Unable to resolve the relative route: $route. No active controller is available.");
        }

        if (strpos($route, '/') === false) {
            // empty or an action ID
            return $route === ''
                ? Yii::$app->controller->getRoute()
                : Yii::$app->controller->getUniqueId() . '/' . $route;
        } else {
            // relative to module
            return ltrim(Yii::$app->controller->module->getUniqueId() . '/' . $route, '/');
        }
    }

    /**
     * @param string $name
     * @return mixed|null
     */
    public static function paramGet($name)
    {
        return Yii::$app->request instanceof Request ? Yii::$app->request->get($name) : null;
    }

    /**
     * @param string $name
     * @return mixed|null
     */
    public static function paramUser($name)
    {
        if (!Yii::$app->has('user')) {
            return null;
        }
        if (Yii::$app->user->hasProperty($name)) {
            return Yii::$app->user->$name;
        }
        return Yii::$app->user->identity->$name;
    }
}
