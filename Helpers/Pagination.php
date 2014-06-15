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
            $requestArgs = Request::$request;
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
                if ($this->prevPage != 1) {
                    $this->prevPageUri = $this->createUri($this->prevPage);
                } else {
                    $this->prevPageUri = Request::$uri;
                }
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

        return Request::$uri . "?{$queryString}";
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
