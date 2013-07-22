<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Alexander Egurtsov
 * Date: 7/22/13
 * Time: 6:24 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Lostedboy\SphinxsearchBundle\Services\Search;


class SearchResult extends \ArrayIterator
{
    /**
     * Query results.
     *
     * @var array
     */
    private $data = array();

    /**
     * Load query data to object.
     *
     * @param array $data
     */
    public function __construct(array $data = array())
    {
        $this->data = $data;
    }

    /**
     * Check to see if an error occurred.
     *
     * @return bool
     */
    public function isValid()
    {
        return !empty($this->data) AND isset($data['error']) AND empty($data['error']);
    }

    /**
     * Check to see if got an empty result.
     *
     * @return bool
     */
    public function isEmpty()
    {
        return isset($data['total']) AND $data['total'] == '0';
    }

    /**
     * Get query results.
     *
     * @return array
     */
    public function getResults()
    {
        $results = array();

        if ($this->isValid() AND !$this->isEmpty() AND isset($this->data['matches'])) {
            $results = $this->data['matches'];
        }

        return $results;
    }

    /**
     * Get response data.
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Get words scanned.
     *
     * @return array
     */
    public function getWords()
    {
        $words = array();

        if ($this->isValid() AND isset($this->data['words'])) {
            $words = $this->data['words'];
        }

        return $words;
    }

    /**
     * Get current result item.
     *
     * @return array|void
     */
    public function current()
    {
        if ($this->isValid() AND !$this->isEmpty()) {
            return $this->getResults[$this->key()];
        }
    }
}