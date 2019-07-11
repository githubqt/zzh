<?php
// +----------------------------------------------------------------------
// | PhpStorm
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://zhahehe.com All rights reserved.
// +----------------------------------------------------------------------
// | 版权所有：昌少 
// +----------------------------------------------------------------------
// | Author: 昌少  Date:2018/8/16 Time:9:35
// +----------------------------------------------------------------------


namespace Assemble\Support;

class Date extends \DateTime
{
    const WEEKS_PER_YEAR = 52;
    const WEEKS_PER_MONTH = 4;
    const DAYS_PER_WEEK = 7;
    const HOURS_PER_DAY = 24;
    const MINUTES_PER_HOUR = 60;
    const SECONDS_PER_MINUTE = 60;
    const MICROSECONDS_PER_SECOND = 1000000;


    public static function startOfDay(string $date)
    {
        if (!self::validateDate($date)) {
            return '';
        }
        return  "{$date} 00:00:00";
    }

    public static function endOfDay(string $date)
    {
        if (!self::validateDate($date)) {
            return '';
        }
        return  "{$date} 23:59:59";
    }

    /**
     * Validate is a valid date.
     *
     * @param  mixed $value
     * @return bool
     */
    public static function validateDate($value)
    {
        if ((!is_string($value) && !is_numeric($value)) || strtotime($value) === false) {
            return false;
        }

        $date = date_parse($value);

        return checkdate($date['month'], $date['day'], $date['year']);
    }

    public static function nowAt(){
        return date("Y-m-d H:i:s");
    }


}