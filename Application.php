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

    // Routes file location.
    protected $routesFile = "Config/Routes.php";

    // Database config file location.
    protected $databaseConfigFile = "Config/Database.php";

    // Database config array and connection object.
    protected $databaseConfig;
    protected $databaseConnection;

    /**
     * Connects to the database and loads the routes.
     */
    public function __construct() {
        $classInfo   = new \ReflectionObject($this);
        $this->path  = dirname($classInfo->getFilename());

        // Load the database configuration and connect
        $this->loadDatabaseConfig();
        $this->connectDatabase();

        // Load the routes
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
        $routesFile = "{$this->path}/{$this->routesFile}";

        if (file_exists($routesFile)) {
            require $routesFile;
        } else {
            Error::halt("Unable to load routes.");
        }
    }

    /**
     * Loads the database configuration file.
     */
    protected function loadDatabaseConfig()
    {
        if (!$this->databaseConfigFile) {
            return null;
        }

        $configPath = "{$this->path}/{$this->databaseConfigFile}";
        if (file_exists($configPath)) {
            $this->databaseConfig = require $configPath;
        } else {
            Error::halt("Unable to load database configuration.");
        }
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
