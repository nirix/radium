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
        foreach ($validations as $validation => $value) {
            static::{$validation}($model, $field, $value);
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
        if ($model::find($field, $model->{$field})) {
            $model->addError($field, 'errors.validations.already_in_use');
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
            $model->addError($field, 'errors.validations.required');
        }
    }
}
