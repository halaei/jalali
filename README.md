# Jalali Validator For Laravel 4.2 and Laravel 5

[![Build Status](https://travis-ci.org/halaei/jalali.svg)](https://travis-ci.org/halaei/jalali)
[![Latest Stable Version](https://poser.pugx.org/halaei/jalali/v/stable)](https://packagist.org/packages/halaei/jalali)
[![Total Downloads](https://poser.pugx.org/halaei/jalali/downloads)](https://packagist.org/packages/halaei/jalali)
[![Latest Unstable Version](https://poser.pugx.org/halaei/jalali/v/unstable)](https://packagist.org/packages/halaei/jalali)
[![License](https://poser.pugx.org/halaei/jalali/license)](https://packagist.org/packages/halaei/jalali)

## Installation

### Step 1: Install Through Composer

    composer require halaei/jalali

### Step 2: Add the Service Provider

Add the provider class to the array of providers in config/app.php file

```php
	'providers' => [
	    ...
        Halaei\Jalali\Laravel\JalaliServiceProvider::class,
	]
```

### Step 3: Define the Error Messages

You need to define error messages for `jalali`, `jalali_after`, and `jalali_before` rules in validation.php in lang folders. Samples to copy & paste are provided under sample-lang directory of this package.
For example, if your project uses Laravel 5 and your Farsi ranslation are under `resources/lang/fa` directory, copy these lines to `resources/lang/fa/validation.php`:

```php
    'jalali'        => ':attribute وارد شده تاریخ شمسی معتبری طبق فرمت :format نیست (مثال معتبر: :fa-sample).',
    'jalali_after'  => ':attribute وارد شده باید یک تاریخ شمسی معتبر بعد از :date باشد.',
    'jalali_before' => ':attribute وارد شده باید یک تاریخ شمسی معتبر قبل از :date باشد.',
    ...
    //the rest of Farsi translations for validation rules.

    'attributes' => [
        'birth_date' => 'تاریخ تولد',
        ...
        //the rest of Farsi translations for attributes
    ],
    ...
```

## Validation Rules

### jalali:Y/m/d

Determines if an input is a valid Jalali date with the specified format. The default format is Y/m/d.

### jalali_after:1380/1/1,Y/m/d

Determines if an input is a valid Jalali date with the specified format and it is after a given date. The default format is Y/m/d and the default date is today.

### jalali_before:1395-01-01,Y-m-d

Determines if an input is a valid Jalali date with the specified format and it is before a given date. The default format is Y/m/d and the default date is today.

## Examples

Thanks to Laravel 5, you may use the mentioned validation rules inside rule() function of your domain specific Request objects.
If that is not an option, you can use the rules, just like any other Laravel rules with codes like the following:

```php
    $v = Validator::make([
            'birth_date' => '1380/01/32'
        ],
        [
            'birth_date' => 'required|jalali|jalali_before:1381/01/01|jalali_after:1300/01/01,Y/m/d'
        ]);

    if ($v->fails()) {
        var_dump($v->messages()->toArray());
    }
```

The output of the code above will be:

```php
array(1) {
  ["birth_date"]=>
  array(3) {
    [0]=>
    string(140) "تاریخ تولد وارد شده تاریخ شمسی معتبری طبق فرمت Y/m/d نیست (مثال معتبر: ۱۳۹۴/۹/۱۳)."
    [1]=>
    string(113) "تاریخ تولد وارد شده باید یک تاریخ شمسی معتبر قبل از 1381/01/01 باشد."
    [2]=>
    string(113) "تاریخ تولد وارد شده باید یک تاریخ شمسی معتبر بعد از 1300/01/01 باشد."
  }
}
```