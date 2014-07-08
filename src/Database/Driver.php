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

        Error::halt("Database Error", $error . '<br />' . $this->lastQuery);
    }
}
