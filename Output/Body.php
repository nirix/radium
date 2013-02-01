<?php
/*!
 * Radium
 * Copyright (C) 2011-2012 Jack P.
 * https://github.com/nirix
 *
 * This file is part of Radium.
 *
 * Radium is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation; version 3 only.
 *
 * Radium is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with Radium. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Radium\Output;

/**
 * Radium's View rendering class.
 *
 * @since 0.1
 * @package Radium
 * @subpackage Output
 * @author Jack P.
 * @copyright (C) Jack P.
 */
class Body
{
    public static $content = '';

    public static function append($content)
    {
        static::$content .= $content;
    }

    public static function content()
    {
        return static::$content;
    }

    public static function clear()
    {
        static::$content = '';
    }
}
