<?php
/*!
 * Radium
 * Copyright (C) 2011-2014 Jack P.
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

require __DIR__ . '/Autoloader.php';

use Radium\Autoloader;
use Radium\Loader;

// Set vendor directory
Loader::setVendorDirectory(defined('VENDOR_PATH') ? VENDOR_PATH : dirname(__DIR__));

// Alias common classes
Autoloader::aliasClasses(array(
    // Core classes
    '\Radium\Http\Request' => 'Request',
    '\Radium\Action\View'  => 'View',

    // Helpers
    '\Radium\Helpers\HTML' => 'HTML',
    '\Radium\Helpers\Form' => 'Form',
    '\Radium\Helpers\Time' => 'Time',

    // Utilities
    '\Radium\Util\Inflector' => 'Inflector'
));

// Register the autoloader
Autoloader::register();
