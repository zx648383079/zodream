<?php
use Zodream\Infrastructure\Support\Html;
class HtmlTest extends PHPUnit_Framework_TestCase {
    public function testTag() {
        echo Html::tag('div', '', [
            'class' => 'a'
        ]);
    }
}
