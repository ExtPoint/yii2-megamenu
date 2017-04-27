<?php

namespace extpoint\megamenu;

use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\web\Application;
use yii\web\ForbiddenHttpException;

/**
 * Class MegaMenu
 * @package extpoint\yii2\components
 * @property array $items
 * @property array $requestedRoute
 * @property-read array $activeItem
 */
class MegaMenu extends Component
{
    /**
     * @var MegaMenuItem[]
     */
    private $_items = [];
    private $_requestedRoute;
    private $isModulesFetched = false;

    public function init()
    {
        parent::init();
        Yii::$app->urlManager->addRules(MenuHelper::menuToRules($this->_items), false);
    }

    /**
     * Add menu items to end of list
     * @param array $items
     */
    public function setItems(array $items)
    {
        $this->addItems($items);
    }

    /**
     * Get all tree menu items
     * @return array
     */
    public function getItems()
    {
        if ($this->isModulesFetched === false) {
            $this->isModulesFetched = true;

            // Fetch items from modules
            foreach (Yii::$app->getModules() as $id => $module) {
                /** @var \yii\base\Module $module */
                $module = Yii::$app->getModule($id);
                if (method_exists($module, 'coreMenu')) {
                    $this->addItems($module->coreMenu(), true);
                }

                // Submodules support
                foreach ($module->getModules() as $subId => $subModule) {
                    $subModule = $module->getModule($subId);
                    if (method_exists($subModule, 'coreMenu')) {
                        $this->addItems($subModule->coreMenu(), true);
                    }
                }
            }
        }

        return $this->_items;
    }

    /**
     * Add tree menu items
     * @param array $items
     * @param bool|true $append
     */
    public function addItems(array $items, $append = true)
    {
        $this->_items = $this->mergeItems($this->_items, $items, $append);
    }

    /**
     * Returned item with current route and parsed params. Alias Yii::$app->requestedRoute, but also have params
     * @return MegaMenuItem|null
     * @throws InvalidConfigException
     */
    public function getActiveItem()
    {
        return $this->getItem($this->getRequestedRoute());
    }

    public function getRequestedRoute()
    {
        if ($this->_requestedRoute === null) {
            // Set active item
            $parseInfo = Yii::$app->urlManager->parseRequest(Yii::$app->request);
            if ($parseInfo) {
                $this->_requestedRoute = [$parseInfo[0] ? '/' . $parseInfo[0] : ''] + $parseInfo[1];
            } else {
                $this->_requestedRoute = ['/' . Yii::$app->errorHandler->errorAction];
            }
        }
        return $this->_requestedRoute;
    }

    public function setRequestedRoute($value)
    {
        $this->_requestedRoute = $value;
    }

    /**
     * Recursive find menu item by param $item (set null for return root) and return tree menu
     * items (in format for yii\bootstrap\Nav::items). In param $custom you can overwrite items
     * configuration, if set it as array. Set param $custom as integer for limit tree levels.
     * For example, getMenu(null, 2) return two-level menu
     * @param array $fromItem
     * @param int $level Level limit
     * @return array
     * @throws InvalidConfigException
     */
    public function getMenu($fromItem = null, $level = null)
    {
        $itemModels = [];
        if ($fromItem) {
            $item = $this->getItem($fromItem);
            if ($item !== null) {
                $itemModels = $item->items;
            }
        } else {
            $itemModels = $this->getItems();
        }

        if (is_int($level)) {
            // Level limit
            return $this->sliceTreeItems($itemModels, $level);
        }

        return array_map(function ($itemModel) {
            /** @type MegaMenuItem $itemModel */
            return $itemModel->toArray();
        }, $itemModels);
    }

    /**
     * Find item by url (ot current page) label and return it
     * @param array|null $url Child url or route, default - current route
     * @return string
     */
    public function getTitle($url = null)
    {
        $titles = array_reverse($this->getBreadcrumbs($url));
        return !empty($titles) ? reset($titles)['label'] : '';
    }

    /**
     * Find item by url (or current page) and return item label with all parent labels
     * @param array|null $url Child url or route, default - current route
     * @param string $separator Separator, default is " - "
     * @return string
     */
    public function getFullTitle($url = null, $separator = ' — ')
    {
        $title = [];
        foreach (array_reverse($this->getBreadcrumbs($url)) as $item) {
            $title[] = $item['label'];
        }
        $title[] = Yii::$app->name;
        return implode($separator, $title);
    }

    /**
     * Return breadcrumbs links for widget \yii\widgets\Breadcrumbs
     * @param array|null $url Child url or route, default - current route
     * @return array
     */
    public function getBreadcrumbs($url = null)
    {
        $url = $url ?: $this->getRequestedRoute();

        // Find child and it parents by url
        $itemModel = $this->getItem($url, $parents);

        if (!$itemModel || (empty($parents) && $this->isHomeUrl($itemModel->normalizedUrl))) {
            return [];
        }

        $parents = array_reverse((array)$parents);
        $parents[] = [
            'label' => $itemModel->modelLabel,
            'url' => $itemModel->normalizedUrl,
            'linkOptions' => is_array($itemModel->linkOptions) ? $itemModel->linkOptions : [],
        ];

        foreach ($parents as &$parent) {
            if (isset($parent['linkOptions'])) {
                $parent = array_merge($parent, $parent['linkOptions']);
                unset($parent['linkOptions']);
            }
        }

        return $parents;
    }

    /**
     * Find menu item by item url or route. In param $parents will be added all parent items
     * @param string|array $item
     * @param array $parents
     * @return MegaMenuItem|null
     * @throws InvalidConfigException
     */
    public function getItem($item, &$parents = [])
    {
        if (is_array($item) && !$this->isRoute($item)) {
            $item = $item['url'];
        }
        if (is_string($item) && strpos($item, '/') === false) {
            $item = implode('.items.', explode('.', $item));
            return ArrayHelper::getValue($this->getItems(), $item);
        }
        return $this->findItemRecursive($item, $this->getItems(), $parents);
    }

    /**
     * Find item by url or route and return it url
     * @param $item
     * @return array|null|string
     */
    public function getItemUrl($item)
    {
        $item = $this->getItem($item);
        return $item ? $item->normalizedUrl : null;
    }

    /**
     * @param string|array|MegaMenuItem $url1
     * @param string|array $url2
     * @return bool
     */
    public function isUrlEquals($url1, $url2)
    {
        if ($url1 instanceof MegaMenuItem) {
            $url1 = $url1->normalizedUrl;
        }
        if ($url2 instanceof MegaMenuItem) {
            $url2 = $url2->normalizedUrl;
        }

        // Is routes
        if ($this->isRoute($url1) && $this->isRoute($url2)) {
            if (MenuHelper::normalizeRoute($url1[0]) !== MenuHelper::normalizeRoute($url2[0])) {
                return false;
            }

            $params1 = array_slice($url1, 1);
            $params2 = array_slice($url2, 1);

            // Compare routes' parameters by checking if keys are identical
            if (count(array_diff_key($params1, $params2)) || count(array_diff_key($params2, $params1))) {
                return false;
            }

            foreach ($params1 as $key => $value) {
                if (is_string($key) && $key !== '#') {
                    if (!array_key_exists($key, $params2)) {
                        return false;
                    }

                    if ($value !== null && $params2[$key] !== null && $params2[$key] != $value) {
                        return false;
                    }
                }
            }

            return true;
        }

        // Is urls
        if (is_string($url1) && is_string($url2)) {
            return $url1 === $url2;
        }

        return false;
    }

    /**
     * @param string|array $url
     * @return bool
     */
    protected function isHomeUrl($url)
    {
        if ($this->isRoute($url)) {
            return $this->isUrlEquals(['/' . Yii::$app->defaultRoute], $url);
        }
        return $url === Yii::$app->homeUrl;
    }

    /**
     * @param mixed $value
     * @return bool
     */
    protected function isRoute($value)
    {
        return is_array($value) && isset($value[0]) && is_string($value[0]);
    }

    /**
     * @param MegaMenuItem[] $items
     * @param int $level
     * @return array
     */
    protected function sliceTreeItems(array $items, $level = 1)
    {
        if ($level <= 0) {
            return [];
        }

        $menu = [];
        foreach ($items as $key => $itemModel) {
            $item = $itemModel->toArray();
            $nextLevel = $level;

            if (!empty($itemModel->items)) {
                if ($itemModel->redirectToChild) {
                    $childModel = null;
                    if ($itemModel->redirectToChild === true) {
                        $childModel = reset($itemModel->items);
                    } elseif (is_string($itemModel->redirectToChild) || is_int($itemModel->redirectToChild)) {
                        $childModel = ArrayHelper::getValue($itemModel->items, $itemModel->redirectToChild);
                    }
                    if ($childModel) {
                        $item['url'] = $childModel->normalizedUrl;
                    }
                    $nextLevel--;
                } elseif ($itemModel->normalizedUrl !== null) {
                    $nextLevel--;
                }
            }

            $item['items'] = $this->sliceTreeItems($itemModel->items, $nextLevel);
            if (empty($item['items'])) {
                $item['items'] = null;
            }
            $menu[$key] = $item;
        }
        return $menu;
    }

    /**
     * @param string|array $url
     * @param MegaMenuItem[] $items
     * @param array $parents
     * @return MegaMenuItem
     */
    protected function findItemRecursive($url, array $items, &$parents)
    {
        foreach ($items as $itemModel) {
            if ($itemModel->normalizedUrl && $this->isUrlEquals($url, $itemModel->normalizedUrl)) {
                return $itemModel;
            }

            if (!empty($itemModel->items)) {
                $foundItem = $this->findItemRecursive($url, $itemModel->items, $parents);
                if ($foundItem) {
                    $parentItem = $itemModel->toArray();
                    unset($parentItem['items']);
                    $parents[] = $parentItem;

                    return $foundItem;
                }
            }
        }

        return null;
    }

    protected function mergeItems($baseItems, $items, $append)
    {
        foreach ($items as $id => $item) {
            // Merge item with group (as key)
            if (is_string($id) && isset($baseItems[$id])) {
                foreach ($item as $key => $value) {
                    if ($key === 'items') {
                        $baseItems[$id]->$key = $this->mergeItems($baseItems[$id]->$key, $value, $append);
                    } elseif (is_array($baseItems[$id]) && is_array($value)) {
                        $baseItems[$id]->$key = $append ?
                            ArrayHelper::merge($baseItems[$id]->$key, $value) :
                            ArrayHelper::merge($value, $baseItems[$id]->$key);
                    } elseif ($append || $baseItems[$id]->$key === null) {
                        $baseItems[$id]->$key = $value;
                    }
                }
            } else {
                // Create instance
                if (!($item instanceof MegaMenuItem)) {
                    $item = new MegaMenuItem($item + ['owner' => $this]);
                    $item->items = $this->mergeItems([], $item->items, true);
                }

                // Append or prepend item
                if (is_int($id)) {
                    if ($append) {
                        $baseItems[] = $item;
                    } else {
                        array_unshift($baseItems, $item);
                    }
                } else {
                    if ($append) {
                        $baseItems[$id] = $item;
                    } else {
                        $baseItems = [$id => $item] + $baseItems;
                    }
                }
            }
        }

        ArrayHelper::multisort($baseItems, 'order');

        return $baseItems;
    }

    public function isAllowAccess($url)
    {
        $menuItem = $this->getItem($url);
        if (!$menuItem) {
            return true;
        }

        return $menuItem->checkVisible($url);
    }
}
