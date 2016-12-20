# yii2-megamenu

Configurable site map with auto generate page title, breadcrumbs and navigation.


## Install

Install by composer:

```sh
$ composer require ExtPoint/yii2-megamenu
```

After install, add MegaMenu to configuration:

```php
...
'bootstrap' => ['log', 'megamenu'],
...
```

```php
'components' => [
    'megaMenu'=> [
        'class' => '\extpoint\megamenu\MegaMenu',
        'items' => [
            // sitemap
        ],
    ],
    ...
],
```

## Features

- Auto generate page title, breadcrumbs, menus and navigation from site map configuration;
- Auto check access for actions (not implemented now) and menu item visible; 
- Separate rules from UrlManager to modules.


## API of component `\extpoint\megamenu\MegaMenu`

- `setItems(array $items)` Add menu items to end of list;
- `addItems()` Add tree menu items;
- `getItems()` Get all tree menu items;
- `getActiveItem()` Returned item with current route and parsed params. Alias `\Yii::$app->requestedRoute`, but also have params;
- `getMenu($fromItem = null, $custom = null)` Recursive find menu item by param $item (set null for return root) and return tree menu items (in format for `\yii\bootstrap\Nav::items`). Set param $custom as integer for limit tree levels. For example, `getMenu(null, 2)` return two-level menu;
- `getTitle($url = null)` Find item by url (ot current page) label and return it;
- `getFullTitle($url = null, $separator = ' — ')` Find item by url (or current page) and return item label with all parent labels;
- `getBreadcrumbs($url = null)` Return breadcrumbs links for widget `\yii\widgets\Breadcrumbs`;
- `getItem($item, &$parents = [])` Find menu item by item url or route. In param $parents will be added all parent items;
- `getItemUrl($item)` Find item by url or route and return it url.


## API of Menu Item (class `\extpoint\megamenu\MegaMenuItem`)

Menu item looks like to item from `\yii\bootstrap\Nav::items`. Next properties is identical:

- label
- url
- visible
- encode
- items
- linkOptions
- active

Additional properties:

- urlRule (string, array or `\yii\rest\UrlRule` instance) Value format is identical to item from `\yii\web\UrlManager::rules`;
- roles (string or array of strings) Value format is identical to `\yii\filters\AccessRule::roles`. `"?"`, `"@"` and string role are supported.
- order (integer or float) Each menu items sorted by `order` param. Default is zero.
- redirectToChild (boolean or string) Set true or child item id for use link from this item.


## How does item search

Each items will be equals with search item (or url/route) by method `\extpoint\megamenu\MegaMenu::isUrlEquals`.
Links are equals as two strings.
Routes are equals will be normalized to full route (with controller and module), then routes equals as strings. If routes is equals, then checked url params (if it exists).
If param value is null, then compared only it keys. If param value is not null, then compared keys and params values.

Examples:

```php
isUrlEquals('http://google.com', 'http://google.com') // true
isUrlEquals(['/qq/ww/ee'], ['/aa/bb/cc']) // false
isUrlEquals(['/aa/bb/cc', 'foo' => null], ['/aa/bb/cc']) // false
isUrlEquals(['/aa/bb/cc', 'foo' => null], ['/aa/bb/cc', 'foo' => null]) // true
isUrlEquals(['/aa/bb/cc', 'foo' => 'qwe'], ['/aa/bb/cc', 'foo' => null]) // true
isUrlEquals(['/aa/bb/cc', 'foo' => 'qwe'], ['/aa/bb/cc', 'foo' => '555']) // false
```