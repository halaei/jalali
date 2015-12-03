<?php

namespace Opilo\Farsi\Laravel;

use Opilo\Farsi\JalaliDate;

class JalaliValidator
{
    public function validateJalali($attribute, $value, $parameters)
    {
        if (!is_string($value)) {
            return false;
        }

        $format = count($parameters) ? $parameters[0] : 'Y/m/d';

        try {
            JalaliDate::fromFormat($format, $value);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    public function replaceJalali($message, $attribute, $rule, $parameters)
    {
        $format = count($parameters) ? $parameters[0] : 'Y/m/d';

        return str_replace(':format', $format, $message);
    }
}