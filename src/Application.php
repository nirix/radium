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

namespace Radium;

use Exception;
use ReflectionObject;
use Radium\Database\ConnectionManager;
use Radium\Templating\View;
use Radium\Templating\Engines\PhpEngine;

/**
 * Application base class.
 *
 * @since 2.0.0
 * @author Jack Polgar <jack@polgar.id.au>
 */
class Application
{
    // Application path.
    protected $path;

    // Routes file location.
    protected $routesFile = "config/routes.php";

    // Database config file location.
    protected $databaseConfigFile = "config/database.php";

    // Database config array and connection object.
    protected $databaseConfig;
    protected $databaseConnection;

    // Environment
    protected $environment;

    /**
     * Connects to the database and loads the routes.
     */
    public function __construct() {
        $classInfo   = new ReflectionObject($this);
        $this->path  = dirname($classInfo->getFilename());

        $this->loadEnvironment();

        // Load the database configuration and connect
        $this->loadDatabaseConfig();
        $this->connectDatabase();

        // Load the routes
        $this->loadRoutes();

        // Configure templating
        $this->configureTemplating();
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
            throw new Exception("Unable to load routes.");
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
            throw new Exception("Unable to load database configuration.");
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

        $this->databaseConnection = ConnectionManager::create($this->databaseConfig);
    }

    /**
     * Configures the view rendering system.
     */
    protected function configureTemplating()
    {
        View::setEngine(new PhpEngine);
        View::addPath("{$this->path}/views");
    }

    /**
     * Runs the application.
     */
    public function run()
    {
        Kernel::run($this);
    }

    /**
     * @return string
     */
    public function environment()
    {
        return $this->environment;
    }

    /**
     * Loads the environment configuration.
     */
    protected function loadEnvironment()
    {
        if (file_exists("{$this->path}/config/environment.php")) {
            require "{$this->path}/config/environment.php";
        }

        if ($this->environment) {
            if (file_exists("{$this->path}/config/environment/{$this->environment}.php")) {
                require "{$this->path}/config/environment/{$this->environment}.php";
            }
        }
    }
}
