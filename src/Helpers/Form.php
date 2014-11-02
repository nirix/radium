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

namespace Radium\Helpers;

/**
 * Form Helper
 *
 * @author Jack P.
 * @package Radium
 * @subpackage Helpers
 */
class Form
{
    /**
     * Creates a label.
     *
     * @param string $text
     * @param string $for
     * @param array $attributes
     *
     * @return string
     */
    public static function label($text, $for = null, $attributes = array())
    {
        if ($for !== null) {
            $attributes['for'] = $for;
        }
        return "<label ". HTML::buildAttributes($attributes) .">{$text}</label>";
    }

    /**
     * Creates a text input field.
     *
     * @param string $name
     * @param array $attributes
     *
     * @return string
     */
    public static function text($name, $attributes = array())
    {
        return self::input('text', $name, $attributes);
    }

    /**
     * Creates a password input field.
     *
     * @param string $name
     * @param array $attributes
     *
     * @return string
     */
    public static function password($name, $attributes = array())
    {
        return self::input('password', $name, $attributes);
    }

    /**
     * Creates a hidden field.
     *
     * @param string $name
     * @param string $value
     *
     * @return string
     */
    public static function hidden($name, $value)
    {
        return self::input('hidden', $name, array('value' => $value));
    }

    /**
     * Creates a form submit button.
     *
     * @param string $text
     * @param string $attributes
     *
     * @return string
     */
    public static function submit($text, $attributes = array())
    {
        if (isset($attributes['name'])) {
            $name = $attributes['name'];
            unset($attributes['name']);
        } else {
            $name = 'submit';
        }

        return self::input('submit', $name, array_merge(array('value' => $text), $attributes));
    }

    /**
     * Creates a textarea field.
     *
     * @param string $name
     * @param array $attributes
     *
     * @return string
     */
    public static function textarea($name, $attributes = array())
    {
        return self::input('textarea', $name, $attributes);
    }

    /**
     * Creates a checkbox field.
     *
     * @param string $name
     * @param mixed $value
     * @param array $attributes
     *
     * @return string
     */
    public static function checkbox($name, $value, $attributes = array())
    {
        $attributes['value'] = $value;
        return self::input('checkbox', $name, $attributes);
    }

    /**
     * Creates a radio field.
     *
     * @param string $name
     * @param mixed $value
     * @param array $attributes
     *
     * @return string
     */
    public static function radio($name, $value, $attributes = array())
    {
        $attributes['value'] = $value;
        return self::input('radio', $name, $attributes);
    }

    /**
     * Creates a select field.
     *
     * @param string $name
     * @param array $attributes
     *
     * @return string
     */
    public static function select($name, $options, $attributes = array())
    {
        // Extract the value
        $value = isset($attributes['value']) ? $attributes['value'] : null;
        unset($attributes['value']);

        // Set the name
        $attributes['name'] = $name;

        // Set the id to the name if one
        // is not already set.
        if (!isset($attributes['id'])) {
            $attributes['id'] = $name;
        }

        // Opening tag
        $select = array();
        $select[] = "<select " . HTML::buildAttributes($attributes) . ">";

        // Options
        foreach ($options as $index => $option) {
            if (!is_numeric($index)) {
                $select[] = '<optgroup label="' . $index . '">';
                foreach ($option as $opt) {
                    $select[] = static::selectOption($opt, $value);
                }
                $select[] = '</optgroup>';
            } else {
                $select[] = static::selectOption($option, $value);
            }
        }

        // Closing tags
        $select[] = '</select>';

        return implode(PHP_EOL, $select);
    }

    /**
     * Return the HTML for a select option.
     *
     * @param array $option
     *
     * @return string
     */
    public static function selectOption($option, $value)
    {
        $attributes = [''];

        $attributes[] = "value=\"{$option['value']}\"";

        if (
            (is_array($value) && in_array($option['value'], $value))
            || ($option['value'] == $value)
        ) {
            $attributes[] = 'selected="selected"';
        }

        $attributes = implode(' ', $attributes);
        return "<option {$attributes}>{$option['label']}</option>";
    }

    /**
     * Creates a form field.
     *
     * @param string $type
     * @param string $name
     * @param array $attributes
     *
     * @return string
     */
    public static function input($type, $name, $attributes)
    {
        // Set id attribute to be same as the name
        // if one has not been set
        if (!isset($attributes['id'])) {
            $attributes['id'] =  $name;
        }

        // Check if the value is set in the
        // attributes array
        if (isset($attributes['value'])) {
            $value = $attributes['value'];
        }
        // Check if its in the _POST array
        elseif (isset($_POST[$name])) {
            $value = $_POST[$name];
        }
        // It's nowhere...
        else {
            $value = '';
        }

        // Add selected or checked attribute?
        foreach (array('selected', 'checked') as $attr) {
            if (isset($attributes[$attr]) and !$attributes[$attr]) {
                unset($attributes[$attr]);
            } elseif (isset($attributes[$attr])) {
                $attributes[$attr] = $attr;
            }
        }

        // Merge default attributes with
        // the specified attributes.
        $attributes = array_merge(array('type' => $type, 'name' => $name), $attributes);

        // Textareas
        if ($type == 'textarea') {
            return "<textarea " . HTML::buildAttributes($attributes) . ">{$value}</textarea>";
        }
        // Everything else
        else {
            // Don't pass the checked attribute if its false.
            if (isset($attributes['checked']) and !$attributes['checked']) {
                unset($attributes['checked']);
            }
            return "<input " . HTML::buildAttributes($attributes) . ">";
        }
    }
}
