<?php

use Illuminate\Translation\Translator;
use Illuminate\Validation\Factory;
use Mockery\MockInterface;
use Halaei\Jalali\Laravel\JalaliValidator;
use Opilo\Farsi\JalaliDate;
use Opilo\Farsi\StringCleaner;

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

        $this->factory->extend('jalali', function($attribute, $value, $parameter) use ($validator) {
            return  $validator->validateJalali($attribute, $value, $parameter);
        });

        $this->factory->extend('jalali_after', function($attribute, $value, $parameter) use ($validator) {
            return $validator->validateAfter($attribute, $value, $parameter);
        });

        $this->factory->extend('jalali_before', function($attribute, $value, $parameter) use ($validator) {
            return $validator->validateBefore($attribute, $value, $parameter);
        });

        $this->factory->replacer('jalali', function($message, $attribute, $rule, $parameter) use ($validator) {
            return $validator->replaceJalali($message, $attribute, $rule, $parameter);
        });

        $this->factory->replacer('jalali_after', function($message, $attribute, $rule, $parameter) use ($validator) {
            return $validator->replaceAfterOrBefore($message, $attribute, $rule, $parameter);
        });

        $this->factory->replacer('jalali_before', function($message, $attribute, $rule, $parameter) use ($validator) {
            return $validator->replaceAfterOrBefore($message, $attribute, $rule, $parameter);
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
                'j_date_1' => 'required|jalali|jalali_after:1394/9/1|jalali_before|jalali_before:1394/12/12',
                'j_date_2' => 'required|jalali:d M Y|jalali_after:۱ آذر ۱۳۹۴,d M Y|jalali_before:۲۹ اسفند ۱۳۹۴,d M Y',
                'j_date_3' => 'required|jalali:Y-m-d *',
            ]
        );
        $this->assertTrue($validator->passes());
    }

    public function test_validating_Jalali_fails()
    {
        JalaliValidator::setSampleDate(new JalaliDate(1395, 1, 1));

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
            ->andReturn(':attribute should be a valid jalali according to :format (e.g. :sample or :fa-sample)');

        $this->translator->shouldReceive('trans')->once()->with('validation.attributes.graduation_date')
            ->andReturn('the graduation date');

        $this->assertTrue($validator->fails());

        $this->assertEquals([
            'birth_date' => [
                'birth_date must be jalali of format Y/m/d',
            ],
            'graduation_date' => [
                'the graduation date should be a valid jalali according to Y-m-d (e.g. 1395-1-1 or ۱۳۹۵-۱-۱)',
            ]
        ], $validator->messages()->toArray());

        JalaliValidator::setSampleDate();
    }

    public function test_sample_dates_are_replaced()
    {
        JalaliValidator::setSampleDate(new JalaliDate(1395, 2, 13));

        $validator = $this->factory->make(
            [
                'graduation_date' => 'garbage',
            ],
            [
                'graduation_date' => 'required|jalali:Y-y-d-j-S-z-m-M-n\\Y',
            ]
        );

        $this->translator->shouldReceive('trans')->once()->with('validation.custom.graduation_date.jalali')
            ->andReturn('validation.custom.graduation_date.jalali');

        $this->translator->shouldReceive('trans')->once()->with('validation.jalali')
            ->andReturn(':sample :fa-sample');

        $this->translator->shouldReceive('trans')->once()->with('validation.attributes.graduation_date')
            ->andReturn('validation.attributes.graduation_date');

        $this->assertTrue($validator->fails());

        $this->assertEquals([
            'graduation_date' => [
                '1395-95-13-13-S-44-2-اردیبهشت-2Y ۱۳۹۵-۹۵-۱۳-۱۳-S-۴۴-۲-اردیبهشت-۲Y',
            ]
        ], $validator->messages()->toArray());

        JalaliValidator::setSampleDate();
    }

    public function test_jalali_after_or_before_replacer_is_applied_with_no_parameter()
    {
        $now = JalaliDate::fromDateTime(new DateTime())->format('Y/m/d', false);
        $faNow = StringCleaner::digitsToFarsi($now);
        $validator = $this->factory->make(
            [
                'graduation_date' => 'garbage',
            ],
            [
                'graduation_date' => 'required|jalali_after|jalali_before',
            ]
        );

        $this->translator->shouldReceive('trans')->once()->with('validation.custom.graduation_date.jalali_after')
            ->andReturn('validation.custom.graduation_date.jalali_after');
        $this->translator->shouldReceive('trans')->once()->with('validation.custom.graduation_date.jalali_before')
            ->andReturn('validation.custom.graduation_date.jalali_before');

        $this->translator->shouldReceive('trans')->once()->with('validation.jalali_after')
            ->andReturn('The :attribute must be a Jalali date after :date (:fa-date).');
        $this->translator->shouldReceive('trans')->once()->with('validation.jalali_before')
            ->andReturn('The :attribute must be a Jalali date before :date (:fa-date).');

        $this->translator->shouldReceive('trans')->twice()->with('validation.attributes.graduation_date')
            ->andReturn('validation.attributes.graduation_date');

        $this->assertTrue($validator->fails());

        $this->assertEquals([
            'graduation_date' => [
                "The graduation date must be a Jalali date after $now ($faNow).",
                "The graduation date must be a Jalali date before $now ($faNow).",
            ]
        ], $validator->messages()->toArray());
    }

    public function test_jalali_after_or_before_replacer_is_applied_with_date()
    {
        $now = '1394/9/14';
        $faNow = StringCleaner::digitsToFarsi($now);
        $validator = $this->factory->make(
            [
                'graduation_date' => 'garbage',
            ],
            [
                'graduation_date' => "required|jalali_after:$now|jalali_before:$now",
            ]
        );

        $this->translator->shouldReceive('trans')->once()->with('validation.custom.graduation_date.jalali_after')
            ->andReturn('validation.custom.graduation_date.jalali_after');
        $this->translator->shouldReceive('trans')->once()->with('validation.custom.graduation_date.jalali_before')
            ->andReturn('validation.custom.graduation_date.jalali_before');

        $this->translator->shouldReceive('trans')->once()->with('validation.jalali_after')
            ->andReturn('The :attribute must be a Jalali date after :date (:fa-date).');
        $this->translator->shouldReceive('trans')->once()->with('validation.jalali_before')
            ->andReturn('The :attribute must be a Jalali date before :date (:fa-date).');

        $this->translator->shouldReceive('trans')->twice()->with('validation.attributes.graduation_date')
            ->andReturn('validation.attributes.graduation_date');

        $this->assertTrue($validator->fails());

        $this->assertEquals([
            'graduation_date' => [
                "The graduation date must be a Jalali date after $now ($faNow).",
                "The graduation date must be a Jalali date before $now ($faNow).",
            ]
        ], $validator->messages()->toArray());
    }

    public function test_jalali_after_or_before_replacer_is_applied_with_date_and_format()
    {
        $now = '1394-9-15';
        $faNow = StringCleaner::digitsToFarsi($now);
        $validator = $this->factory->make(
            [
                'graduation_date' => 'garbage',
            ],
            [
                'graduation_date' => "required|jalali_after:$now,Y-m-d|jalali_before:$now,Y-m-d",
            ]
        );

        $this->translator->shouldReceive('trans')->once()->with('validation.custom.graduation_date.jalali_after')
            ->andReturn('validation.custom.graduation_date.jalali_after');
        $this->translator->shouldReceive('trans')->once()->with('validation.custom.graduation_date.jalali_before')
            ->andReturn('validation.custom.graduation_date.jalali_before');

        $this->translator->shouldReceive('trans')->once()->with('validation.jalali_after')
            ->andReturn('The :attribute must be a Jalali date after :date (:fa-date).');
        $this->translator->shouldReceive('trans')->once()->with('validation.jalali_before')
            ->andReturn('The :attribute must be a Jalali date before :date (:fa-date).');

        $this->translator->shouldReceive('trans')->twice()->with('validation.attributes.graduation_date')
            ->andReturn('validation.attributes.graduation_date');

        $this->assertTrue($validator->fails());

        $this->assertEquals([
            'graduation_date' => [
                "The graduation date must be a Jalali date after $now ($faNow).",
                "The graduation date must be a Jalali date before $now ($faNow).",
            ]
        ], $validator->messages()->toArray());
    }

}