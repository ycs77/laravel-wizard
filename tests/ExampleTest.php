<?php

namespace Ycs77\LaravelWizard\Test;

use Ycs77\LaravelWizard\Example;

class ExampleTest extends TestCase
{
    public function testExampleMethod()
    {
        $this->assertTrue((new Example)->method());
    }
}
