<?php

namespace Lostedboy\SphinxsearchBundle\Services\Search;

/**
 *
 */
class Sphinxsearch
{
    /**
     * @var string $host
     */
    protected $_host;

    /**
     * @var string $port
     */
    protected $_port;

    /**
     * @var string $socket
     */
    protected $_socket;

    /**
     * @var array $indexes
     *
     * $this->_indexes should look like:
     *
     * $this->_indexes = array(
     *   'IndexLabel' => 'Index name as defined in sphinxsearch.conf',
     *   ...,
     * );
     */
    protected $_indexes;

    /**
     * @var \SphinxClient $sphinx
     */
    protected $_sphinx;

    /**
     * Constructor.
     *
     * @param string $host    The server's host name/IP.
     * @param string $port    The port that the server is listening on.
     * @param string $socket  The UNIX socket that the server is listening on.
     * @param array $indexes The list of indexes that can be used.
     *
     * @return null
     */
    public function __construct($host = 'localhost', $port = '9312', $socket = null, array $indexes = array())
    {
        $this->_host = $host;
        $this->_port = $port;
        $this->_socket = $socket;
        $this->_indexes = $indexes;

        $this->_sphinx = new \SphinxClient();
        if ($this->_socket !== null)
            $this->_sphinx->setServer($this->_socket);
        else
            $this->_sphinx->setServer($this->_host, $this->_port);
    }

    /**
     * Escape the supplied string.
     *
     * @param string $string The string to be escaped.
     *
     * @return string The escaped string.
     */
    public function escapeString($string)
    {
        return $this->_sphinx->escapeString($string);
    }

    /**
     * Set the desired match mode.
     *
     * @param int $mode The matching mode to be used.
     *
     * @return null
     */
    public function setMatchMode($mode)
    {
        $this->_sphinx->setMatchMode($mode);
    }

    public function setSortMode($mode, $field)
    {
        $this->_sphinx->setSortMode($mode, $field);
    }

    public function resetSortMode()
    {
        $this->_sphinx->_sortby = '';
    }

    /**
     * Set limits on the range and number of results returned.
     *
     * @param int $offset The number of results to seek past.
     * @param int $limit  The number of results to return.
     * @param int $max    The maximum number of matches to retrieve.
     * @param int $cutoff The cutoff to stop searching at.
     *
     * @return null
     * \
     */
    public function setLimits($offset, $limit, $max = 0, $cutoff = 0)
    {
        $this->_sphinx->setLimits($offset, $limit, $max, $cutoff);
    }

    /**
     * Set weights for individual fields.  $weights should look like:
     *
     * $weights = array(
     *   'Normal field name' => 1,
     *   'Important field name' => 10,
     * );
     *
     * @param array $weights Array of field weights.
     *
     * @return null
     */
    public function setFieldWeights(array $weights)
    {
        $this->_sphinx->setFieldWeights($weights);
    }

    /**
     * Set filter range for attribute
     *
     * @param string $attribute The attribute to filter.
     * @param integer $min       minimal value
     * @param integer $max       maximal value
     * @param boolean $exclude   exclusion policy
     *
     * @return null
     */
    public function setFilterRange($attribute, $min, $max, $exclude = false)
    {
        $this->_sphinx->SetFilterRange($attribute, $min, $max, $exclude);
    }

    /**
     * Set the desired search filter.
     *
     * @param string $attribute The attribute to filter.
     * @param array $values    The values to filter.
     * @param bool $exclude   Is this an exclusion filter?
     *
     * @return null
     */
    public function setFilter($attribute, $values, $exclude = false)
    {
        $this->_sphinx->setFilter($attribute, $values, $exclude);
    }

    /**
     * Reset all previously set filters.
     *
     * @return null
     */
    public function resetFilters()
    {
        $this->_sphinx->resetFilters();
    }

    /**
     * Set the format of the search result array:
     *  $arrayResult = FALSE (default): Result will be a hash indexed by document ids
     *  $arrayResult = TRUE:            Result will be an array where document ids are provided as '@id' attribute.
     *
     * @param bool $arrayResult     should be TRUE or FALSE
     */
    public function setArrayResult($arrayResult)
    {
        $this->_sphinx->SetArrayResult($arrayResult);
    }

    /**
     * Set grouping
     *
     * @param string $attribute
     * @param string $func
     * @param string $groupSort
     */
    public function setGroupBy($attribute, $func, $groupSort = '@groupby desc')
    {
        $this->_sphinx->SetGroupBy($attribute, $func, $groupSort);
    }

    /**
     * Disable grouping
     */
    public function resetGroupBy()
    {
        $this->_sphinx->ResetGroupBy();
    }

    /**
     * Count the number of distinct attribute2 values per group while grouping by some other attribute @see setGroupBy()
     *
     * @param string $attribute2
     */
    public function setGroupDistinct($attribute2)
    {
        $this->_sphinx->SetGroupDistinct($attribute2);
    }

    /**
     * Search for the specified query string.
     *
     * @param string $query       The query string that we are searching for.
     * @param array $indexes     The indexes to perform the search on.
     * @param array $options     The options for the query.
     * @param bool $escapeQuery Should the query string be escaped?
     *
     * @return SearchResult The results of the search.
     *
     * @throws \RuntimeException
     */
    public function search($query, array $indexes, array $options = array(), $escapeQuery = true)
    {
        if ($escapeQuery)
            $query = $this->_sphinx->escapeString($query);

        /**
         * Build the list of indexes to be queried.
         */
        $indexNames = '';
        foreach ($indexes as $key => $value) {
            if (in_array($value, $this->_indexes)) {
                $indexNames .= $value . ' ';
            }
        }

        /**
         * If no valid indexes were specified, return an empty result set.
         */
        if (empty($indexNames)) {
            throw new \RuntimeException('No indexes matched');
        }

        /**
         * Set the offset and limit for the returned results.
         */
        if (isset($options['result_offset']) && isset($options['result_limit']))
            $this->_sphinx->setLimits($options['result_offset'], $options['result_limit']);

        /**
         * Weight the individual fields.
         */
        if (isset($options['field_weights']))
            $this->_sphinx->setFieldWeights($options['field_weights']);

        /**
         * Perform the query.
         */
        $results = $this->_sphinx->query($query, $indexNames);

        if (!is_array($results) OR $results['status'] !== SEARCHD_OK)
            throw new \RuntimeException(sprintf('Searching index "%s" for "%s" failed with error "%s".', $indexNames, $query, $this->_sphinx->getLastError()));

        return new SearchResult($results);
    }

    /**
     * Adds a query to a multi-query batch using current settings.
     *
     * @param string $query   The query string that we are searching for.
     * @param array $indexes The indexes to perform the search on.
     *
     * @return null
     */
    public function addQuery($query, array $indexes)
    {
        $indexNames = '';
        foreach ($indexes as $key => $value) {
            if (in_array($value, $this->_indexes))

                $indexNames .= $value . ' ';
        }

        if (!empty($indexNames))
            $this->_sphinx->addQuery($query, $indexNames);
    }

    /**
     * Runs the currently batched queries, and returns the results.
     *
     * @return array The results of the queries.
     */
    public function runQueries()
    {
        return $this->_sphinx->runQueries();
    }

    /**
     * Get index list or one index.
     *
     * @param null $name
     * @return array|null
     */
    public function getIndex($name = null)
    {
        if (is_null($name)){
            return $this->_indexes;
        }

        if(isset($this->_indexes[$name])){
            return $this->_indexes[$name];
        }

        return null;
    }
}