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
     * @return bool
     */
    public function getActive()
    {
        if ($this->_active === null) {
            $this->_active = false;

            if ($this->url && $this->owner->isUrlEquals($this->url, $this->owner->getRequestedRoute())) {
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

        return $this->checkVisible($this->url);
    }

    /**
     * @param array $url
     * @return bool
     */
    public function checkVisible($url) {
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

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'label' => $this->label,
            'url' => $this->url,
            'roles' => $this->roles,
            'visible' => $this->getVisible(),
            'encode' => $this->encode,
            'active' => $this->active,
            'items' => $this->items,
            'options' => $this->options,
            'linkOptions' => $this->linkOptions,
        ];
    }
}
