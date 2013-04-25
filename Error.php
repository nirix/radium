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

namespace Radium;

use Radium\Kernel;

/**
 * Error class
 *
 * @since 0.1
 * @package Radium
 * @author Jack P.
 * @copyright (C) Jack P.
 */
class Error
{
    /**
     * Halts the executing of the script and displays the error.
     *
     * @param string $title   Error title
     * @param string $message Error message
     * @param array  $trace   Exception stack trace
     */
    public static function halt($title, $message = '', $trace = array())
    {
        @ob_end_clean();

        $body = array();
        $body[] = "<blockquote style=\"font-family:'Helvetica Neue', Arial, Helvetica, sans-serif;background:#fbe3e4;color:#8a1f11;padding:0.8em;margin-bottom:1em;border:2px solid #fbc2c4;\">";

        if (!$title !== null) {
            $body[] = "  <h1 style=\"margin: 0;\">{$title}</h1>";
        }

        $body[] = "  <div>{$message}</div>";

        if (count($trace)) {
            $body[] = "  <div style=\"margin-top:10px;\">";
            $body[] = "    <strong>Stack trace</strong>";
            $body[] = "    <ul style=\"margin-top:0px;\">";
            foreach ($trace as $t) {
                $body[] = "      <li>Line #{$t['line']} in <code>{$t['file']}</code></li>";
            }
            $body[] = "    </ul>";
            $body[] = "  </div>";
        }

        $body[] = "  <div style=\"margin-top:8px;\"><small>Powered by Radium " . Kernel::version() . "</small></div>";
        $body[] = "</blockquote>";

        echo implode(PHP_EOL, $body);
        exit;
    }
}
