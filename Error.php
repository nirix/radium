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
