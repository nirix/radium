<?php
/*!
 * Radium
 * Copyright (C) 2011-2013 Jack P.
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

namespace Radium\Translatons;

use Radium\Language;

/**
 * The English translations for Radium.
 * @package Radium\Translations
 */
$enAU = new Language(function($t){
    $t->name    = "English (Australian)";
    $t->locale  = "enAU";
    $t->strings = array(
        // Model validations
        'errors.validations.already_in_use'  => "{field} is already in use",
        'errors.validations.required'        => "{field} is required",
        'errors.validations.must_be_email'   => "{field} is not a valid email",
        'errors.validations.field_too_short' => "{field} must be at least {minLength} characters long",
        'errors.validations.field_too_long'  => "{field} must be under {maxLength} characters long",
        'errors.validations.must_be_numeric' => "{field} must be a number"
    );
});
