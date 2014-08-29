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

namespace Radium\Database\Model;

use Radium\Util\Inflector;

/**
 * Errors model trait.
 *
 * @package Radium\Database\Model
 * @since 2.0
 * @author Jack Polgar
 */
trait Errors
{
    /**
     * Returns the errors array.
     *
     * @return array
     */
    public function errors()
    {
        return $this->errors;
    }

    /**
     * Returns the models errors with proper messages.
     *
     * @return array
     */
    public function errorMessages()
    {
        $messages = array();

        // Loop over each field
        foreach ($this->errors as $field => $errors) {
            $messages[$field] = array();

            // Loop over the fields errors
            foreach ($errors as $validation => $error) {
                $vars = array_merge(
                    array('field' => Language::translate($field)),
                    $error
                );
                unset($vars['message']);
                $messages[$field][] = Language::translate($error['message'], $vars);
            }
        }

        return $messages;
    }

    /**
     * Adds an error for the specified field.
     *
     * @param string $field
     * @param string $message
     */
    public function addError($field, $validation, $data)
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = array();
        }

        $this->errors[$field][$validation] = $data;
    }
}
