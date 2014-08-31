<?php
/*!
 * Radium
 * Copyright 2011-2014 Jack Polgar
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Radium\Helpers;

use Radium\Language;

/**
 * Time Helper
 *
 * @author Jack P.
 * @package Radium
 * @subpackage Helpers
 */
class Time
{
    /**
     * Formats the date.
     *
     * @param string $format Date format
     * @param mixed $time Date in unix time or date-time format.
     *
     * @return string
     */
    public static function date($format = "Y-m-d H:i:s", $time = null)
    {
        $time = ($time !== null ? $time : time());

        if (!is_numeric($time)) {
            $time = static::toUnix($time);
        }

        return date($format, $time);
    }

    /**
     * Returns the current GMT date andtime in date/time format.
     *
     * @return string
     */
    public static function gmt()
    {
        return date("Y-m-d H:i:s", time() - date("Z"));
    }

    /**
     * Converts the given GMT time to local time.
     *
     * @param string $datetime
     *
     * @return string
     */
    public static function gmtToLocal($datetime)
    {
        $stamp = static::toUnix($datetime);
        return date("Y-m-d H:i:s", $stamp + date("Z"));
    }

    /**
     * Converts the given local time to GMT time.
     *
     * @param string $datetime
     *
     * @return string
     */
    public static function localToGmt($datetime)
    {
        $stamp = static::toUnix($datetime);
        return date("Y-m-d H:i:s", $stamp - date("Z"));
    }

    /**
     * Converts a datetime timestamp into a unix timestamp.
     *
     * @param datetime $original
     *
     * @return mixed
     */
    public static function toUnix($original)
    {
        if (is_numeric($original)) {
            return $original;
        }

        // YYYY-MM-DD HH:MM:SS
        if (preg_match("/(?P<year>\d+)-(?P<month>\d+)-(?P<day>\d+) (?P<hour>\d+):(?P<minute>\d+):(?P<second>\d+)/", $original, $match)) {
            return mktime($match['hour'], $match['minute'], $match['second'], $match['month'], $match['day'], $match['year']);
        }
        // YYYY-MM-DD
        elseif (preg_match("/(?P<year>\d+)-(?P<month>\d+)-(?P<day>\d+)/", $original, $match)) {
            return mktime(0, 0, 0, $match['month'], $match['day'], $match['year']);
        }
        // Fail
        else {
            return strtotime($original);
        }
    }

    /**
     * Returns time ago in words of the given date.
     *
     * @param string  $original
     * @param boolean $detailed
     *
     * @return string
     */
    public static function agoInWords($original, $detailed = false)
    {
        // Check what kind of format we're dealing with, timestamp or datetime
        // and convert it to a timestamp if it is in datetime form.
        if (!is_numeric($original)) {
            $original = static::toUnix($original);
        }

        $now = time(); // Get the time right now...

        // Time chunks...
        $chunks = array(
            array(60 * 60 * 24 * 365, 'year', 'years'),
            array(60 * 60 * 24 * 30, 'month', 'months'),
            array(60 * 60 * 24 * 7, 'week', 'weeks'),
            array(60 * 60 * 24, 'day', 'days'),
            array(60 * 60, 'hour', 'hours'),
            array(60, 'minute', 'minutes'),
            array(1, 'second', 'seconds'),
        );

        // Get the difference
        $difference = $now > $original ? ($now - $original) : ($original - $now);

        // Loop around, get the time from
        for ($i = 0, $c = count($chunks); $i < $c; $i++) {
            $seconds = $chunks[$i][0];
            $name = $chunks[$i][1];
            $names = $chunks[$i][2];
            if(0 != $count = floor($difference / $seconds)) break;
        }

        // Format the time from
        $from = Language::current()->translate("time.x_{$name}", array($count));

        // Get the detailed time from if the detail variable is true
        if ($detailed && $i + 1 < $c) {
            $seconds2 = $chunks[$i + 1][0];
            $name2 = $chunks[$i + 1][1];
            $names2 = $chunks[$i + 1][2];
            if (0 != $count2 = floor(($difference - $seconds * $count) / $seconds2)) {
                $from = Language::current()->translate('time.x_and_x', $from, Language::current()->translate("time.x_{$name2}", array($count2)));
            }
        }

        // Return the time from
        return $from;
    }
}
