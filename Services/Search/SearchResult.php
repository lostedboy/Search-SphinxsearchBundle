<?php
/**
 * User: Alexander Egurtsov
 * Date: 7/22/13
 * Time: 6:24 PM
 */

namespace Lostedboy\SphinxsearchBundle\Services\Search;


class SearchResult implements \Iterator, \ArrayAccess
{
    /**
     * Query results.
     *
     * @var array
     */
    private $data = array();

    private $results = array();

    /**
     * Load query data to object.
     *
     * @param array $data
     */
    public function __construct(array $data = array())
    {
        $this->data = $data;
        $this->results = $this->getResults();
    }

    /**
     * Count results.
     *
     * @return int
     */
    public function count()
    {
        return $this->isEmpty() ? 0 : (int)$this->data['total'];
    }
    /**
     * Check to see if an error occurred.
     *
     * @return bool
     */
    public function isValid()
    {
        return !empty($this->data) AND empty($this->data['error']);
    }

    /**
     * Check to see if got an empty result.
     *
     * @return bool
     */
    public function isEmpty()
    {
        return isset($this->data['total']) AND $this->data['total'] == '0';
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
     * {@inheritdoc}
     */
    public function rewind()
    {
        reset($this->results);
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return key($this->results);
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        return next($this->results);
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return $this->current() !== false;
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        return current($this->results);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return isset($this->results[$offset]);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        return $this->results[$offset];
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        return $this->results[$offset] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        unset($this->results[$offset]);
    }
}