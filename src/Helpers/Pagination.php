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

use Radium\Http\Request;

/**
 * Pagination helper.
 *
 * @author Jack P.
 * @since 2.0
 * @package Radium\Helpers
 */
class Pagination
{
    /**
     * Whether to paginate or not.
     *
     * @var boolean
     */

    public $paginate = false;

    /**
     * Number of database rows.
     *
     * @var integer
     */
    public $rows = 0;

    /**
     * Total pages.
     *
     * @var integer
     */
    public $totalPages = 0;

    /**
     * Rows per page
     *
     * @var integer
     */
    public $perPage = 25;

    /**
     * Pagination links.
     *
     * @var array
     */
    public $pageLinks = array();

    /**
     * Range of pagination links.
     *
     * @var integer
     */
    public $range = 12;

    /**
     * Next page number.
     *
     * @var integer
     */
    public $nextPage;

    /**
     * Previous page number.
     *
     * @var integer
     */
    public $prevPage;

    /**
     * Next page URI.
     *
     * @var string
     */
    public $nextPageUri;

    /**
     * Previous page URI.
     *
     * @var string
     */
    public $prevPageUri;

    /**
     * Database row limit.
     *
     * @var integer
     */
    public $limit;

    /**
     * Modified request query string.
     *
     * @var array
     */
    protected $query = array();

    /**
     * Generates pagination information.
     *
     * @param integer $page     Current page
     * @param integer $per_page Rows per page
     * @param integer $rows     Rows in the database
     */
    public function __construct($page, $perPage, $rows)
    {
        // Set information
        $this->page        = $page;
        $this->perPage     = $perPage;
        $this->totalPages  = ceil($rows / $perPage);
        $this->rows        = $rows;

        // More than per-page limit?
        if ($rows > $perPage) {
            $this->paginate = true;

            // Next/prev pages
            $this->nextPage = ($this->page + 1);
            $this->prevPage = ($this->page - 1);

            // Limit pages
            $this->limit = ($this->page - 1 > 0 ? $this->page - 1 : 0) * $perPage;

            // Remove current page from query string
            $requestArgs = Request::$get;
            unset($requestArgs['page']);

            // Create query string
            foreach ($requestArgs as $key => $value) {
                $this->query[] = "{$key}={$value}";
            }

            // Next page URI
            if ($this->nextPage <= $this->totalPages) {
                $this->nextPageUri = $this->createUri($this->nextPage);
            }

            // Previous page URI
            if ($this->prevPage > 0) {
                $this->prevPageUri = $this->createUri($this->prevPage);
            }

            // Create page links
            $this->createPageLinks();
        }
    }

    /**
     * Creates the URI for the specified page.
     *
     * @param integer $page
     *
     * @return string
     */
    public function createUri($page)
    {
        $queryString   = $this->query;
        $queryString[] = "page={$page}";
        $queryString   = implode('&', $queryString);

        return Request::pathInfo() . "?{$queryString}";
    }

    /**
     * Creates the page links.
     */
    protected function createPageLinks()
    {
        $pageLinks = array();

        if ($this->totalPages > 10) {
            $startRange = $this->page - floor($this->range/2);
            $endRange   = $this->page + floor($this->range/2);

            //Start range
            if ($startRange <= 0) {
                $startRange = 1;
                $endRange += abs($startRange) + 1;
            }

            // End range
            if ($endRange > $this->totalPages) {
                $startRange -= $endRange - $this->totalPages;
                $endRange = $this->totalPages;
            }

            // Range
            $range = range($startRange, $endRange);

            // Add first page
            $this->pageLinks[] = array(
                'page' => 1,
                'uri'  => $this->createUri(1)
            );

            foreach ($range as $page) {
                // Skip for first and last page
                if ($page == 1 or $page == $this->totalPages) {
                    continue;
                }

                $this->pageLinks[] = array(
                    'page' => $page,
                    'uri'  => $this->createUri($page)
                );
            }

            // Add last page
            $this->pageLinks[] = array(
                'page' => $this->totalPages,
                'uri'  => $this->createUri($this->totalPages)
            );
        } else {
            for ($i = 1; $i <= $this->totalPages; $i++) {
                $this->pageLinks[] = array(
                    'page' => $i,
                    'uri'  => $this->createUri($i)
                );
            }
        }
    }
}
