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

/**
 * The FishHook plugin library
 *
 * @package FishHook
 * @author Jack P.
 * @copyright (C) 2009-2012 Jack P.
 * @version 4.0
 */
class Hook
{
    private static $plugins = array();

    /**
     * Adds a plugin to the library
     *
     * @param string $class
     * @param mixed  $plugin String of the function or array of the class and method.
     */
    public static function add($hook, $plugin)
    {
        // Make sure the hook index exists
        if (!isset(static::$plugins[$hook])) {
            static::$plugins[$hook] = array();
        }

        // Add the plugin
        static::$plugins[$hook][] = $plugin;
    }

    /**
     * Executes a hook
     *
     * @param string $hook
     * @param array  $params Parameters to be passed to the plugins method.
     */
    public static function run($hook, array $params = array())
    {
        // Make sure the hook index exists
        if (!isset(static::$plugins[$hook])) {
            return false;
        }

        // Run the hook
        foreach (static::$plugins[$hook] as $plugin) {
            call_user_func_array($plugin, $params);
        }
    }
}
