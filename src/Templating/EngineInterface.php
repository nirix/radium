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

namespace Radium\Templating;

/**
 * Template rendering engine interface.
 *
 * @since 2.0.0
 * @author Jack Polgar <jack@polgar.id.au>
 */
interface EngineInterface
{
    /**
     * The name of the engine.
     *
     * @return string
     */
    public function name();

    /**
     * Adds a global variable for all templates.
     *
     * @param string $name
     * @param mixed  $value
     */
    public function addGlobal($name, $value);

    /**
     * Adds a template path to search in.
     *
     * @param string|array $path
     */
    public function addPath($path, $prepend = false);

    /**
     * @param string $template
     * @param array  $locals
     *
     * @return string
     */
    public function render($template, array $locals = []);

    /**
     * Checks if the engine can render the template.
     *
     * @param string $template
     *
     * @return bool
     */
    public function supports($template);

    /**
     * Checks if the template exists.
     *
     * @param string $template
     *
     * @return bool
     */
    public function exists($template);
}
