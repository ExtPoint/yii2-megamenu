<?php

return [
    [
        'label' => 'Главная',
        'url' => ['/site/index'],
        'urlRule' => '/',
    ],
    [
        'label' => 'Хабрахабр',
        'items' => [
            [
                'label' => 'Публикации',
                'url' => ['/site/page', 'name' => 'habrahabr'],
                'urlRule' => '/habrahabr',
                'items' => [
                    [
                        'label' => 'Все подряд',
                        'url' => ['/site/page', 'name' => 'habrahabr', 'category' => 'all'],
                        'urlRule' => '/habrahabr/all',
                    ],
                    [
                        'label' => 'По подписке',
                        'url' => ['/site/page', 'name' => 'habrahabr', 'category' => 'feed'],
                        'urlRule' => '/habrahabr/feed',
                    ],
                    [
                        'label' => 'Лучшие',
                        'url' => ['/site/page', 'name' => 'habrahabr', 'category' => 'top'],
                        'urlRule' => '/habrahabr/top',
                    ],
                    [
                        'label' => 'Интересные',
                        'url' => ['/site/page', 'name' => 'habrahabr', 'category' => 'interesting'],
                        'urlRule' => '/habrahabr/interesting',
                    ],
                ],
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