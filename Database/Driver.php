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

namespace Radium\Database;

use Radium\Error;

/**
 * Database driver
 *
 * @since 0.2
 * @package Radium
 * @subpackage Database
 * @author Jack P.
 * @copyright (C) Jack P.
 */
class Driver
{
    /**
     * Shortcut to the Error::halt method.
     *
     * @param string $error Error message
     */
    public function halt($error = 'Unknown error')
    {
        if (is_array($error) and isset($error[2]) and !empty($error[2])) {
            $error = $error[2];
        } elseif (!is_array($error)) {
            $error = $error;
        } else {
            $error = 'Unknown error. ' . implode('/', $error);
        }

        Error::halt("Database Error", $error . '<br />' . $this->last_query);
    }
}
