<?php

namespace extpoint\megamenu;

use Yii;
use yii\base\Object;
use yii\helpers\ArrayHelper;
use yii\web\UrlRule;

/**
 * Class MegaMenuItem
 * @package extpoint\yii2\components
 * @property bool $active
 * @property-read string $modelLabel
 * @property-read string $normalizedUrl
 */
class MegaMenuItem extends Object
{
    /**
     * @var string
     */
    public $label;

    /**
     * @var string|array
     */
    public $url;

    /**
     * Value format is identical to item from \yii\web\UrlManager::rules
     * @var string|array|UrlRule
     */
    public $urlRule;

    /**
     * Value format is identical to \yii\filters\AccessRule::roles. "?", "@" or string role are supported
     * @var string|string[]
     */
    public $roles;

    /**
     * @var bool
     */
    public $visible;

    /**
     * @var bool
     */
    public $encode;

    /**
     * @var float
     */
    public $order = 0;

    /**
     * @var MegaMenuItem[]
     */
    public $items = [];

    /**
     * @var array
     */
    public $options = [];

    /**
     * @var array
     */
    public $linkOptions = [];

    /**
     * @var MegaMenu
     */
    public $owner;

    /**
     * @var bool|string|int
     */
    public $redirectToChild = false;

    /**
     * @var bool
     */
    public $_active;

    /**
     * @var callable|callable[]
     */
    public $accessCheck;

    /**
     * @var string
     */
    public $icon;

    /**
     * @var int|string
     */
    public $badge;

    /**
     * @var string
     */
    public $modelClass;

    private $_modelLabel;

    /**
     * @return bool
     */
    public function getActive()
    {
        if ($this->_active === null) {
            $this->_active = false;

            if ($this->normalizedUrl && $this->owner->isUrlEquals($this->normalizedUrl, $this->owner->getRequestedRoute())) {
                $this->_active = true;
            } else {
                foreach ($this->items as $itemModel) {
                    if ($itemModel->active) {
                        $this->_active = true;
                        break;
                    }
                }
            }
        }
        return $this->_active;
    }

    /**
     * @param bool $value
     */
    public function setActive($value)
    {
        $this->_active = (bool)$value;
    }

    /**
     * @return bool
     */
    public function getVisible()
    {
        if ($this->visible !== null) {
            return $this->visible;
        }

        return $this->checkVisible($this->normalizedUrl);
    }

    /**
     * @param array $url
     * @return bool
     */
    public function checkVisible($url)
    {
        $rules = array_merge((array)$this->accessCheck, (array)$this->roles);
        if (!empty($rules)) {
            foreach ($rules as $rule) {
                if (is_callable($rule)) {
                    $params = call_user_func($rule, $url);
                    $permissionName = ArrayHelper::remove($params, '0');
                    if ($permissionName && Yii::$app->user->can($permissionName, $params)) {
                        return true;
                    }
                } elseif ($rule === '?') {
                    if (Yii::$app->user->isGuest) {
                        return true;
                    }
                } elseif ($rule === '@') {
                    if (!Yii::$app->user->isGuest) {
                        return true;
                    }
                } elseif (Yii::$app->user->can($rule)) {
                    return true;
                }
            }
            return false;
        }

        return true;
    }

    public function getNormalizedUrl()
    {
        if (is_array($this->url)) {
            $url = [$this->url[0]];

            foreach ($this->url as $key => $value) {
                if (strpos($value, ':') !== false) {
                    list($getter, $name) = explode(':', $value);

                    if (is_int($key) && $key > 0) {
                        $key = $name;
                    }

                    switch ($getter) {
                        case 'user':
                            $url[$key] = MenuHelper::paramUser($name);
                            break;

                        case '':
                        case 'get':
                            $url[$key] = MenuHelper::paramGet($name);
                            break;
                    }
                }
            }

            // Append keys from url rule
            if (is_string($this->urlRule)) {
                preg_match_all('/<([^:>]+)[:>]/', $this->urlRule, $matches);
                foreach ($matches[1] as $key) {
                    if (!isset($url[$key])) {
                        $url[$key] = MenuHelper::paramGet($key);
                    }
                }
            }

            return $url;
        }
        return $this->url;
    }

    public function getModelLabel() {
        if ($this->_modelLabel === null) {
            $this->_modelLabel = $this->label;

            /** @var \extpoint\yii2\base\Model $modelClass */
            $modelClass = $this->modelClass;
            $coreModelClassName = '\extpoint\yii2\base\Model';
            if ($modelClass && class_exists($coreModelClassName) && is_subclass_of($modelClass, $coreModelClassName)) {
                $pkParam = $modelClass::getRequestParamName();
                $primaryKey = MenuHelper::paramGet($pkParam);
                if ($primaryKey) {
                    $model = $modelClass::findOne($primaryKey);
                    if ($model) {
                        $this->_modelLabel = $model->getModelLabel();
                    }
                }
            }
        }
        return $this->_modelLabel;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'label' => $this->label,
            'url' => $this->getNormalizedUrl(),
            'roles' => $this->roles,
            'visible' => $this->getVisible(),
            'encode' => $this->encode,
            'active' => $this->active,
            'icon' => $this->icon,
            'badge' => $this->badge,
            'items' => $this->items,
            'options' => $this->options,
            'linkOptions' => $this->linkOptions,
        ];
    }
}
