<?php

return [
    'id' => 'megamenu',
    'basePath' => dirname(__DIR__),
    'vendorPath' => __DIR__ . '/../../../vendor',
    'bootstrap' => ['log', 'megaMenu'],
    'components' => [
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false
        ],
        'request' => [
            'cookieValidationKey' => '1',
        ],
        'megaMenu'=> [
            'class' => '\extpoint\megamenu\MegaMenu',
            'items' => require __DIR__ . '/menu.php',
        ],
    ],
];