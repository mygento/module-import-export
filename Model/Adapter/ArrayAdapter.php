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
     * Go to given position and check if it is valid
     *
     * @param int $position
     * @throws \OutOfBoundsException
     * @return void
     */
    public function seek($position)
    {
        $this->position = $position;

        if (!$this->valid()) {
            throw new \OutOfBoundsException("invalid seek position (${position})");
        }
    }

    /**
     * Rewind to starting position
     *
     * @return void
     */
    public function rewind()
    {
        $this->position = 0;
    }

    /**
     * Get data at current position
     *
     * @return mixed
     */
    public function current()
    {
        if (empty($this->array)) {
            return [];
        }

        return $this->array[$this->position];
    }

    /**
     * Get current position
     *
     * @return int
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * Set pointer to next position
     *
     * @return void
     */
    public function next()
    {
        ++$this->position;
    }

    /**
     * Is current position valid?
     *
     * @return bool
     */
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
