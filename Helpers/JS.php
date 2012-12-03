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

namespace Radium\Helpers;

/**
 * JavaScript Helper
 *
 * @author Jack P.
 * @package Radium
 * @subpackage Helpers
 */
class JS
{
    /**
     * Escapes the specified content.
     *
     * @param string $content
     *
     * @return string
     */
    public static function escape($content)
    {
        $replace = array(
            "\r" => '',
            "\n" => ''
        );
        return addslashes(str_replace(array_keys($replace), array_values($replace), $content));
    }
}