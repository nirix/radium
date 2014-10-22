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
 * Event dispatcher.
 *
 * @author Jack Polgar <jack@polgar.id.au>
 */
class EventDispatcher
{
    /**
     * @var arary
     */
    protected static $listeners = [];

    /**
     * Add a listener to the action.
     *
     * @param string         $action
     * @param array|callable $callback
     */
    public static function addListener($action, $callback)
    {
        // Make sure it's something callable
        if (!is_callable($callback) && !is_array($callback)) {
            throw new Exception("Invalid callback for [{$action}]");
        }

        if (!isset(static::$listeners[$action])) {
            static::$listeners[$action] = [];
        }

        static::$listeners[$action][] = $callback;
    }

    /**
     * Dispatch the event name and pass the parameters to the callbacks.
     *
     * @param string $action
     * @param array  $parameters
     */
    public static function dispatch($action, array $parameters = [])
    {
        if (isset(static::$listeners[$action])) {
            foreach (static::$listeners[$action] as $callback) {
                call_user_func_array($callback, $parameters);
            }
        }
    }
}
