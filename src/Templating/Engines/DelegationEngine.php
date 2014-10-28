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

namespace Radium\Templating\Engines;

use Exception;
use Radium\Templating\EngineInterface;

/**
 * Adds support for multiple rendering engines.
 *
 * @since 2.0.0
 * @author Jack Polgar <jack@polgar.id.au>
 */
class DelegationEngine implements EngineInterface
{
    /**
     * @var EngineInterface[]
     */
    protected $engines = [];

    /**
     * @param EngineInterface[] $engines
     */
    public function __construct(array $engines)
    {
        $this->engines = $engines;
    }

    /**
     * @return string
     */
    public function name()
    {
        return 'delegation';
    }

    /**
     * Adds a global variable for all templates.
     *
     * @param string $name
     * @param mixed  $value
     */
    public function addGlobal($name, $value)
    {
        foreach ($this->engines as $engine) {
            $engine->addGlobal($name, $value);
        }
    }

    /**
     * Adds a template path to search in.
     *
     * @param string|array $path
     */
    public function addPath($path, $prepend = false)
    {
        foreach ($this->engines as $engine) {
            $engine->addPath($path, $prepend);
        }
    }

    /**
     * @param string $template
     * @param array  $locals
     *
     * @return string
     */
    public function render($template, array $locals = [])
    {
        foreach ($this->engines as $engine) {
            if ($engine->supports($template)) {
                return $engine->render($template, $locals);
            }
        }

        throw new Exception("Unable to find template [{$template}]");
    }

    /**
     * Checks if any of the engines can render the template.
     *
     * @param string $template
     *
     * @return bool
     */
    public function supports($template)
    {
        foreach ($this->engines as $engine) {
            if ($engine->supports($template)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if the template exists.
     *
     * @param string $template
     *
     * @return bool
     */
    public function exists($template)
    {
        foreach ($this->engines as $engine) {
            if ($engine->supports($template)) {
                return $engine->exists($template);
            }
        }

        return false;
    }
}
