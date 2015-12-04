<?php

use Halaei\Jalali\Laravel\JalaliValidator;

class JalaliValidatorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var JalaliValidator
     */
    private $validator;

    public function setUp()
    {
        $this->validator = new JalaliValidator();
    }
    public function test_without_parameter()
    {
        $this->assertTrue($this->validator->validateJalali('foo', '1394/9/12', []));
        $this->assertFalse($this->validator->validateJalali('foo', '1394/9/32', []));
        $this->assertFalse($this->validator->validateJalali('foo', ['1394/9/12'], []));
    }

    public function test_with_parameter()
    {
        $this->assertTrue($this->validator->validateJalali('foo', '1394-9-12', ['Y-m-d']));
        $this->assertTrue($this->validator->validateJalali('foo', '1394/9/12 ', ['Y/m/d ']));
        $this->assertTrue($this->validator->validateJalali('foo', '1394/9/12 12:55:59', ['Y/m/d *:*:*']));

        $this->assertFalse($this->validator->validateJalali('foo', '1394-9-32', ['Y-m-d']));
        $this->assertFalse($this->validator->validateJalali('foo', '1394/9/12', ['Y-m-d']));
        $this->assertFalse($this->validator->validateJalali('foo', '12:55:59', ['Y/m/d *:*:*']));
        $this->assertFalse($this->validator->validateJalali('foo', ['1394/9/12'], ['Y/m/d']));
    }
}