<?php

/**
 * @author Mygento Team
 * @copyright 2018-2020 Mygento (https://www.mygento.ru)
 * @package Mygento_ImportExport
 */

namespace Mygento\ImportExport\Model\Adapter;

class ArrayAdapter extends \Magento\ImportExport\Model\Import\AbstractSource
{
    /**
     * @var int
     */
    private $position = 0;

    /**
     * @var array The Data; Array of Array
     */
    private $array = [];

    /**
     * ArrayAdapter constructor.
     * @param array $data
     */
    public function __construct($data)
    {
        $this->array = $data;
        $this->position = 0;
        $colnames = array_keys($this->current());
        parent::__construct($colnames);
    }

    /**
     * Seeks to a position (Seekable interface)
     *
     * @param int $position The position to seek to 0 or more
     * @throws \OutOfBoundsException
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function seek($position)
    {
        $this->position = $position;

        if (!$this->valid()) {
            throw new \OutOfBoundsException("invalid seek position (${position})");
        }
    }

    /**
     * Rewind the \Iterator to the first element (\Iterator interface)
     *
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function rewind()
    {
        $this->position = 0;
    }

    /**
     * Return the current element
     *
     * Returns the row in associative array format: array(<col_name> => <value>, ...)
     *
     * @return array
     */
    #[\ReturnTypeWillChange]
    public function current()
    {
        if (empty($this->array)) {
            return [];
        }

        return $this->array[$this->position];
    }

    /**
     * Return the key of the current element (\Iterator interface)
     *
     * @return int -1 if out of bounds, 0 or more otherwise
     */
    #[\ReturnTypeWillChange]
    public function key()
    {
        return $this->position;
    }

    /**
     * Move forward to next element (\Iterator interface)
     *
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function next()
    {
        ++$this->position;
    }

    /**
     * Checks if current position is valid (\Iterator interface)
     *
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function valid()
    {
        return isset($this->array[$this->position]);
    }

    /**
     * Column names getter.
     *
     * @return array
     */
    public function getColNames()
    {
        $colNames = [];
        foreach ($this->array as $row) {
            foreach (array_keys($row) as $key) {
                if (!is_numeric($key) && !isset($colNames[$key])) {
                    $colNames[$key] = $key;
                }
            }
        }

        return $colNames;
    }

    public function setValue($key, $value)
    {
        if (!$this->valid()) {
            return;
        }

        $this->array[$this->position][$key] = $value;
    }

    public function unsetValue($key)
    {
        if (!$this->valid()) {
            return;
        }

        unset($this->array[$this->position][$key]);
    }

    protected function _getNextRow()
    {
        $this->next();

        return $this->current();
    }
}
