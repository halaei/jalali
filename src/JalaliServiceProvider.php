<?php

namespace Halaei\Jalali\Laravel;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

class JalaliServiceProvider extends ServiceProvider
{
    public function register()
    {
    }

    public function boot()
    {
        Validator::extend('jalali', JalaliValidator::class . '@validateJalali');

        Validator::replacer('jalali', JalaliValidator::class . '@replaceJalali');
    }
}