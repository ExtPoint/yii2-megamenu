<?php

namespace extpoint\megamenu;

use yii\base\InvalidParamException;
use yii\web\Request;

class MenuHelper {

    public static function menuToRules($items) {
        $rules = [];
        foreach ($items as $item) {
            if (isset($item['url']) && is_array($item['url']) && isset($item['urlRule'])) {
                $defaults = $item['url'];
                $route = array_shift($defaults);

                if (is_string($item['urlRule'])) {
                    $rules[] = [
                        'pattern' => $item['urlRule'],
                        'route' => $route,
                        'defaults' => $defaults,
                    ];
                } elseif (is_array($item['urlRule'])) {
                    if (!isset($item['urlRule']['route'])) {
                        $item['urlRule']['route'] = $route;
                    }
                    if (!isset($item['urlRule']['defaults'])) {
                        $item['urlRule']['defaults'] = $defaults;
                    }
                    $rules[] = $item['urlRule'];
                }
            }

            if (!empty($item['items'])) {
                $rules = array_merge($rules, static::menuToRules($item['items']));
            }
        }
        return $rules;
    }


    /**
     * @param string $route
     * @return string
     * @throws InvalidParamException
     */
    public static function normalizeRoute($route) {
        $route = \Yii::getAlias((string) $route);
        if (strncmp($route, '/', 1) === 0) {
            // absolute route
            return ltrim($route, '/');
        }

        // relative route
        if (\Yii::$app->controller === null) {
            throw new InvalidParamException("Unable to resolve the relative route: $route. No active controller is available.");
        }

        if (strpos($route, '/') === false) {
            // empty or an action ID
            return $route === '' ? \Yii::$app->controller->getRoute() : \Yii::$app->controller->getUniqueId() . '/' . $route;
        } else {
            // relative to module
            return ltrim(\Yii::$app->controller->module->getUniqueId() . '/' . $route, '/');
        }
    }

    /**
     * @param string $name
     * @return mixed|null
     */
    public static function paramGet($name) {
        return \Yii::$app->request instanceof Request ? \Yii::$app->request->get($name) : null;
    }

    /**
     * @param string $name
     * @return mixed|null
     */
    public static function paramUser($name) {
        if (!\Yii::$app->has('user')) {
            return null;
        }
        if (\Yii::$app->user->hasProperty($name)) {
            return \Yii::$app->user->$name;
        }
        return \Yii::$app->user->identity->$name;
    }

}