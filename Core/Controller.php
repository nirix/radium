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

namespace Radium\Core;

use Radium\Http\Router;
use Radium\Output\Body;
use Radium\Output\View;

/**
 * Controller
 *
 * @since 0.3
 * @package Radium
 * @subpackage Core
 * @author Jack P.
 * @copyright (C) Jack P.
 */
class Controller
{
    public $render = array(
        'action' => true,     // Call the routed action, or not
        'view'   => false,    // View to render, set in __construct()
        'layout' => 'default' // Layout to render
    );

    public $before = array();
    public $after = array();

    public function __construct()
    {
        $this->render['view'] = get_called_class() . '/' . Router::$method;
    }

    public function __shutdown()
    {
        // Don't render the layout for json content
        if (Router::$extension == 'json') {
            $this->render['layout'] = false;
        }

        // Render the view
        $content = '';
        if ($this->render['action'] and $this->render['view']) {
            Body::append(View::render($this->render['view']));
        }

        // Are we wrapping the view in a layout?
        if ($this->render['layout']) {
            $content = Body::content();
            Body::clear();
            Body::append(View::render("layouts/{$this->render['layout']}", array('content' => $content)));
        } else {
            Body::append($content);
        }

        // Set the X-Powered-By header and render the layout with the content
        header("X-Powered-By: Radium/" . Kernel::version());
        print(Body::content());
    }
}
