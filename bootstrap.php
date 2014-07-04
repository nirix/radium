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
