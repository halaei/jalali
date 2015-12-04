<?php

use Illuminate\Translation\Translator;
use Illuminate\Validation\Factory;
use Mockery\MockInterface;
use Halaei\Jalali\Laravel\JalaliValidator;

class JalaliServiceProviderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Factory
     */
    private $factory;

    /**
     * @var Translator|MockInterface
     */
    private $translator;

    public function setUp()
    {
        parent::setUp();
        $this->translator = Mockery::mock(Translator::class);
        $this->factory = new Factory($this->translator);

        $validator = new JalaliValidator();

        $this->factory->extend('jalali', function($attribute, $value, $parameter) use ($validator){
            return  $validator->validateJalali($attribute, $value, $parameter);
        });

        $this->factory->replacer('jalali', function($message, $attribute, $rule, $parameter) use ($validator) {
            return $validator->replaceJalali($message, $attribute, $rule, $parameter);
        });
    }

    public function tearDown()
    {
        parent::tearDown();
        Mockery::close();
    }

    public function test_validation_rules_pass()
    {
        $validator = $this->factory->make(
            [
                'j_date_1' => '1394/9/12',
                'j_date_2' => '۱۲ آذر ۱۳۹۴',
                'j_date_3' => '1394-9-12 13:21:44',
            ],
            [
                'j_date_1' => 'required|jalali',
                'j_date_2' => 'required|jalali:d M Y',
                'j_date_3' => 'required|jalali:Y-m-d *',
            ]
        );
        $this->assertTrue($validator->passes());
    }

    public function test_validation_rules_fail()
    {
        $validator = $this->factory->make(
            [
                'birth_date' => '1394/9/32',
                'graduation_date' => '1394-9-32'
            ],
            [
                'birth_date' => 'required|jalali',
                'graduation_date' => 'required|jalali:Y-m-d',
            ]
        );

        $this->translator->shouldReceive('trans')->once()->with('validation.custom.birth_date.jalali')
            ->andReturn('birth_date must be jalali of format :format');

        $this->translator->shouldReceive('trans')->once()->with('validation.attributes.birth_date')
            ->andReturn('validation.attributes.birth_date');

        $this->translator->shouldReceive('trans')->once()->with('validation.custom.graduation_date.jalali')
            ->andReturn('validation.custom.graduation_date.jalali');

        $this->translator->shouldReceive('trans')->once()->with('validation.jalali')
            ->andReturn(':attribute should be a valid jalali according to :format');

        $this->translator->shouldReceive('trans')->once()->with('validation.attributes.graduation_date')
            ->andReturn('the graduation date');

        $this->assertTrue($validator->fails());

        $this->assertEquals([
            'birth_date' => [
                'birth_date must be jalali of format Y/m/d',
            ],
            'graduation_date' => [
                'the graduation date should be a valid jalali according to Y-m-d',
            ]
        ], $validator->messages()->toArray());
    }
}