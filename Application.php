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

namespace Radium;

use Radium\Database;
use Radium\Action\View;

/**
 * Application base class.
 *
 * @since 2.0
 * @package Radium
 * @author Jack P.
 * @copyright (C) Jack P.
 */
class Application
{
    // Application path.
    protected $path;

    protected $routesFile = "Config/Routes.php";
    protected $databaseConfig;
    protected $databaseConnection;

    /**
     * Connects to the database and loads the routes.
     */
    public function __construct() {
        $classInfo   = new \ReflectionObject($this);
        $this->path  = dirname($classInfo->getFilename());

        $this->connectDatabase();
        $this->loadRoutes();

        // Add views directory
        View::addSearchPath("{$this->path}/Views");
    }

    /**
     * Returns the connected database object.
     *
     * @return object
     */
    public function databaseConnection()
    {
        return $this->databaseConnection;
    }

    /**
     * Loads the applications routes.
     */
    protected function loadRoutes()
    {
        $classInfo = new \ReflectionObject($this);
        $path      = dirname($classInfo->getFilename());
        require "{$path}/{$this->routesFile}";
    }

    /**
     * Connects to the configured database.
     */
    protected function connectDatabase()
    {
        if (!$this->databaseConfig) {
            return null;
        }

        $this->databaseConnection = Database::factory($this->databaseConfig);
    }

    /**
     * Runs the application.
     */
    public function run()
    {
        Kernel::run($this);
    }
}
