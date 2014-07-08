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

use Radium\Language;

/**
 * Validations class.
 *
 * @package Radium
 * @subpackage Database
 * @author Jack P.
 * @copyright (C) Jack P.
 */
class Validations
{
    /**
     * Runs the validations for passed Model and field.
     *
     * @param object $model
     * @param string $field
     * @param array  $validations
     */
    public static function run($model, $field, $validations)
    {
        // Run validations
        foreach ($validations as $validation => $options) {
            // Is this a validation without any options?
            if (is_numeric($validation)) {
                $validation = $options;
            }

            if ($data = call_user_func_array(array(get_called_class(), $validation), array($model, $field, $options)) and $data !== null) {
                if (is_string($data)) {
                    $data = array('message' => $data);
                }
                $model->addError($field, $validation, array_merge($data, array('validation' => $validation)));
            }
        }
    }

    /**
     * Checks if the field is unique.
     *
     * @param object $model
     * @param string $field
     */
    private static function unique($model, $field)
    {
        if ($row = $model::find($field, $model->{$field}) and $row->{$model::primaryKey()} != $model->{$model::primaryKey()}) {
            return 'errors.validations.already_in_use';
        }
    }

    /**
     * Checks if the field is set.
     *
     * @param object $model
     * @param string $field
     */
    private static function required($model, $field)
    {
        if (!isset($model->{$field}) or empty($model->{$field})) {
            return 'errors.validations.required';
        }
    }

    /**
     * Checks if the field is an email address.
     *
     * @param object $model
     * @param string $field
     */
    private static function email($model, $field)
    {
        if (!filter_var($model->{$field}, FILTER_VALIDATE_EMAIL)) {
            return 'errors.validations.must_be_email';
        }
    }

    /**
     * Validates the minimum length of the field.
     *
     * @param object $model
     * @param string $field
     */
    private static function minLength($model, $field, $minLength)
    {
        if (strlen($model->{$field}) < $minLength) {
            return array('message' => "errors.validations.field_too_short", 'minLength' => $minLength);
        }
    }

    /**
     * Validates the maximum length of the field.
     *
     * @param object $model
     * @param string $field
     */
    private static function maxLength($model, $field, $maxLength)
    {
        if (strlen($model->{$field}) > $maxLength) {
            return array('message' => "errors.validations.field_too_long", 'maxLength' => $maxLength);
        }
    }

    /**
     * Checks if the field is numeric.
     *
     * @param object $model
     * @param string $field
     */
    private static function numeric($model, $field)
    {
        if (!is_numeric($model->{$field})) {
            return 'errors.validations.must_be_numeric';
        }
    }
}
