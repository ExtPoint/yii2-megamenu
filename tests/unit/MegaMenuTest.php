<?php

namespace tests\unit;

class MegaMenuTest extends \PHPUnit_Framework_TestCase {

    public function testEquals() {
        $this->assertEquals(true, \Yii::$app->megaMenu->isUrlEquals('http://google.com', 'http://google.com'));
        $this->assertEquals(false, \Yii::$app->megaMenu->isUrlEquals(['/qq/ww/ee'], ['/aa/bb/cc']));
        $this->assertEquals(false, \Yii::$app->megaMenu->isUrlEquals(['/aa/bb/cc', 'foo' => null], ['/aa/bb/cc']));
        $this->assertEquals(true, \Yii::$app->megaMenu->isUrlEquals(['/aa/bb/cc', 'foo' => null], ['/aa/bb/cc', 'foo' => null]));
        $this->assertEquals(true, \Yii::$app->megaMenu->isUrlEquals(['/aa/bb/cc', 'foo' => 'qwe'], ['/aa/bb/cc', 'foo' => null]));
        $this->assertEquals(false, \Yii::$app->megaMenu->isUrlEquals(['/aa/bb/cc', 'foo' => 'qwe'], ['/aa/bb/cc', 'foo' => '555']));
    }

}