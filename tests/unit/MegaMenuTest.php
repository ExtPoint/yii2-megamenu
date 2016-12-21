<?php

namespace tests\unit;

use extpoint\megamenu\MegaMenu;

class MegaMenuTest extends \PHPUnit_Framework_TestCase
{

    public function testEquals()
    {
        $this->assertEquals(true, \Yii::$app->megaMenu->isUrlEquals('http://google.com', 'http://google.com'));
        $this->assertEquals(false, \Yii::$app->megaMenu->isUrlEquals(['/qq/ww/ee'], ['/aa/bb/cc']));
        $this->assertEquals(false, \Yii::$app->megaMenu->isUrlEquals(['/aa/bb/cc', 'foo' => null], ['/aa/bb/cc']));
        $this->assertEquals(true, \Yii::$app->megaMenu->isUrlEquals(['/aa/bb/cc', 'foo' => null], ['/aa/bb/cc', 'foo' => null]));
        $this->assertEquals(true, \Yii::$app->megaMenu->isUrlEquals(['/aa/bb/cc', 'foo' => 'qwe'], ['/aa/bb/cc', 'foo' => null]));
        $this->assertEquals(false, \Yii::$app->megaMenu->isUrlEquals(['/aa/bb/cc', 'foo' => 'qwe'], ['/aa/bb/cc', 'foo' => '555']));
    }

    public function testFind()
    {
        $menu = new MegaMenu([
            'requestedRoute' => ['/site/index'],
            'items' => $this->getMenu(),
        ]);

        $this->assertEquals(['/site/page', 'name' => 'habrahabr'], $menu->getItemUrl(['/site/page', 'name' => 'habrahabr']));
        $this->assertEquals(['/site/page', 'name' => 'habrahabr'], $menu->getItemUrl('habrahabr.publications'));
        $this->assertEquals([
            ['url' => ['/site/index'], 'items' => null],
            'habrahabr' => ['url' => ['/site/page', 'name' => 'habrahabr'], 'items' => null],
            ['url' => ['/site/page', 'name' => 'geektimes'], 'items' => null],
            ['url' => ['/site/page', 'name' => 'toster'], 'items' => null],
        ], array_map(function($item) {
            return [
                'url' => $item['url'],
                'items' => $item['items']
            ];
        }, $menu->getMenu(null, 1)));
        $this->assertEquals([
            'publications' => ['url' => ['/site/page', 'name' => 'habrahabr', 'category' => 'top'], 'items' => null],
            'hubs' => ['url' => ['/site/hubs'], 'items' => null],
        ], array_map(function($item) {
            return [
                'url' => $item['url'],
                'items' => $item['items']
            ];
        }, $menu->getMenu('habrahabr', 1)));
    }

    protected function getMenu() {
        return [
            [
                'label' => 'Главная',
                'url' => ['/site/index'],
                'urlRule' => '/',
            ],
            'habrahabr' => [
                'label' => 'Хабрахабр',
                'redirectToChild' => true,
                'items' => [
                    'publications' => [
                        'label' => 'Публикации',
                        'url' => ['/site/page', 'name' => 'habrahabr'],
                        'urlRule' => '/habrahabr',
                        'redirectToChild' => 'top',
                        'items' => [
                            'all' => [
                                'label' => 'Все подряд',
                                'url' => ['/site/page', 'name' => 'habrahabr', 'category' => 'all'],
                                'urlRule' => '/habrahabr/all',
                            ],
                            'feed' => [
                                'label' => 'По подписке',
                                'url' => ['/site/page', 'name' => 'habrahabr', 'category' => 'feed'],
                                'urlRule' => '/habrahabr/feed',
                            ],
                            'top' => [
                                'label' => 'Лучшие',
                                'url' => ['/site/page', 'name' => 'habrahabr', 'category' => 'top'],
                                'urlRule' => '/habrahabr/top',
                            ],
                            'interesting' => [
                                'label' => 'Интересные',
                                'url' => ['/site/page', 'name' => 'habrahabr', 'category' => 'interesting'],
                                'urlRule' => '/habrahabr/interesting',
                            ],
                        ],
                    ],
                    'hubs' => [
                        'label' => 'Хабы',
                        'url' => ['/site/hubs'],
                        'urlRule' => '/habrahabr/hubs',
                    ],
                ],
            ],
            [
                'label' => 'Geektimes',
                'url' => ['/site/page', 'name' => 'geektimes'],
                'urlRule' => '/geektimes',
            ],
            [
                'label' => 'Тостер',
                'url' => ['/site/page', 'name' => 'toster'],
                'urlRule' => '/toster',
            ],
        ];
    }

}